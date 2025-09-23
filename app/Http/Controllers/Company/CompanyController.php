<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyContacts;
use App\Models\CompanyContactsEmail;
use App\Models\CompanyContactsPhones;
use App\Models\CompanyLog;
use App\Models\CompanyActions;
use App\Models\LogType;
use App\Models\Regions;
use App\Models\Sources;
use App\Models\User;
use App\Models\Warehouses;
use App\Models\ProductStatus;
use App\Models\ProductLog;
use App\Models\ProductAction;
use App\Models\AdvLog;
use App\Models\AdvAction;
use App\Models\AdvertisementStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Company::with([
            'contacts' => function($query) {
                $query->where('main_contact', true);
            },
            'contacts.phones',
            'addresses' => function($query) {
                $query->where('main_address', true);
            },
            'regional',
            'owner',
            'status',
            'region',
            'source',
            'actions' => function($query) {
                $query->where('status', false)
                      ->orderBy('expired_at', 'asc');
            }
        ]);

        // Фильтрация компаний в зависимости от роли пользователя
        if ($user && $user->role) {
            $canViewCompanies = $user->role->can_view_companies;
            
            if ($canViewCompanies === 1) {
                // Показываем только компании, где пользователь является владельцем
                $query->where('owner_user_id', $user->id);
            } elseif ($canViewCompanies === 0 && $user->role->name === 'Региональный представитель') {
                // Для регионального представителя показываем компании, где он указан как региональный
                $query->where('regional_user_id', $user->id);
            } elseif ($canViewCompanies === 3) {
                // Показываем все компании (без дополнительных ограничений)
                // Query остается без изменений
            } else {
                // Для других значений (0, 2) показываем пустой список
                $query->whereRaw('1 = 0'); // Всегда false
            }
        } else {
            // Если пользователь не авторизован или у него нет роли, показываем пустой список
            $query->whereRaw('1 = 0'); // Всегда false
        }

        // Применяем фильтры
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (request('status_id')) {
            $query->where('company_status_id', request('status_id'));
        }

        if (request('region_id')) {
            $query->where('region_id', request('region_id'));
        }

        if (request('source_id')) {
            $query->where('source_id', request('source_id'));
        }

        if (request('regional_id')) {
            $query->where('regional_user_id', request('regional_id'));
        }

        // Фильтр по менеджеру (только для администраторов)
        if (request('owner_id') && $user && $user->role && $user->role->can_view_companies === 3) {
            $query->where('owner_user_id', request('owner_id'));
        }

        $companies = $query->orderBy('id', 'desc')->paginate(4);

        // Данные для фильтров
        $filterData = [
            'statuses' => \App\Models\CompanyStatus::all(),
            'regions' => $this->getUserRegions(),
            'sources' => Sources::all(),
            'regionals' => User::where('role_id', 3)
                ->where('active', true)
                ->get(),
            'owners' => $user && $user->role && $user->role->can_view_companies === 3 
                ? User::where('active', true)
                    ->whereIn('id', function($query) {
                        $query->select('owner_user_id')
                              ->from('companies')
                              ->whereNotNull('owner_user_id');
                    })
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect()
        ];

        $warehouses = Warehouses::all();
        $sources = Sources::all();
        $regionals = User::where('role_id', 3)
            ->where('active', true)
            ->get();
        $regions = $this->getUserRegions();

        // Определяем права пользователя на создание компаний
        $canCreate = $user && $user->role && $user->role->name !== 'Региональный представитель';

        // Определяем, показывать ли фильтры (всем кроме региональных представителей)
        $showFilters = $user && $user->role && $user->role->name !== 'Региональный представитель';

        return view('Company.CompanyPage', compact('companies', 'warehouses', 'sources', 'regionals', 'regions', 'canCreate', 'filterData', 'showFilters'));
    }

    public function create()
    {
        // Получаем склады, доступные текущему пользователю
        $userWarehouses = $this->getUserWarehouses();
        
        // Загружаем склады с их регионами
        $warehouses = $userWarehouses->load('regions');
            
        $sources = Sources::all();
        // Не загружаем всех региональных представителей, они будут загружены через AJAX
        $regionals = collect();

        return view('Company.CompanyCreatePage', compact('warehouses', 'sources', 'regionals'));
    }

    public function store(Request $request)
    {
        // Проверяем права пользователя на создание компаний
        $user = auth()->user();
        if (!$user || !$user->role || $user->role->can_view_companies < 1) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'У вас нет прав на создание компаний']);
        }

        $validated = $request->validate([
            'sku' => 'nullable|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'source_id' => 'required|exists:sources,id',
            'region_id' => 'required|exists:users,id',
            'region' => 'required|exists:regions,id',
            'inn' => 'nullable|string',
            'name' => 'required|string',
            'addresses' => 'required|array',
            'addresses.*' => 'required|string',
            'main_address' => 'array',
            'contact_name' => 'required|array',
            'contact_name.*' => 'required|string',
            'phones' => 'required|array',
            'phones.*' => 'required|array',
            'phones.*.*' => 'required|string',
            'contact_emails' => 'array',
            'contact_emails.*' => 'array',
            'contact_emails.*.*' => 'nullable|email',
            'position' => 'required|array',
            'position.*' => 'required|string',
            'main_contact' => 'array',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company_emails' => 'array',
            'company_emails.*' => 'nullable|email',
            'site' => 'nullable|url',
            'common_info' => 'required|string',
        ]);

        // Проверяем, что выбранный склад доступен пользователю
        $userWarehouses = $this->getUserWarehouses();
        $warehouse = $userWarehouses->find($validated['warehouse_id']);
        if (!$warehouse || !$warehouse->regions->count()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['warehouse_id' => 'Выбранный склад недоступен или не прикреплен к региону']);
        }
        
        // Проверяем, что выбранный регион принадлежит складу
        $selectedRegion = $warehouse->regions->where('id', $validated['region'])->first();
        if (!$selectedRegion) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['region' => 'Выбранный регион не принадлежит выбранному складу']);
        }

        // Проверка доступности региона для пользователя больше не нужна,
        // так как пользователи теперь привязываются к складам, а не к регионам напрямую

        // Проверяем, что выбранный региональный представитель имеет доступ к складу
        $regional = User::where('id', $validated['region_id'])
            ->where('role_id', 3)
            ->where('active', true)
            ->whereHas('warehouses', function($query) use ($validated) {
                $query->where('warehouses.id', $validated['warehouse_id']);
            })
            ->first();

        if (!$regional) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['region_id' => 'Выбранный региональный представитель не имеет доступа к складу']);
        }

        try {
            // Генерируем артикул, если он не заполнен
            $sku = $validated['sku'];
            if (empty($sku)) {
                $warehouse = Warehouses::find($validated['warehouse_id']);
                $sku = $this->generateUniqueSku($warehouse->name);
            }

            $company = Company::create([
                'sku' => $sku,
                'name' => $validated['name'],
                'inn' => $validated['inn'],
                'source_id' => $validated['source_id'],
                'region_id' => $validated['region'],
                'regional_user_id' => $validated['region_id'],
                'owner_user_id' => auth()->id(),
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'site' => $validated['site'],
                'common_info' => $validated['common_info'],
                'company_status_id' => 1,
            ]);

            // Создание связи компании со складом
            $company->warehouses()->attach($validated['warehouse_id']);

            // Сохранение адресов
            foreach ($validated['addresses'] as $index => $address) {
                CompanyAddress::create([
                    'company_id' => $company->id,
                    'address' => $address,
                    'main_address' => isset($validated['main_address'][$index]) && $validated['main_address'][$index],
                ]);
            }

            // Сохранение контактных лиц
            foreach ($validated['contact_name'] as $index => $contactName) {
                $contact = CompanyContacts::create([
                    'company_id' => $company->id,
                    'name' => $contactName,
                    'position' => $validated['position'][$index],
                    'email' => $validated['email'],
                    'main_contact' => isset($validated['main_contact'][$index]) && $validated['main_contact'][$index],
                ]);

                // Сохранение телефонов для каждого контакта
                if (isset($validated['phones'][$index])) {
                    foreach ($validated['phones'][$index] as $phone) {
                        CompanyContactsPhones::create([
                            'company_contact_id' => $contact->id,
                            'phone' => $phone,
                        ]);
                    }
                }

                // Сохранение email для каждого контакта
                if (isset($validated['contact_emails'][$index])) {
                    foreach ($validated['contact_emails'][$index] as $emailIndex => $email) {
                        if (!empty($email)) {
                            CompanyContactsEmail::create([
                                'company_contact_id' => $contact->id,
                                'email' => $email,
                                'is_primary' => $emailIndex === 0, // Первый email считается основным
                            ]);
                        }
                    }
                }
            }

            // Сохранение дополнительных email компании
            if (isset($validated['company_emails'])) {
                foreach ($validated['company_emails'] as $email) {
                    if (!empty($email)) {
                        \App\Models\CompanyEmails::create([
                            'company_id' => $company->id,
                            'email' => $email,
                        ]);
                    }
                }
            }

            // Создание записи в логе
            $systemLogType = LogType::where('name', 'Системный')->first();
            if ($systemLogType) {
                $logText = "Создана компания {$company->name}, пользователем {$user->name}";
                CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => null,
                    'log' => $logText,
                    'type_id' => $systemLogType->id,
                ]);
            }

            // Создание действия для компании
            CompanyActions::create([
                'company_id' => $company->id,
                'user_id' => auth()->id(),
                'action' => 'Отправить регионала, уточнить список и цены на оборудование',
                'expired_at' => now()->addDays(7),
                'status' => false,
            ]);

            return redirect()->route('companies.show', $company)
                ->with('success', 'Компания успешно создана');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании компании: ' . $e->getMessage()]);
        }
    }

    /**
     * Генерирует уникальный артикул для компании
     */
    private function generateUniqueSku($warehouseName)
    {
        // Очищаем название склада от лишних символов
        $cleanWarehouseName = preg_replace('/[^A-ZА-Я]/u', '', strtoupper($warehouseName));
        
        // Если название пустое, используем "COMP"
        if (empty($cleanWarehouseName)) {
            $cleanWarehouseName = 'COMP';
        }
        
        // Ищем последний артикул с таким префиксом
        $lastCompany = Company::where('sku', 'LIKE', $cleanWarehouseName . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(sku, "-", -1) AS UNSIGNED) DESC')
            ->first();
        
        if ($lastCompany) {
            // Извлекаем номер из последнего артикула
            $lastNumber = (int) substr($lastCompany->sku, strrpos($lastCompany->sku, '-') + 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Форматируем номер с ведущими нулями
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        return $cleanWarehouseName . '-' . $formattedNumber;
    }

    /**
     * Получает следующий доступный номер артикула для склада
     */
    public function getNextSku($warehouseName)
    {
        $nextSku = $this->generateUniqueSku($warehouseName);
        
        return response()->json([
            'next_sku' => $nextSku
        ]);
    }

    public function show(Company $company)
    {
        // Проверяем права пользователя на просмотр данной компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            abort(403, 'Доступ запрещен');
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может видеть только свои компании
            if ($company->owner_user_id !== $user->id) {
                abort(403, 'Доступ запрещен');
            }
        } elseif ($canViewCompanies === 0 && $user->role->name === 'Региональный представитель') {
            // Региональный представитель может видеть компании, где он указан как региональный
            if ($company->regional_user_id !== $user->id) {
                abort(403, 'Доступ запрещен');
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на просмотр компаний
            abort(403, 'Доступ запрещен');
        }

        $company->load([
            'contacts' => function($query) {
                $query->with(['phones', 'emails']);
            },
            'addresses',
            'emails',
            'regional',
            'owner',
            'status',
            'region',
            'source'
        ]);

        // Получаем последний лог компании
        $lastLog = CompanyLog::where('company_id', $company->id)
            ->with(['type', 'user'])
            ->latest()
            ->first();

        // Получаем первое невыполненное действие компании (сортировка по сроку выполнения ASC)
        $lastAction = CompanyActions::where('company_id', $company->id)
            ->where('status', false)
            ->orderBy('expired_at', 'asc')
            ->first();

        $statuses = \App\Models\CompanyStatus::all();

        // Определяем права пользователя на редактирование
        $canEdit = $user->role->can_view_companies === 3 || 
                   ($user->role->can_view_companies === 1 && $company->owner_user_id === $user->id);

        return view('Company.CompanyShowPage', compact('company', 'statuses', 'lastLog', 'lastAction', 'canEdit'));
    }

    public function updateStatus(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление статуса компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies === 0 && $user->role->name === 'Региональный представитель') {
            // Региональный представитель не может обновлять статус компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:company_statuses,id',
            'comment' => 'required|string|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Обновляем статус компании
            $oldStatus = $company->status;
            $company->update([
                'company_status_id' => $validated['status_id']
            ]);

            $company->load('status');

            // Получаем новый статус компании
            $newStatus = $company->status;

            // Специальная обработка для статуса "Холд"
            if ($newStatus->name === 'Холд') {
                $this->handleCompanyHoldStatus($company, $user);
            }

            // Специальная обработка для статуса "Отказ"
            if ($newStatus->name === 'Отказ') {
                $this->handleCompanyRefuseStatus($company, $user);
            }

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Смена статуса с '{$oldStatus->name}' на '{$newStatus->name}'. Комментарий: {$validated['comment']}";
                
                // Добавляем дополнительную информацию в лог
                if (isset($productsLogText)) {
                    $logText .= $productsLogText;
                }
                if (isset($actionLogText)) {
                    $logText .= $actionLogText;
                }

                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => $company->status,
                'message' => 'Статус успешно обновлен',
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении статуса: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получает региональных представителей для выбранного склада
     */
    public function getRegionalsByWarehouse($warehouseId, $regionId = null)
    {
        // Проверяем, что склад доступен текущему пользователю
        $userWarehouses = $this->getUserWarehouses();
        
        $warehouse = $userWarehouses->find($warehouseId);
        
        if (!$warehouse) {
            return response()->json([]);
        }
        
        // Получаем региональных представителей, которые имеют доступ к выбранному складу
        $regionals = User::where('role_id', 3)
            ->where('active', true)
            ->whereHas('warehouses', function($query) use ($warehouse) {
                $query->where('warehouses.id', $warehouse->id);
            })
            ->get(['id', 'name']);

        return response()->json($regionals);
    }

    /**
     * Получает регионы склада
     */
    public function getWarehouseRegions($warehouseId)
    {
        // Проверяем, что склад доступен текущему пользователю
        $userWarehouses = $this->getUserWarehouses();
        
        $warehouse = $userWarehouses->find($warehouseId);
        
        if (!$warehouse || !$warehouse->regions->count()) {
            return response()->json(['regions' => []]);
        }
        
        $regions = $warehouse->regions->map(function($region) {
            return [
                'id' => $region->id,
                'name' => $region->name
            ];
        });
        
        return response()->json([
            'regions' => $regions
        ]);
    }

    /**
     * Получает информацию о компании (склад, регион, адреса)
     */
    public function getCompanyInfo(Company $company)
    {
        // Проверяем права пользователя
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        if ($user->role->name === 'Региональный представитель') {
            // Для регионального представителя проверяем, что он назначен как региональный представитель
            if ($company->regional_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($user->role->can_view_companies === 1) {
            // Пользователь может видеть только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($user->role->can_view_companies !== 3) {
            // Пользователь не имеет прав на просмотр компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Загружаем связанные данные
        $company->load(['warehouses.regions', 'addresses', 'region']);

        // Получаем основной адрес компании
        $mainAddress = $company->addresses->where('main_address', true)->first();
        
        // Получаем склад компании
        $warehouse = $company->warehouses->first();
        
        // Получаем регион компании напрямую из поля region_id
        $region = $company->region;

        return response()->json([
            'success' => true,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'sku' => $company->sku,
                'warehouse' => $warehouse ? [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name
                ] : null,
                'region' => $region ? [
                    'id' => $region->id,
                    'name' => $region->name
                ] : null,
                'main_address' => $mainAddress ? $mainAddress->address : null
            ]
        ]);
    }

    /**
     * Получает все логи компании
     */
    public function getLogs(Company $company)
    {
        // Проверяем права пользователя на просмотр данной компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        if ($user->role->name === 'Региональный представитель') {
            // Для регионального представителя проверяем, что он назначен как региональный представитель
            if ($company->regional_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($user->role->can_view_companies === 1) {
            // Пользователь может видеть только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($user->role->can_view_companies !== 3) {
            // Пользователь не имеет прав на просмотр компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $logs = CompanyLog::where('company_id', $company->id)
            ->with(['type', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Получает регионы, доступные авторизованному пользователю
     * Теперь регионы получаются через склады, к которым привязан пользователь
     */
    private function getUserRegions()
    {
        $user = auth()->user();
        
        // Если пользователь не авторизован, показываем пустой список
        if (!$user) {
            return collect();
        }
        
        // Проверяем роль пользователя - администраторы видят все регионы
        if ($user->role && $user->role->can_view_companies === 3) {
            return Regions::where('active', true)->get();
        } else {
            // Обычные пользователи видят регионы через свои склады
            return Regions::where('active', true)
                ->whereIn('id', function($query) use ($user) {
                    $query->select('region_id')
                          ->from('warehouses_to_regions')
                          ->whereIn('warehouse_id', function($subQuery) use ($user) {
                              $subQuery->select('warehouse_id')
                                       ->from('users_to_warehouses')
                                       ->where('user_id', $user->id);
                          });
                })
                ->get();
        }
    }

    /**
     * Получает склады, доступные авторизованному пользователю
     */
    private function getUserWarehouses()
    {
        $user = auth()->user();
        
        // Если пользователь не авторизован, показываем пустой список
        if (!$user) {
            return collect();
        }
        
        // Проверяем роль пользователя - администраторы видят все склады
        if ($user->role && $user->role->can_view_companies === 3) {
            return Warehouses::where('active', true)->get();
        } else {
            // Обычные пользователи видят только свои склады
            return Warehouses::where('active', true)
                ->whereIn('id', function($query) use ($user) {
                    $query->select('warehouse_id')
                          ->from('users_to_warehouses')
                          ->where('user_id', $user->id);
                })
                ->get();
        }
    }

    /**
     * Получает все действия компании
     */
    public function getActions(Company $company)
    {
        // Проверяем права пользователя на просмотр данной компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может видеть только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на просмотр компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $actions = CompanyActions::where('company_id', $company->id)
            ->orderBy('expired_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'actions' => $actions
        ]);
    }

    /**
     * Создает новое действие для компании
     */
    public function storeAction(Request $request, Company $company)
    {
        // Проверяем права пользователя на работу с данной компанией
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может работать только со своими компаниями
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на работу с компаниями
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Валидируем запрос
        $validated = $request->validate([
            'action' => 'required|string|max:1000',
            'expired_at' => 'required|date|after:today'
        ]);

        try {
            // Создаем новое действие
            $action = CompanyActions::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'action' => $validated['action'],
                'expired_at' => $validated['expired_at'],
                'status' => false,
            ]);

            // Создаем лог о создании действия
            $commentType = LogType::where('name', 'Комментарий')->first();
            
            $logMessage = "Пользователь: {$user->name}, создал новую задачу: \"{$validated['action']}\" со сроком до {$validated['expired_at']}";
            
            $log = CompanyLog::create([
                'company_id' => $company->id,
                'type_id' => $commentType ? $commentType->id : null,
                'log' => $logMessage,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Действие успешно создано',
                'action' => $action,
                'log' => $log->load(['type', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании действия: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отмечает действие как выполненное
     */
    public function completeAction(Request $request, Company $company, $actionId)
    {
        // Проверяем права пользователя на работу с данной компанией
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может работать только со своими компаниями
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на работу с компаниями
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Валидируем запрос
        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        try {
            // Находим действие
            $action = CompanyActions::where('id', $actionId)
                ->where('company_id', $company->id)
                ->first();

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Действие не найдено'
                ], 404);
            }

            // Проверяем, что действие еще не выполнено
            if ($action->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Действие уже выполнено'
                ], 400);
            }

            // Отмечаем действие как выполненное
            $action->status = true;
            $action->completed_at = now();
            $action->save();

            // Создаем лог о выполнении действия
            $commentType = LogType::where('name', 'Комментарий')->first();
            
            $logMessage = "Пользователь: {$user->name}, выполнил задачу \"{$action->action}\" с комментарием: {$validated['comment']}";
            
            $log = CompanyLog::create([
                'company_id' => $company->id,
                'type_id' => $commentType ? $commentType->id : null,
                'log' => $logMessage,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Действие успешно выполнено',
                'log' => $log->load(['type', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении действия: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обрабатывает перевод компании в статус "Холд"
     */
    private function handleCompanyHoldStatus(Company $company, $user)
    {
        // 1. Создаем задачу для компании со сроком сейчас + 3 месяца
        $expiredAt = now()->addMonths(3);
        
        CompanyActions::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и ценам',
            'expired_at' => $expiredAt,
            'status' => false
        ]);

        // 2. Получаем статусы товаров, которые НЕ нужно переводить в "Холд"
        $excludedProductStatuses = \App\Models\ProductStatus::whereIn('name', ['Продано', 'Холд', 'Отказ'])->pluck('id');

        // 3. Получаем товары компании, которые нужно перевести в "Холд"
        $productsToUpdate = $company->products()
            ->whereNotIn('status_id', $excludedProductStatuses)
            ->get();

        // 4. Получаем статус "Холд" для товаров
        $holdProductStatus = \App\Models\ProductStatus::where('name', 'Холд')->first();

        if ($holdProductStatus && $productsToUpdate->count() > 0) {
            // Обновляем статусы товаров
            $company->products()
                ->whereNotIn('status_id', $excludedProductStatuses)
                ->update(['status_id' => $holdProductStatus->id]);

            // 5. Создаем системные логи для каждого товара
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            foreach ($productsToUpdate as $product) {
                // Создаем системный лог для товара
                \App\Models\ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "В связи с переводом компании \"{$company->name}\" в статус Холд, товар переводится в статус холд.",
                    'user_id' => null // От имени системы
                ]);

                // Создаем задачу для товара
                \App\Models\ProductAction::create([
                    'product_id' => $product->id,
                    'user_id' => $product->owner_id,
                    'action' => 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.',
                    'expired_at' => $expiredAt,
                    'status' => false
                ]);

                // 6. Находим связанные объявления для товара
                $excludedAdvertisementStatuses = \App\Models\AdvertisementStatus::whereIn('name', ['Продано', 'Архив', 'Холд'])->pluck('id');
                
                $advertisementsToUpdate = $product->advertisements()
                    ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                    ->get();

                // 7. Получаем статус "Холд" для объявлений
                $holdAdvertisementStatus = \App\Models\AdvertisementStatus::where('name', 'Холд')->first();

                if ($holdAdvertisementStatus && $advertisementsToUpdate->count() > 0) {
                    // Обновляем статусы объявлений
                    $product->advertisements()
                        ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                        ->update(['status_id' => $holdAdvertisementStatus->id]);

                    // 8. Создаем системные логи для каждого объявления
                    foreach ($advertisementsToUpdate as $advertisement) {
                        \App\Models\AdvLog::create([
                            'advertisement_id' => $advertisement->id,
                            'type_id' => $systemLogType ? $systemLogType->id : null,
                            'log' => "В связи с переводом компании \"{$company->name}\" в статус Холд, объявление переводится в статус Холд.",
                            'user_id' => null // От имени системы
                        ]);

                        // Создаем задачу для объявления
                        \App\Models\AdvAction::create([
                            'advertisement_id' => $advertisement->id,
                            'user_id' => $advertisement->created_by,
                            'action' => 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.',
                            'expired_at' => $expiredAt,
                            'status' => false
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Обновляет общую информацию о компании
     */
    public function updateCommonInfo(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'common_info' => 'nullable|string|max:2000'
        ]);

        try {
            DB::beginTransaction();

            // Сохраняем старое значение для лога
            $oldCommonInfo = $company->common_info;

            // Обновляем общую информацию
            $company->update([
                'common_info' => $validated['common_info']
            ]);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $oldValue = $oldCommonInfo ?: 'пустое';
                $newValue = $validated['common_info'] ?: 'пустое';
                $logText = "Пользователь {$user->name} изменил Описание с \"{$oldValue}\" на \"{$newValue}\"";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Общая информация успешно обновлена',
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении общей информации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет контактную информацию компании
     */
    public function updateContactInfo(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'site' => 'nullable|url'
        ]);

        try {
            DB::beginTransaction();

            // Обновляем контактную информацию
            $company->update([
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'site' => $validated['site']
            ]);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Обновлена контактная информация компании";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Контактная информация успешно обновлена',
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении контактной информации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет контакты компании
     */
    public function updateContacts(Request $request, Company $company)
    {
        // Отладочная информация
        \Log::info('updateContacts method called', [
            'company_id' => $company->id,
            'request_data' => $request->all(),
            'existing_contacts_count' => $company->contacts()->count()
        ]);

        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'contacts' => 'required|array',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.position' => 'required|string|max:255',
            'contacts.*.phones' => 'array',
            'contacts.*.phones.*' => 'string|max:20',
            'contacts.*.emails' => 'array',
            'contacts.*.emails.*' => 'email|max:255',
            'contacts.*.main_contact' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Получаем существующие контакты
            $existingContacts = $company->contacts()->with(['phones', 'emails'])->get();
            $existingContactIds = $existingContacts->pluck('id')->toArray();

            // Обрабатываем каждый контакт
            $updatedContacts = [];
            foreach ($validated['contacts'] as $index => $contactData) {
                \Log::info("Processing contact {$index}", [
                    'contact_data' => $contactData,
                    'has_id' => isset($contactData['id']),
                    'id_value' => $contactData['id'] ?? 'null'
                ]);
                
                if (isset($contactData['id']) && $contactData['id'] && $contactData['id'] !== '') {
                    // Обновляем существующий контакт
                    $contact = $existingContacts->find($contactData['id']);
                    if ($contact) {
                        $contact->update([
                            'name' => $contactData['name'],
                            'position' => $contactData['position'],
                            'main_contact' => $contactData['main_contact'] ?? false
                        ]);

                        // Обновляем телефоны
                        $contact->phones()->delete();
                        if (isset($contactData['phones'])) {
                            foreach ($contactData['phones'] as $phone) {
                                if (!empty($phone)) {
                                    $contact->phones()->create(['phone' => $phone]);
                                }
                            }
                        }

                        // Обновляем email
                        $contact->emails()->delete();
                        if (isset($contactData['emails'])) {
                            foreach ($contactData['emails'] as $index => $email) {
                                if (!empty($email)) {
                                    $contact->emails()->create([
                                        'email' => $email,
                                        'is_primary' => $index === 0 // Первый email считается основным
                                    ]);
                                }
                            }
                        }

                        $updatedContacts[] = $contact->fresh(['phones', 'emails']);
                    }
                } else {
                    // Создаем новый контакт
                    $contact = $company->contacts()->create([
                        'name' => $contactData['name'],
                        'position' => $contactData['position'],
                        'main_contact' => $contactData['main_contact'] ?? false
                    ]);

                    // Добавляем телефоны
                    if (isset($contactData['phones'])) {
                        foreach ($contactData['phones'] as $phone) {
                            if (!empty($phone)) {
                                $contact->phones()->create(['phone' => $phone]);
                            }
                        }
                    }

                    // Добавляем email
                    if (isset($contactData['emails'])) {
                        foreach ($contactData['emails'] as $index => $email) {
                            if (!empty($email)) {
                                $contact->emails()->create([
                                    'email' => $email,
                                    'is_primary' => $index === 0 // Первый email считается основным
                                ]);
                            }
                        }
                    }

                    $updatedContacts[] = $contact->fresh(['phones', 'emails']);
                }
            }

            // Удаляем контакты, которые больше не нужны
            $updatedContactIds = collect($updatedContacts)->pluck('id')->toArray();
            $contactsToDelete = array_diff($existingContactIds, $updatedContactIds);
            
            \Log::info('Contacts deletion info', [
                'existing_contact_ids' => $existingContactIds,
                'updated_contact_ids' => $updatedContactIds,
                'contacts_to_delete' => $contactsToDelete
            ]);
            
            if (!empty($contactsToDelete)) {
                // Сначала удаляем все связанные записи для контактов, которые нужно удалить
                foreach ($contactsToDelete as $contactId) {
                    $contactToDelete = $existingContacts->find($contactId);
                    if ($contactToDelete) {
                        // Удаляем телефоны
                        $contactToDelete->phones()->delete();
                        // Удаляем email
                        $contactToDelete->emails()->delete();
                        // Удаляем сам контакт
                        $contactToDelete->delete();
                    }
                }
            }

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Обновлены контакты компании";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Контакты успешно обновлены',
                'contacts' => $updatedContacts,
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating contacts', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении контактов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет адреса компании
     */
    public function updateAddresses(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'addresses' => 'required|array',
            'addresses.*.address' => 'required|string|max:500',
            'addresses.*.main_address' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Получаем существующие адреса
            $existingAddresses = $company->addresses()->get();
            $existingAddressIds = $existingAddresses->pluck('id')->toArray();

            // Обрабатываем каждый адрес
            $updatedAddresses = [];
            foreach ($validated['addresses'] as $index => $addressData) {
                if (isset($addressData['id']) && $addressData['id'] && $addressData['id'] !== '') {
                    // Обновляем существующий адрес
                    $address = $existingAddresses->find($addressData['id']);
                    if ($address) {
                        $address->update([
                            'address' => $addressData['address'],
                            'main_address' => $addressData['main_address'] ?? false
                        ]);
                        $updatedAddresses[] = $address->fresh();
                    }
                } else {
                    // Создаем новый адрес
                    $address = $company->addresses()->create([
                        'address' => $addressData['address'],
                        'main_address' => $addressData['main_address'] ?? false
                    ]);
                    $updatedAddresses[] = $address->fresh();
                }
            }

            // Удаляем адреса, которые больше не нужны
            $updatedAddressIds = collect($updatedAddresses)->pluck('id')->toArray();
            $addressesToDelete = array_diff($existingAddressIds, $updatedAddressIds);
            
            if (!empty($addressesToDelete)) {
                $company->addresses()->whereIn('id', $addressesToDelete)->delete();
            }

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Обновлены адреса компании";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Адреса успешно обновлены',
                'addresses' => $updatedAddresses,
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении адресов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет юридическую информацию компании
     */
    public function updateLegalInfo(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inn' => 'nullable|string|max:12'
        ]);

        try {
            DB::beginTransaction();

            // Обновляем юридическую информацию
            $company->update([
                'name' => $validated['name'],
                'inn' => $validated['inn']
            ]);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Обновлена юридическая информация компании";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Юридическая информация успешно обновлена',
                'company' => [
                    'name' => $company->name,
                    'inn' => $company->inn
                ],
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении юридической информации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновляет только название компании
     */
    public function updateName(Request $request, Company $company)
    {
        // Проверяем права пользователя на обновление компании
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $canViewCompanies = $user->role->can_view_companies;
        
        if ($canViewCompanies === 1) {
            // Пользователь может обновлять только свои компании
            if ($company->owner_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен'
                ], 403);
            }
        } elseif ($canViewCompanies !== 3) {
            // Пользователь не имеет прав на обновление компаний
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Сохраняем старое значение для лога
            $oldName = $company->name;

            // Обновляем название компании
            $company->update([
                'name' => $validated['name']
            ]);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Пользователь {$user->name} изменил название компании с \"{$oldName}\" на \"{$validated['name']}\"";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Название компании успешно обновлено',
                'company' => [
                    'name' => $company->name
                ],
                'log' => $log ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении названия компании: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обрабатывает перевод компании в статус "Отказ"
     */
    private function handleCompanyRefuseStatus(Company $company, $user)
    {
        // 1. Создаем задачу для компании со сроком сейчас + 6 месяцев
        $expiredAt = now()->addMonths(6);
        
        CompanyActions::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и цены',
            'expired_at' => $expiredAt,
            'status' => false
        ]);

        // 2. Получаем статусы товаров, которые НЕ нужно переводить в "Отказ"
        $excludedProductStatuses = \App\Models\ProductStatus::whereIn('name', ['Продано', 'Отказ'])->pluck('id');

        // 3. Получаем товары компании, которые нужно перевести в "Отказ"
        $productsToUpdate = $company->products()
            ->whereNotIn('status_id', $excludedProductStatuses)
            ->get();

        // 4. Получаем статус "Отказ" для товаров
        $refuseProductStatus = \App\Models\ProductStatus::where('name', 'Отказ')->first();

        if ($refuseProductStatus && $productsToUpdate->count() > 0) {
            // Обновляем статусы товаров
            $company->products()
                ->whereNotIn('status_id', $excludedProductStatuses)
                ->update(['status_id' => $refuseProductStatus->id]);

            // 5. Создаем системные логи для каждого товара
            $systemLogType = LogType::where('name', 'Системный')->first();
            
            foreach ($productsToUpdate as $product) {
                // Создаем системный лог для товара
                \App\Models\ProductLog::create([
                    'product_id' => $product->id,
                    'type_id' => $systemLogType ? $systemLogType->id : null,
                    'log' => "В связи с переводом компании \"{$company->name}\" в статус Отказ, товар переводится в статус Отказ.",
                    'user_id' => null // От имени системы
                ]);

                // Создаем задачу для товара
                \App\Models\ProductAction::create([
                    'product_id' => $product->id,
                    'user_id' => $product->owner_id,
                    'action' => 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.',
                    'expired_at' => $expiredAt,
                    'status' => false
                ]);

                // 6. Находим связанные объявления для товара
                $excludedAdvertisementStatuses = \App\Models\AdvertisementStatus::whereIn('name', ['Продано', 'Архив'])->pluck('id');
                
                $advertisementsToUpdate = $product->advertisements()
                    ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                    ->get();

                // 7. Получаем статус "Архив" для объявлений
                $archiveAdvertisementStatus = \App\Models\AdvertisementStatus::where('name', 'Архив')->first();

                if ($archiveAdvertisementStatus && $advertisementsToUpdate->count() > 0) {
                    // Обновляем статусы объявлений
                    $product->advertisements()
                        ->whereNotIn('status_id', $excludedAdvertisementStatuses)
                        ->update(['status_id' => $archiveAdvertisementStatus->id]);

                    // 8. Создаем системные логи для каждого объявления
                    foreach ($advertisementsToUpdate as $advertisement) {
                        \App\Models\AdvLog::create([
                            'advertisement_id' => $advertisement->id,
                            'type_id' => $systemLogType ? $systemLogType->id : null,
                            'log' => "В связи с переводом компании \"{$company->name}\" в статус Отказ, объявление переводится в статус Архив.",
                            'user_id' => null // От имени системы
                        ]);

                        // Создаем задачу для объявления
                        \App\Models\AdvAction::create([
                            'advertisement_id' => $advertisement->id,
                            'user_id' => $advertisement->created_by,
                            'action' => 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.',
                            'expired_at' => $expiredAt,
                            'status' => false
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Смена ответственного за компанию
     */
    public function changeOwner(Request $request, Company $company)
    {
        // Проверяем права пользователя на смену ответственного
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Проверяем, что пользователь может менять ответственного
        if (!$company->canChangeOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для смены ответственного. Только администраторы могут выполнять эту операцию.'
            ], 403);
        }

        $validated = $request->validate([
            'new_owner_id' => 'required|integer|exists:users,id'
        ]);

        try {
            // Выполняем смену ответственного
            $company->changeOwner($validated['new_owner_id'], $user);

            // Получаем нового ответственного
            $newOwner = User::find($validated['new_owner_id']);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Пользователь {$user->name} сменил ответственного с '{$company->owner->name}' на '{$newOwner->name}'";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            // Обновляем данные компании
            $company->load('owner');

            return response()->json([
                'success' => true,
                'message' => 'Ответственный успешно изменен',
                'company' => [
                    'owner' => $company->owner
                ],
                'log' => $log ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Получает список доступных пользователей для назначения ответственными
     */
    public function getAvailableOwners(Company $company)
    {
        // Проверяем права пользователя
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Проверяем, что пользователь может менять ответственного
        if (!$company->canChangeOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для просмотра доступных пользователей'
            ], 403);
        }

        try {
            $availableOwners = $company->getAvailableOwners();

            return response()->json([
                'success' => true,
                'users' => $availableOwners
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка пользователей: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Смена регионального представителя компании
     */
    public function changeRegional(Request $request, Company $company)
    {
        // Проверяем права пользователя на смену регионального представителя
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Проверяем, что пользователь может менять регионального представителя
        if (!$company->canChangeRegional($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для смены регионального представителя. Только администраторы могут выполнять эту операцию.'
            ], 403);
        }

        $validated = $request->validate([
            'new_regional_id' => 'required|integer|exists:users,id'
        ]);

        try {
            // Выполняем смену регионального представителя
            $company->changeRegional($validated['new_regional_id'], $user);

            // Получаем нового регионального представителя
            $newRegional = User::find($validated['new_regional_id']);

            // Создаем запись в логе
            $commentLogType = LogType::where('name', 'Комментарий')->first();
            if ($commentLogType) {
                $logText = "Пользователь {$user->name} сменил регионального представителя с '{$company->regional->name}' на '{$newRegional->name}'";
                
                $log = CompanyLog::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'log' => $logText,
                    'type_id' => $commentLogType->id,
                ]);

                // Загружаем связи для лога
                $log->load(['type', 'user']);
            }

            // Обновляем данные компании
            $company->load('regional');

            return response()->json([
                'success' => true,
                'message' => 'Региональный представитель успешно изменен',
                'company' => [
                    'regional' => $company->regional
                ],
                'log' => $log ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Получает список доступных пользователей для назначения региональными представителями
     */
    public function getAvailableRegionals(Company $company)
    {
        // Проверяем права пользователя
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        // Проверяем, что пользователь может менять регионального представителя
        if (!$company->canChangeRegional($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для просмотра доступных пользователей'
            ], 403);
        }

        try {
            $availableRegionals = $company->getAvailableRegionals();

            return response()->json([
                'success' => true,
                'users' => $availableRegionals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка пользователей: ' . $e->getMessage()
            ], 500);
        }
    }
}
