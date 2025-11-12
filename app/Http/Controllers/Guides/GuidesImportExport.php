<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyContacts;
use App\Models\CompanyContactsPhones;
use App\Models\CompanyContactsEmail;
use App\Models\CompanyEmails;
use App\Models\CompanyStatus;
use App\Models\Sources;
use App\Models\Regions;
use App\Models\User;
use App\Models\Warehouses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GuidesImportExport extends Controller
{
    public function index()
    {
        return view('Guides.GuidesImportExportPage');
    }

    public function importCompanies(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Пропускаем первые 4 строки, заголовки начинаются с 5-й строки (индекс 4)
            $headerRowIndex = 4;
            $dataStartRowIndex = 5;

            if (count($rows) < $dataStartRowIndex + 1) {
                return redirect()->route('import-export.index')
                    ->with('error', 'Файл пуст или содержит недостаточно данных. Заголовки должны быть в 5-й строке.');
            }

            // Находим заголовки в 5-й строке (индекс 4)
            $headers = array_map('trim', $rows[$headerRowIndex]);
            
            // Столбцы с ID, которые нужно игнорировать
            $ignoredColumns = [
                'id (менеджера)',
                'id менеджера',
                'id(менеджера)',
                'id (склада)',
                'id склада',
                'id(склада)',
                'id (региона)',
                'id региона',
                'id(региона)',
                'id (источника)',
                'id источника',
                'id(источника)',
                'id (рег.представителя)',
                'id рег.представителя',
                'id(рег.представителя)',
                'id регионального представителя',
                'id (регионального представителя)',
            ];
            
            $headerMap = [];
            foreach ($headers as $index => $header) {
                $key = mb_strtolower(trim($header));
                
                // Пропускаем столбцы с ID
                $shouldIgnore = false;
                foreach ($ignoredColumns as $ignoredColumn) {
                    if (mb_strpos($key, mb_strtolower($ignoredColumn)) !== false) {
                        $shouldIgnore = true;
                        break;
                    }
                }
                
                if ($shouldIgnore) {
                    continue;
                }
                
                if (!isset($headerMap[$key])) {
                    $headerMap[$key] = [];
                }
                // Сохраняем индекс столбца (индекс 0 = столбец A)
                $headerMap[$key][] = $index;
            }

            // Сохраняем данные для отладки
            $debugData = [
                'skippedRows' => array_slice($rows, 0, $headerRowIndex),
                'note' => 'Столбцы с ID (менеджера, склада, региона, источника, рег.представителя) пропускаются при обработке. Индексы в headerMap соответствуют индексам в массиве (0 = столбец A)',
                'ignoredColumns' => $ignoredColumns,
                'allHeaders' => $headers,
                'headers' => array_keys($headerMap),
                'headerMap' => $headerMap,
                'headerMapWithColumnLetters' => array_map(function($indices) {
                    return array_map(function($idx) {
                        $colLetter = '';
                        $num = $idx;
                        while ($num >= 0) {
                            $colLetter = chr(65 + ($num % 26)) . $colLetter;
                            $num = intval($num / 26) - 1;
                        }
                        return $colLetter . ' (индекс ' . $idx . ')';
                    }, $indices);
                }, $headerMap),
                'totalRows' => count($rows),
                'dataRowsCount' => count($rows) - $dataStartRowIndex,
                'sampleRows' => []
            ];

            $results = [];
            $skipped = [];

            // Обрабатываем каждую строку начиная с 6-й (индекс 5)
            for ($i = $dataStartRowIndex; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Пропускаем пустые строки
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Сохраняем сырые данные строки для отладки (первые 5 строк данных)
                    if (count($debugData['sampleRows']) < 5) {
                        $debugData['sampleRows'][] = [
                            'rowNumber' => $i + 1,
                            'rawData' => $row,
                            'parsedData' => []
                        ];
                    }

                    // Получаем данные из строки
                    $managerName = $this->getCellValue($row, $headerMap, 'менеджер');
                    $supplierNumber = $this->getCellValue($row, $headerMap, '№ поставщика (текст)') 
                        ?: $this->getCellValue($row, $headerMap, 'номер поставщика (текст)')
                        ?: $this->getCellValue($row, $headerMap, 'номер поставщика');
                    $statusName = $this->getCellValue($row, $headerMap, 'статус поставщика');
                    $warehouseName = $this->getCellValue($row, $headerMap, 'склад поставщика (текст)')
                        ?: $this->getCellValue($row, $headerMap, 'склад поставщика');
                    $regionName = $this->getCellValue($row, $headerMap, 'регион');
                    $companyName = $this->getCellValue($row, $headerMap, 'название компании');
                    $sourceName = $this->getCellValue($row, $headerMap, 'источник контакта');
                    $regionalName = $this->getCellValue($row, $headerMap, 'фио регионала');
                    $inn = $this->getCellValue($row, $headerMap, 'инн') ?: '';
                    // Ищем адрес по разным возможным названиям
                    $address = $this->getCellValue($row, $headerMap, 'адрес компании') 
                        ?: $this->getCellValue($row, $headerMap, 'адреса')
                        ?: $this->getCellValue($row, $headerMap, 'адрес');
                    $mainAddress = mb_strtolower(trim($this->getCellValue($row, $headerMap, 'основной адрес'))) === 'да';
                    
                    // Контакт 1
                    $contact1Name = $this->getCellValue($row, $headerMap, 'фио контактного лица');
                    $contact1Phone1 = $this->getCellValue($row, $headerMap, 'телефон 1');
                    $contact1Phone2 = $this->getCellValue($row, $headerMap, 'телефон 2');
                    // Для контакта ищем email, который не относится к организации
                    $contact1Email = $this->getCellValue($row, $headerMap, 'email') ?: '';
                    $contact1Position = $this->getCellValue($row, $headerMap, 'должность');
                    $contact1Main = mb_strtolower(trim($this->getCellValue($row, $headerMap, 'основной контакт'))) === 'да';
                    
                    // Контакт 2 (ищем столбцы с суффиксом или в других позициях)
                    $contact2Name = $this->getCellValue($row, $headerMap, 'фио контактного лица', 1);
                    $contact2Phone1 = $this->getCellValue($row, $headerMap, 'телефон 1', 1);
                    $contact2Phone2 = $this->getCellValue($row, $headerMap, 'телефон 2', 1);
                    $contact2Email = $this->getCellValue($row, $headerMap, 'email', 1) ?: '';
                    $contact2Position = $this->getCellValue($row, $headerMap, 'должность', 1);
                    $contact2Main = mb_strtolower(trim($this->getCellValue($row, $headerMap, 'основной контакт', 1))) === 'да';
                    
                    $companyEmail = $this->getCellValue($row, $headerMap, 'email организации') ?: '';
                    $companyPhone = $this->getCellValue($row, $headerMap, 'телефон компании') ?: '';
                    $companySite = $this->getCellValue($row, $headerMap, 'сайт') ?: '';
                    $additionalEmails = $this->getCellValue($row, $headerMap, 'дополнительные email организации');
                    $commonInfo = $this->getCellValue($row, $headerMap, 'суть разговора') ?: '';

                    // Сохраняем распарсенные данные для отладки (первые 5 строк)
                    if (count($debugData['sampleRows']) > 0 && isset($debugData['sampleRows'][count($debugData['sampleRows']) - 1]) && 
                        $debugData['sampleRows'][count($debugData['sampleRows']) - 1]['rowNumber'] === $i + 1) {
                        $debugData['sampleRows'][count($debugData['sampleRows']) - 1]['parsedData'] = [
                            'managerName' => $managerName,
                            'supplierNumber' => $supplierNumber,
                            'statusName' => $statusName,
                            'warehouseName' => $warehouseName,
                            'regionName' => $regionName,
                            'companyName' => $companyName,
                            'sourceName' => $sourceName,
                            'regionalName' => $regionalName,
                            'inn' => $inn,
                            'address' => $address,
                            'mainAddress' => $mainAddress,
                            'contact1Name' => $contact1Name,
                            'contact1Phone1' => $contact1Phone1,
                            'contact1Phone2' => $contact1Phone2,
                            'contact1Email' => $contact1Email,
                            'contact1Position' => $contact1Position,
                            'contact1Main' => $contact1Main,
                            'contact2Name' => $contact2Name,
                            'contact2Phone1' => $contact2Phone1,
                            'contact2Phone2' => $contact2Phone2,
                            'contact2Email' => $contact2Email,
                            'contact2Position' => $contact2Position,
                            'contact2Main' => $contact2Main,
                            'companyEmail' => $companyEmail,
                            'companyPhone' => $companyPhone,
                            'companySite' => $companySite,
                            'additionalEmails' => $additionalEmails,
                            'commonInfo' => $commonInfo,
                        ];
                    }

                    // Форматируем номер поставщика: название склада + дефис + номер поставщика (например: ЧЛБ-001)
                    $sku = null;
                    if ($warehouseName && $supplierNumber) {
                        // Очищаем название склада от лишних символов, оставляем только буквы
                        $cleanWarehouseName = preg_replace('/[^A-ZА-Я]/u', '', strtoupper($warehouseName));
                        // Если название пустое, используем первые буквы
                        if (empty($cleanWarehouseName)) {
                            $cleanWarehouseName = mb_substr(strtoupper($warehouseName), 0, 3);
                        }
                        // Форматируем номер поставщика с ведущими нулями (минимум 3 цифры)
                        $formattedNumber = str_pad($supplierNumber, 3, '0', STR_PAD_LEFT);
                        $sku = $cleanWarehouseName . '-' . $formattedNumber;
                    } elseif ($supplierNumber) {
                        // Если нет названия склада, используем старый формат
                        $sku = '00' . $supplierNumber;
                    }

                    // Проверяем на дубликаты
                    $existingCompany = Company::where('sku', $sku)
                        ->orWhere('name', $companyName)
                        ->first();

                    if ($existingCompany) {
                        $skipped[] = [
                            'name' => $companyName,
                            'sku' => $sku,
                            'owner' => $managerName ?? 'Не указан',
                            'regional' => $regionalName ?? 'Не указан',
                            'warehouse' => $warehouseName ?? 'Не указан',
                            'reason' => 'Компания с таким артикулом или названием уже существует',
                            'skipped' => true
                        ];
                        continue;
                    }

                    // Находим связанные сущности по названию
                    $manager = User::where('name', $managerName)->first();
                    $status = CompanyStatus::where('name', $statusName)->first();
                    $warehouse = Warehouses::where('name', $warehouseName)->first();
                    $region = Regions::where('name', $regionName)->first();
                    $source = Sources::where('name', $sourceName)->first();
                    $regional = User::where('name', $regionalName)->first();

                    if (!$manager || !$status || !$warehouse || !$region || !$source || !$regional) {
                        $skipped[] = [
                            'name' => $companyName,
                            'sku' => $sku,
                            'owner' => $managerName ?? 'Не указан',
                            'regional' => $regionalName ?? 'Не указан',
                            'warehouse' => $warehouseName ?? 'Не указан',
                            'reason' => 'Не найдены связанные данные: ' . 
                                (!$manager ? 'Менеджер ' : '') .
                                (!$status ? 'Статус ' : '') .
                                (!$warehouse ? 'Склад ' : '') .
                                (!$region ? 'Регион ' : '') .
                                (!$source ? 'Источник ' : '') .
                                (!$regional ? 'Региональный представитель ' : ''),
                            'skipped' => true
                        ];
                        continue;
                    }

                    DB::beginTransaction();

                    // Создаем компанию
                    $company = Company::create([
                        'sku' => $sku,
                        'name' => $companyName,
                        'inn' => $inn,
                        'source_id' => $source->id,
                        'region_id' => $region->id,
                        'regional_user_id' => $regional->id,
                        'owner_user_id' => $manager->id,
                        'email' => $companyEmail,
                        'phone' => $companyPhone,
                        'site' => $companySite,
                        'common_info' => $commonInfo,
                        'company_status_id' => $status->id,
                    ]);

                    // Связываем со складом
                    $company->warehouses()->attach($warehouse->id);

                    // Создаем адрес
                    if ($address) {
                        CompanyAddress::create([
                            'company_id' => $company->id,
                            'address' => $address,
                            'main_address' => $mainAddress,
                        ]);
                    }

                    // Создаем контакты
                    $contacts = [];
                    if ($contact1Name) {
                        $contact1 = CompanyContacts::create([
                            'company_id' => $company->id,
                            'name' => $contact1Name,
                            'position' => $contact1Position ?: '',
                            'main_contact' => $contact1Main,
                        ]);

                        if ($contact1Phone1) {
                            CompanyContactsPhones::create([
                                'company_contact_id' => $contact1->id,
                                'phone' => $contact1Phone1,
                            ]);
                        }
                        if ($contact1Phone2) {
                            CompanyContactsPhones::create([
                                'company_contact_id' => $contact1->id,
                                'phone' => $contact1Phone2,
                            ]);
                        }
                        if ($contact1Email) {
                            CompanyContactsEmail::create([
                                'company_contact_id' => $contact1->id,
                                'email' => $contact1Email,
                                'is_primary' => true,
                            ]);
                        }
                        $contacts[] = $contact1;
                    }

                    if ($contact2Name) {
                        $contact2 = CompanyContacts::create([
                            'company_id' => $company->id,
                            'name' => $contact2Name,
                            'position' => $contact2Position ?: '',
                            'main_contact' => $contact2Main,
                        ]);

                        if ($contact2Phone1) {
                            CompanyContactsPhones::create([
                                'company_contact_id' => $contact2->id,
                                'phone' => $contact2Phone1,
                            ]);
                        }
                        if ($contact2Phone2) {
                            CompanyContactsPhones::create([
                                'company_contact_id' => $contact2->id,
                                'phone' => $contact2Phone2,
                            ]);
                        }
                        if ($contact2Email) {
                            CompanyContactsEmail::create([
                                'company_contact_id' => $contact2->id,
                                'email' => $contact2Email,
                                'is_primary' => true,
                            ]);
                        }
                        $contacts[] = $contact2;
                    }

                    // Создаем дополнительные email организации
                    if ($additionalEmails) {
                        $emails = array_filter(explode(',', $additionalEmails));
                        foreach ($emails as $email) {
                            $email = trim($email);
                            if ($email) {
                                CompanyEmails::create([
                                    'company_id' => $company->id,
                                    'email' => $email,
                                ]);
                            }
                        }
                    }

                    DB::commit();

                    $results[] = [
                        'name' => $company->name,
                        'sku' => $company->sku,
                        'owner' => $manager->name,
                        'regional' => $regional->name,
                        'warehouse' => $warehouse->name,
                        'status' => 'success',
                        'skipped' => false
                    ];

                } catch (\Exception $e) {
                    DB::rollBack();
                    $skipped[] = [
                        'name' => $companyName ?? 'Неизвестно',
                        'sku' => $sku ?? 'Неизвестно',
                        'owner' => $managerName ?? 'Не указан',
                        'regional' => $regionalName ?? 'Не указан',
                        'warehouse' => $warehouseName ?? 'Не указан',
                        'reason' => 'Ошибка: ' . $e->getMessage(),
                        'skipped' => true
                    ];
                }
            }

            // Объединяем результаты и пропущенные компании
            $allResults = array_merge($results, $skipped);

            return redirect()->route('import-export.index')->with([
                'import_results' => $allResults,
                'import_debug_data' => $debugData,
                'success' => 'Импорт завершен. Создано: ' . count($results) . ', Пропущено: ' . count($skipped)
            ]);

        } catch (\Exception $e) {
            return redirect()->route('import-export.index')
                ->with('error', 'Ошибка при импорте: ' . $e->getMessage());
        }
    }

    /**
     * Получает значение ячейки по названию столбца
     */
    private function getCellValue($row, $headerMap, $headerName, $index = 0)
    {
        $key = mb_strtolower(trim($headerName));
        
        if (isset($headerMap[$key]) && is_array($headerMap[$key])) {
            if (isset($headerMap[$key][$index])) {
                $colIndex = $headerMap[$key][$index];
                return isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
            }
        }
        
        return null;
    }

    public function exportCompanies()
    {
        // TODO: Реализовать логику экспорта компаний
        return redirect()->route('import-export.index')->with('success', 'Экспорт компаний начат');
    }

    public function exportProducts()
    {
        // TODO: Реализовать логику экспорта товаров
        return redirect()->route('import-export.index')->with('success', 'Экспорт товаров начат');
    }
}

