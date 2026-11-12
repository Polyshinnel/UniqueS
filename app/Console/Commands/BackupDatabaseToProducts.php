<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class BackupDatabaseToProducts extends Command
{
    protected $signature = 'database:backup-products';

    protected $description = 'Создает полный дамп базы данных в storage/app/public/webserv/products и хранит дампы за 7 дней';

    public function handle(): int
    {
        try {
            // Используем параметры подключения после обработки DATABASE_URL Laravel.
            $connection = DB::connection()->getConfig();
        } catch (\Throwable $exception) {
            $this->error('Не удалось получить параметры подключения к базе данных.');
            return self::FAILURE;
        }

        if (! $connection || $connection['driver'] !== 'mysql') {
            $this->error('Команда поддерживает только MySQL.');
            return self::FAILURE;
        }

        $directory = storage_path('app/public/webserv/products');

        if (! File::isDirectory($directory) && ! File::makeDirectory($directory, 0755, true)) {
            $this->error("Не удалось создать папку: {$directory}");
            return self::FAILURE;
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . now()->format('d-m-Y') . '_uniqset_dump.sql';
        $temporaryPath = $filePath . '.tmp';
        $database = $connection['database'] ?? '';

        if ($database === '') {
            $this->error('В конфигурации подключения не задано имя базы данных.');
            return self::FAILURE;
        }

        $command = [
            (new ExecutableFinder())->find('mariadb-dump') ?? 'mysqldump',
            // Должен быть первым параметром: исключает пароль из /root/.my.cnf.
            '--no-defaults',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? 3306),
            '--user=' . ($connection['username'] ?? ''),
            $database,
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket=' . $connection['unix_socket'];
        }

        $process = new Process($command, base_path(), null, null, null);
        $process->setEnv(['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);

        try {
            $output = fopen($temporaryPath, 'wb');
            if ($output === false) {
                $this->error("Не удалось открыть файл для записи: {$temporaryPath}");
                return self::FAILURE;
            }

            $process->run(function (string $type, string $buffer) use ($output): void {
                if ($type === Process::OUT) {
                    fwrite($output, $buffer);
                }
            });
            fclose($output);

            if (! $process->isSuccessful()) {
                File::delete($temporaryPath);
                $this->error('Ошибка mysqldump: ' . trim($process->getErrorOutput()));
                return self::FAILURE;
            }

            if (! rename($temporaryPath, $filePath)) {
                File::delete($temporaryPath);
                $this->error("Не удалось сохранить дамп: {$filePath}");
                return self::FAILURE;
            }
        } catch (\Throwable $exception) {
            if (isset($output) && is_resource($output)) {
                fclose($output);
            }
            File::delete($temporaryPath);
            $this->error('Не удалось создать дамп: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info("Дамп базы данных сохранен: {$filePath}");

        try {
            $this->rotateDumps($directory);
        } catch (\Throwable $exception) {
            $this->error('Не удалось удалить старые дампы: ' . $exception->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function rotateDumps(string $directory): void
    {
        // Сегодня и шесть предыдущих дней; дата берется из имени, а не mtime.
        $oldestDate = now()->startOfDay()->subDays(6)->format('Y-m-d');

        foreach (File::files($directory) as $file) {
            if (! preg_match('/^(\d{2}-\d{2}-\d{4})_uniqset_dump\.sql$/', $file->getFilename(), $matches)) {
                continue;
            }

            $date = \DateTimeImmutable::createFromFormat('!d-m-Y', $matches[1]);

            if ($date === false || $date->format('d-m-Y') !== $matches[1] || $date->format('Y-m-d') >= $oldestDate) {
                continue;
            }

            if (! File::delete($file->getPathname())) {
                throw new \RuntimeException('Не удалось удалить файл: ' . $file->getPathname());
            }
        }
    }
}
