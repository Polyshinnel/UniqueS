<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductCategories;
use App\Models\Advertisement;
use App\Models\AdvertisementMedia;
use App\Models\AdvertisementsTags;
use App\Models\AdvertisementStatus;
use App\Models\ProductCheckStatuses;
use App\Models\ProductInstallStatuses;
use App\Models\ProductState;
use App\Models\ProductAvailable;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMElement;

class ExportAdvertisementsToXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:export-xml {--path= : Путь для сохранения XML файла}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Экспортирует все объявления в XML файл для сторонней системы';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаю экспорт объявлений в XML...');

        // Определяем путь для сохранения файла
        $filePath = $this->option('path') 
            ?: storage_path('app/public/exports/advertisements.xml');

        // Создаем директорию, если её нет
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Создаем XML документ
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // Создаем корневой элемент
        $root = $dom->createElement('advertisements_export');
        $root->setAttribute('export_date', now()->format('Y-m-d H:i:s'));
        $dom->appendChild($root);

        // Добавляем категории
        $this->info('Экспортирую категории...');
        $categoriesElement = $dom->createElement('categories');
        $categories = ProductCategories::where('active', true)->get();
        
        foreach ($categories as $category) {
            $categoryElement = $dom->createElement('category');
            $categoryElement->setAttribute('id', (string)$category->id);
            $categoryElement->appendChild($dom->createElement('name', htmlspecialchars($category->name, ENT_XML1, 'UTF-8')));
            $categoryElement->appendChild($dom->createElement('parent_id', $category->parent_id ? (string)$category->parent_id : ''));
            $categoriesElement->appendChild($categoryElement);
        }
        $root->appendChild($categoriesElement);

        // Добавляем статусы объявлений
        $this->info('Экспортирую статусы объявлений...');
        $statusesElement = $dom->createElement('advertisement_statuses');
        $advertisementStatuses = AdvertisementStatus::all();
        
        foreach ($advertisementStatuses as $status) {
            $statusElement = $dom->createElement('status');
            $statusElement->setAttribute('id', (string)$status->id);
            $statusElement->appendChild($dom->createElement('name', htmlspecialchars($status->name ?? '', ENT_XML1, 'UTF-8')));
            if (isset($status->color)) {
                $statusElement->appendChild($dom->createElement('color', htmlspecialchars($status->color, ENT_XML1, 'UTF-8')));
            }
            $statusesElement->appendChild($statusElement);
        }
        $root->appendChild($statusesElement);

        // Добавляем статусы проверки
        $this->info('Экспортирую статусы проверки...');
        $checkStatusesElement = $dom->createElement('check_statuses');
        $checkStatuses = ProductCheckStatuses::all();
        
        foreach ($checkStatuses as $checkStatus) {
            $checkStatusElement = $dom->createElement('status');
            $checkStatusElement->setAttribute('id', (string)$checkStatus->id);
            $checkStatusElement->appendChild($dom->createElement('name', htmlspecialchars($checkStatus->name ?? '', ENT_XML1, 'UTF-8')));
            if (isset($checkStatus->color)) {
                $checkStatusElement->appendChild($dom->createElement('color', htmlspecialchars($checkStatus->color, ENT_XML1, 'UTF-8')));
            }
            $checkStatusesElement->appendChild($checkStatusElement);
        }
        $root->appendChild($checkStatusesElement);

        // Добавляем статусы погрузки/демонтажа
        $this->info('Экспортирую статусы погрузки и демонтажа...');
        $installStatusesElement = $dom->createElement('install_statuses');
        $installStatuses = ProductInstallStatuses::all();
        
        foreach ($installStatuses as $installStatus) {
            $installStatusElement = $dom->createElement('status');
            $installStatusElement->setAttribute('id', (string)$installStatus->id);
            $installStatusElement->appendChild($dom->createElement('name', htmlspecialchars($installStatus->name ?? '', ENT_XML1, 'UTF-8')));
            if (isset($installStatus->color)) {
                $installStatusElement->appendChild($dom->createElement('color', htmlspecialchars($installStatus->color, ENT_XML1, 'UTF-8')));
            }
            $installStatusesElement->appendChild($installStatusElement);
        }
        $root->appendChild($installStatusesElement);

        // Загружаем все объявления с необходимыми связями
        $this->info('Загружаю объявления...');
        $advertisements = Advertisement::with([
            'category',
            'status',
            'mediaOrdered',
            'tags',
            'productState',
            'productAvailable',
            'product.warehouse.regions',
            'product.company.addresses',
            'creator.role',
            'product.owner.role',
            'product.regional.role'
        ])->get();

        // Добавляем объявления
        $this->info('Экспортирую объявления...');
        $advertisementsElement = $dom->createElement('advertisements');
        $bar = $this->output->createProgressBar($advertisements->count());
        $bar->start();

        foreach ($advertisements as $advertisement) {
            $adElement = $dom->createElement('advertisement');
            $adElement->setAttribute('id', (string)$advertisement->id);

            // Основная информация
            $adElement->appendChild($dom->createElement('title', htmlspecialchars($advertisement->title ?? '', ENT_XML1, 'UTF-8')));
            $adElement->appendChild($dom->createElement('product_id', $advertisement->product_id ?? ''));
            
            // Артикул объявления (равен артикулу товара)
            if ($advertisement->product && isset($advertisement->product->sku)) {
                $adElement->appendChild($dom->createElement('sku', htmlspecialchars($advertisement->product->sku, ENT_XML1, 'UTF-8')));
            }
            
            // Категория
            if ($advertisement->category) {
                $categoryElement = $dom->createElement('category');
                $categoryElement->setAttribute('id', (string)$advertisement->category->id);
                $categoryElement->appendChild($dom->createTextNode(htmlspecialchars($advertisement->category->name ?? '', ENT_XML1, 'UTF-8')));
                $adElement->appendChild($categoryElement);
            }

            // Статус объявления
            if ($advertisement->status) {
                $statusElement = $dom->createElement('status');
                $statusId = $advertisement->status->id ?? 0;
                $statusElement->setAttribute('id', (string)$statusId);
                $statusElement->appendChild($dom->createTextNode(htmlspecialchars($advertisement->status->name ?? '', ENT_XML1, 'UTF-8')));
                $adElement->appendChild($statusElement);
            }

            // Описания и характеристики
            $adElement->appendChild($this->createCDataElement($dom, 'main_characteristics', $advertisement->main_characteristics ?? ''));
            $adElement->appendChild($this->createCDataElement($dom, 'complectation', $advertisement->complectation ?? ''));
            $adElement->appendChild($this->createCDataElement($dom, 'technical_characteristics', $advertisement->technical_characteristics ?? ''));
            $adElement->appendChild($this->createCDataElement($dom, 'main_info', $advertisement->main_info ?? ''));
            $adElement->appendChild($this->createCDataElement($dom, 'additional_info', $advertisement->additional_info ?? ''));

            // Информация о проверке (расшифрованная)
            $checkData = $advertisement->check_data ?? [];
            $checkElement = $dom->createElement('check');
            if (!empty($checkData['status_id'])) {
                $checkStatus = ProductCheckStatuses::find($checkData['status_id']);
                if ($checkStatus) {
                    $checkElement->appendChild($dom->createElement('status', htmlspecialchars($checkStatus->name, ENT_XML1, 'UTF-8')));
                }
            } else {
                $checkElement->appendChild($dom->createElement('status', 'Не указан'));
            }
            $checkElement->appendChild($this->createCDataElement($dom, 'comment', $checkData['comment'] ?? ''));
            $adElement->appendChild($checkElement);

            // Информация о погрузке (расшифрованная)
            $loadingData = $advertisement->loading_data ?? [];
            $loadingElement = $dom->createElement('loading');
            if (!empty($loadingData['status_id'])) {
                $loadingStatus = ProductInstallStatuses::find($loadingData['status_id']);
                if ($loadingStatus) {
                    $loadingElement->appendChild($dom->createElement('status', htmlspecialchars($loadingStatus->name, ENT_XML1, 'UTF-8')));
                }
            } else {
                $loadingElement->appendChild($dom->createElement('status', 'Не указан'));
            }
            $loadingElement->appendChild($this->createCDataElement($dom, 'comment', $loadingData['comment'] ?? ''));
            $adElement->appendChild($loadingElement);

            // Информация о демонтаже (расшифрованная)
            $removalData = $advertisement->removal_data ?? [];
            $removalElement = $dom->createElement('removal');
            if (!empty($removalData['status_id'])) {
                $removalStatus = ProductInstallStatuses::find($removalData['status_id']);
                if ($removalStatus) {
                    $removalElement->appendChild($dom->createElement('status', htmlspecialchars($removalStatus->name, ENT_XML1, 'UTF-8')));
                }
            } else {
                $removalElement->appendChild($dom->createElement('status', 'Не указан'));
            }
            $removalElement->appendChild($this->createCDataElement($dom, 'comment', $removalData['comment'] ?? ''));
            $adElement->appendChild($removalElement);

            // Цена и информация о продаже
            $priceElement = $dom->createElement('price');
            $priceElement->appendChild($dom->createElement('adv_price', $advertisement->adv_price ?? '0'));
            $priceElement->appendChild($this->createCDataElement($dom, 'adv_price_comment', $advertisement->adv_price_comment ?? ''));
            $priceElement->appendChild($dom->createElement('show_price', $advertisement->show_price ? '1' : '0'));
            $adElement->appendChild($priceElement);

            // Состояние и доступность товара
            if ($advertisement->productState) {
                $stateElement = $dom->createElement('product_state');
                $stateElement->setAttribute('id', (string)$advertisement->productState->id);
                $stateElement->appendChild($dom->createTextNode(htmlspecialchars($advertisement->productState->name ?? '', ENT_XML1, 'UTF-8')));
                $adElement->appendChild($stateElement);
            }

            if ($advertisement->productAvailable) {
                $availableElement = $dom->createElement('product_available');
                $availableElement->setAttribute('id', (string)$advertisement->productAvailable->id);
                $availableElement->appendChild($dom->createTextNode(htmlspecialchars($advertisement->productAvailable->name ?? '', ENT_XML1, 'UTF-8')));
                $adElement->appendChild($availableElement);
            }

            // Локация
            $locationElement = $dom->createElement('location');
            
            // Склад
            if ($advertisement->product && $advertisement->product->warehouse) {
                $warehouseElement = $dom->createElement('warehouse');
                $warehouseElement->setAttribute('id', (string)$advertisement->product->warehouse->id);
                $warehouseElement->appendChild($dom->createElement('name', htmlspecialchars($advertisement->product->warehouse->name ?? '', ENT_XML1, 'UTF-8')));
                $locationElement->appendChild($warehouseElement);
                
                // Регионы склада
                if ($advertisement->product->warehouse->regions && $advertisement->product->warehouse->regions->count() > 0) {
                    $regionsElement = $dom->createElement('regions');
                    foreach ($advertisement->product->warehouse->regions as $region) {
                        $regionElement = $dom->createElement('region');
                        $regionElement->setAttribute('id', (string)$region->id);
                        $regionElement->appendChild($dom->createElement('name', htmlspecialchars($region->name ?? '', ENT_XML1, 'UTF-8')));
                        if (isset($region->city_name)) {
                            $regionElement->appendChild($dom->createElement('city_name', htmlspecialchars($region->city_name, ENT_XML1, 'UTF-8')));
                        }
                        $regionsElement->appendChild($regionElement);
                    }
                    $locationElement->appendChild($regionsElement);
                }
            }
            
            // Адреса компании
            if ($advertisement->product && $advertisement->product->company && $advertisement->product->company->addresses) {
                $companyAddressesElement = $dom->createElement('company_addresses');
                foreach ($advertisement->product->company->addresses as $companyAddress) {
                    $addressElement = $dom->createElement('address');
                    $addressElement->setAttribute('id', (string)$companyAddress->id);
                    $addressElement->appendChild($dom->createElement('address', htmlspecialchars($companyAddress->address ?? '', ENT_XML1, 'UTF-8')));
                    $addressElement->appendChild($dom->createElement('main_address', $companyAddress->main_address ? '1' : '0'));
                    $companyAddressesElement->appendChild($addressElement);
                }
                $locationElement->appendChild($companyAddressesElement);
            }
            
            // Адрес товара
            if ($advertisement->product && !empty($advertisement->product->product_address)) {
                $productAddressElement = $dom->createElement('product_address', htmlspecialchars($advertisement->product->product_address, ENT_XML1, 'UTF-8'));
                $locationElement->appendChild($productAddressElement);
            }
            
            // Всегда добавляем элемент локации
            $adElement->appendChild($locationElement);

            // Контакты менеджера (ответственного за объявление)
            $managerElement = $dom->createElement('manager');
            
            // Создатель объявления (основной ответственный)
            if ($advertisement->creator) {
                $creatorElement = $dom->createElement('creator');
                $creatorElement->setAttribute('id', (string)$advertisement->creator->id);
                $creatorElement->appendChild($dom->createElement('name', htmlspecialchars($advertisement->creator->name ?? '', ENT_XML1, 'UTF-8')));
                $creatorElement->appendChild($dom->createElement('email', htmlspecialchars($advertisement->creator->email ?? '', ENT_XML1, 'UTF-8')));
                $creatorElement->appendChild($dom->createElement('phone', htmlspecialchars($advertisement->creator->phone ?? '', ENT_XML1, 'UTF-8')));
                if ($advertisement->creator->role) {
                    $creatorElement->appendChild($dom->createElement('role', htmlspecialchars($advertisement->creator->role->name ?? '', ENT_XML1, 'UTF-8')));
                }
                $creatorElement->appendChild($dom->createElement('has_whatsapp', $advertisement->creator->has_whatsapp ? '1' : '0'));
                $creatorElement->appendChild($dom->createElement('has_telegram', $advertisement->creator->has_telegram ? '1' : '0'));
                $managerElement->appendChild($creatorElement);
            }
            
            // Владелец товара
            if ($advertisement->product && $advertisement->product->owner) {
                $ownerElement = $dom->createElement('product_owner');
                $ownerElement->setAttribute('id', (string)$advertisement->product->owner->id);
                $ownerElement->appendChild($dom->createElement('name', htmlspecialchars($advertisement->product->owner->name ?? '', ENT_XML1, 'UTF-8')));
                $ownerElement->appendChild($dom->createElement('email', htmlspecialchars($advertisement->product->owner->email ?? '', ENT_XML1, 'UTF-8')));
                $ownerElement->appendChild($dom->createElement('phone', htmlspecialchars($advertisement->product->owner->phone ?? '', ENT_XML1, 'UTF-8')));
                if ($advertisement->product->owner->role) {
                    $ownerElement->appendChild($dom->createElement('role', htmlspecialchars($advertisement->product->owner->role->name ?? '', ENT_XML1, 'UTF-8')));
                }
                $ownerElement->appendChild($dom->createElement('has_whatsapp', $advertisement->product->owner->has_whatsapp ? '1' : '0'));
                $ownerElement->appendChild($dom->createElement('has_telegram', $advertisement->product->owner->has_telegram ? '1' : '0'));
                $managerElement->appendChild($ownerElement);
            }
            
            // Региональный представитель
            if ($advertisement->product && $advertisement->product->regional) {
                $regionalElement = $dom->createElement('regional_representative');
                $regionalElement->setAttribute('id', (string)$advertisement->product->regional->id);
                $regionalElement->appendChild($dom->createElement('name', htmlspecialchars($advertisement->product->regional->name ?? '', ENT_XML1, 'UTF-8')));
                $regionalElement->appendChild($dom->createElement('email', htmlspecialchars($advertisement->product->regional->email ?? '', ENT_XML1, 'UTF-8')));
                $regionalElement->appendChild($dom->createElement('phone', htmlspecialchars($advertisement->product->regional->phone ?? '', ENT_XML1, 'UTF-8')));
                if ($advertisement->product->regional->role) {
                    $regionalElement->appendChild($dom->createElement('role', htmlspecialchars($advertisement->product->regional->role->name ?? '', ENT_XML1, 'UTF-8')));
                }
                $regionalElement->appendChild($dom->createElement('has_whatsapp', $advertisement->product->regional->has_whatsapp ? '1' : '0'));
                $regionalElement->appendChild($dom->createElement('has_telegram', $advertisement->product->regional->has_telegram ? '1' : '0'));
                $managerElement->appendChild($regionalElement);
            }
            
            // Всегда добавляем элемент менеджера
            $adElement->appendChild($managerElement);

            // Медиафайлы
            $mediaElement = $dom->createElement('media');
            foreach ($advertisement->mediaOrdered as $media) {
                $mediaItemElement = $dom->createElement('media_item');
                $mediaItemElement->setAttribute('id', (string)$media->id);
                $mediaItemElement->setAttribute('type', $media->file_type ?? '');
                $mediaItemElement->appendChild($dom->createElement('file_name', htmlspecialchars($media->file_name, ENT_XML1, 'UTF-8')));
                $mediaItemElement->appendChild($dom->createElement('file_path', htmlspecialchars($media->file_path, ENT_XML1, 'UTF-8')));
                $fileUrl = config('app.url') . '/storage/' . $media->file_path;
                $mediaItemElement->appendChild($dom->createElement('file_url', htmlspecialchars($fileUrl, ENT_XML1, 'UTF-8')));
                $mediaItemElement->appendChild($dom->createElement('mime_type', htmlspecialchars($media->mime_type ?? '', ENT_XML1, 'UTF-8')));
                $mediaItemElement->appendChild($dom->createElement('file_size', $media->file_size ?? '0'));
                $mediaItemElement->appendChild($dom->createElement('sort_order', $media->sort_order ?? '0'));
                $mediaItemElement->appendChild($dom->createElement('is_selected_from_product', $media->is_selected_from_product ? '1' : '0'));
                $mediaItemElement->appendChild($dom->createElement('is_main_image', ($advertisement->main_img == $media->id) ? '1' : '0'));
                $mediaElement->appendChild($mediaItemElement);
            }
            $adElement->appendChild($mediaElement);

            // Теги
            $tagsElement = $dom->createElement('tags');
            foreach ($advertisement->tags as $tag) {
                $tagElement = $dom->createElement('tag', htmlspecialchars($tag->tag, ENT_XML1, 'UTF-8'));
                $tagsElement->appendChild($tagElement);
            }
            $adElement->appendChild($tagsElement);

            // Даты
            $datesElement = $dom->createElement('dates');
            if ($advertisement->published_at) {
                $datesElement->appendChild($dom->createElement('published_at', $advertisement->published_at->format('Y-m-d H:i:s')));
            }
            $datesElement->appendChild($dom->createElement('created_at', $advertisement->created_at->format('Y-m-d H:i:s')));
            $datesElement->appendChild($dom->createElement('updated_at', $advertisement->updated_at->format('Y-m-d H:i:s')));
            $adElement->appendChild($datesElement);

            $advertisementsElement->appendChild($adElement);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $root->appendChild($advertisementsElement);

        // Сохраняем XML файл
        $this->info('Сохраняю XML файл...');
        $dom->save($filePath);

        $this->info("Экспорт завершен! Файл сохранен: {$filePath}");
        $this->info("Всего объявлений экспортировано: " . $advertisements->count());

        return 0;
    }

    /**
     * Создает элемент с CDATA секцией
     */
    private function createCDataElement(DOMDocument $dom, string $name, string $value): DOMElement
    {
        $element = $dom->createElement($name);
        $cdata = $dom->createCDATASection($value);
        $element->appendChild($cdata);
        return $element;
    }
}

