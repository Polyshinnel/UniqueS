<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LargeFileUploadMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Увеличиваем лимиты для загрузки больших файлов (до 2GB)
        ini_set('upload_max_filesize', '2048M');
        ini_set('post_max_size', '2048M');
        ini_set('max_execution_time', 600);
        ini_set('max_input_time', 600);
        ini_set('memory_limit', '1024M');
        ini_set('max_file_uploads', 100);
        
        // Увеличиваем лимиты для обработки больших запросов
        ini_set('max_input_vars', 20000);
        ini_set('max_input_nesting_level', 128);
        
        // Проверяем размер загружаемых файлов в различных полях
        $fileFields = ['media_files', 'files', 'images', 'photos', 'documents'];
        $totalSize = 0;
        
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $files = $request->file($field);
                
                // Если это массив файлов
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            $totalSize += $file->getSize();
                        }
                    }
                } 
                // Если это один файл
                elseif ($files && $files->isValid()) {
                    $totalSize += $files->getSize();
                }
            }
        }
        
        // Максимальный общий размер файлов: 2GB
        $maxTotalSize = 2048 * 1024 * 1024; // 2GB в байтах
        
        if ($totalSize > $maxTotalSize) {
            return response()->json([
                'success' => false,
                'message' => 'Общий размер файлов превышает лимит в 2GB. Пожалуйста, загрузите файлы по частям.',
                'total_size' => $totalSize,
                'max_size' => $maxTotalSize
            ], 413);
        }
        
        return $next($request);
    }
}
