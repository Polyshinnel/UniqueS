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

        $companies = $query->get();

        $warehouses = Warehouses::all();
        $sources = Sources::all();
        $regionals = User::where('role_id', 3)
            ->where('active', true)
            ->get();
        $regions = $this->getUserRegions();

        return view('Company.CompanyPage', compact('companies', 'warehouses', 'sources', 'regionals', 'regions'));
    }

    public function create()
    {
        $warehouses = Warehouses::with('regions')->get();
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

        // Получаем регион из выбранного склада
        $warehouse = Warehouses::with('regions')->find($validated['warehouse_id']);
        if (!$warehouse || !$warehouse->regions->count()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['warehouse_id' => 'Выбранный склад не прикреплен к региону']);
        }
        
        $region = $warehouse->regions->first();
        $validated['region'] = $region->id;

        // Проверяем, что регион склада доступен пользователю
        $userRegions = $this->getUserRegions()->pluck('id')->toArray();
        if (!empty($userRegions) && !in_array($validated['region'], $userRegions)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['warehouse_id' => 'Регион выбранного склада недоступен для вашего пользователя']);
        }

        // Проверяем, что выбранный региональный представитель прикреплен к региону склада
        $regional = User::where('id', $validated['region_id'])
            ->where('role_id', 3)
            ->where('active', true)
            ->whereHas('regions', function($query) use ($validated) {
                $query->where('regions.id', $validated['region']);
            })
            ->first();

        if (!$regional) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['region_id' => 'Выбранный региональный представитель не прикреплен к региону выбранного склада']);
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

        return view('Company.CompanyShowPage', compact('company', 'statuses', 'lastLog', 'lastAction'));
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
    public function getRegionalsByWarehouse($warehouseId)
    {
        $warehouse = Warehouses::with('regions')->find($warehouseId);
        
        if (!$warehouse || !$warehouse->regions->count()) {
            return response()->json([]);
        }
        
        $region = $warehouse->regions->first();
        
        $regionals = User::where('role_id', 3)
            ->where('active', true)
            ->whereHas('regions', function($query) use ($region) {
                $query->where('regions.id', $region->id);
            })
            ->get(['id', 'name']);

        return response()->json($regionals);
    }

    /**
     * Получает информацию о регионе склада
     */
    public function getWarehouseRegion($warehouseId)
    {
        $warehouse = Warehouses::with('regions')->find($warehouseId);
        
        if (!$warehouse || !$warehouse->regions->count()) {
            return response()->json(['region' => null]);
        }
        
        $region = $warehouse->regions->first();
        
        return response()->json([
            'region' => [
                'id' => $region->id,
                'name' => $region->name
            ]
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

        // Загружаем связанные данные
        $company->load(['warehouses.regions', 'addresses']);

        // Получаем основной адрес компании
        $mainAddress = $company->addresses->where('main_address', true)->first();
        
        // Получаем склад компании
        $warehouse = $company->warehouses->first();
        
        // Получаем регион склада
        $region = $warehouse ? $warehouse->regions->first() : null;

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
            // Обычные пользователи видят только свои регионы
            return Regions::where('active', true)
                ->whereIn('id', function($query) use ($user) {
                    $query->select('region_id')
                          ->from('users_to_regions')
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
     * Обрабатывает перевод компании в статус "Отказ"
     */
    private function handleCompanyRefuseStatus(Company $company, $user)
    {
        // 1. Создаем задачу для компании со сроком сейчас + 6 месяцев
        $expiredAt = now()->addMonths(6);
        
        CompanyActions::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и ценам',
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
}
