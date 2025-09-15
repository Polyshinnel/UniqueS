<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductAction;
use App\Models\CompanyActions;
use App\Models\AdvAction;
use App\Models\ProductLog;
use App\Models\CompanyLog;
use App\Models\AdvLog;
use App\Models\Product;
use App\Models\Company;
use App\Models\Advertisement;
use App\Models\User;
use App\Models\LogType;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Определяем роли: 1 - Администратор, 2 - Менеджер, 3 - Региональный представитель
        $isAdmin = $user->role_id == 1;
        
        // Подсчет активных задач
        $activeProductActions = ProductAction::where('status', 0)
            ->where('expired_at', '>', Carbon::now());
        $activeCompanyActions = CompanyActions::where('status', 0)
            ->where('expired_at', '>', Carbon::now());
        $activeAdvActions = AdvAction::where('status', 0)
            ->where('expired_at', '>', Carbon::now());
            
        if (!$isAdmin) {
            $activeProductActions->where('user_id', $user->id);
            $activeCompanyActions->where('user_id', $user->id);
            $activeAdvActions->where('user_id', $user->id);
        }
        
        $activeTasks = $activeProductActions->count() + 
                      $activeCompanyActions->count() + 
                      $activeAdvActions->count();
        
        // Подсчет просроченных задач
        $expiredProductActions = ProductAction::where('status', 0)
            ->where('expired_at', '<', Carbon::now());
        $expiredCompanyActions = CompanyActions::where('status', 0)
            ->where('expired_at', '<', Carbon::now());
        $expiredAdvActions = AdvAction::where('status', 0)
            ->where('expired_at', '<', Carbon::now());
            
        if (!$isAdmin) {
            $expiredProductActions->where('user_id', $user->id);
            $expiredCompanyActions->where('user_id', $user->id);
            $expiredAdvActions->where('user_id', $user->id);
        }
        
        $expiredTasks = $expiredProductActions->count() + 
                       $expiredCompanyActions->count() + 
                       $expiredAdvActions->count();
        
        // Получение последнего лога
        $lastProductLog = ProductLog::query();
        $lastCompanyLog = CompanyLog::query();
        $lastAdvLog = AdvLog::query();
        
        if (!$isAdmin) {
            $lastProductLog->where('user_id', $user->id);
            $lastCompanyLog->where('user_id', $user->id);
            $lastAdvLog->where('user_id', $user->id);
        }
        
        $lastProductLog = $lastProductLog->latest()->first();
        $lastCompanyLog = $lastCompanyLog->latest()->first();
        $lastAdvLog = $lastAdvLog->latest()->first();
        
        // Определяем самый последний лог
        $lastLog = collect([$lastProductLog, $lastCompanyLog, $lastAdvLog])
            ->filter()
            ->sortByDesc('created_at')
            ->first();
        
        $lastLogDate = $lastLog ? $lastLog->created_at->format('d.m.Y H:i:s') : 'Нет логов';
        
        return view('Events.EventListPage', compact('activeTasks', 'expiredTasks', 'lastLogDate'));
    }

    public function active(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Определяем роли: 1 - Администратор, 2 - Менеджер, 3 - Региональный представитель
        $isAdmin = $user->role_id == 1;
        
        // Получаем список пользователей для фильтра (только для администраторов)
        $usersForFilter = $isAdmin ? User::orderBy('name')->get() : collect();
        
        // Получаем активные задачи (expired_at > now и status = 0)
        $productActionsQuery = ProductAction::with(['product', 'user'])
            ->where('status', 0)
            ->where('expired_at', '>', Carbon::now());
            
        $companyActionsQuery = CompanyActions::with(['company', 'user'])
            ->where('status', 0)
            ->where('expired_at', '>', Carbon::now());
            
        $advActionsQuery = AdvAction::with(['advertisement', 'user'])
            ->where('status', 0)
            ->where('expired_at', '>', Carbon::now());
        
        // Если не администратор, показываем только свои задачи
        if (!$isAdmin) {
            $productActionsQuery->where('user_id', $user->id);
            $companyActionsQuery->where('user_id', $user->id);
            $advActionsQuery->where('user_id', $user->id);
        } else {
            // Фильтрация по пользователю для администраторов
            $userId = $request->get('user_id');
            if ($userId) {
                if ($userId === 'system') {
                    // Фильтр для системных записей (user_id = null)
                    $productActionsQuery->whereNull('user_id');
                    $companyActionsQuery->whereNull('user_id');
                    $advActionsQuery->whereNull('user_id');
                } else {
                    $productActionsQuery->where('user_id', $userId);
                    $companyActionsQuery->where('user_id', $userId);
                    $advActionsQuery->where('user_id', $userId);
                }
            }
        }
        
        // Получаем данные
        $productActions = $productActionsQuery->get();
        $companyActions = $companyActionsQuery->get();
        $advActions = $advActionsQuery->get();
        
        // Объединяем все задачи в одну коллекцию с типом
        $allActions = collect();
        
        // Добавляем задачи продуктов
        $productActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'product',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->product_id,
                'related_name' => $action->product->name ?? 'Товар не найден',
                'related_sku' => $action->product->sku ?? '',
                'route' => route('products.show', $action->product_id)
            ]);
        });
        
        // Добавляем задачи компаний
        $companyActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'company',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->company_id,
                'related_name' => $action->company->name ?? 'Компания не найдена',
                'related_sku' => $action->company->sku ?? '',
                'route' => route('companies.show', $action->company_id)
            ]);
        });
        
        // Добавляем задачи объявлений
        $advActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'advertisement',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->advertisement_id,
                'related_name' => $action->advertisement->title ?? 'Объявление не найдено',
                'related_sku' => '',
                'route' => route('advertisements.show', $action->advertisement_id)
            ]);
        });
        
        // Сортируем по дате истечения (сначала самые срочные)
        $allActions = $allActions->sortBy('expired_at');
        
        // Пагинация - 20 элементов на страницу
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedActions = $allActions->slice($offset, $perPage);
        $total = $allActions->count();
        $lastPage = ceil($total / $perPage);
        
        // Создаем объект пагинации для совместимости с Laravel Pagination
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedActions,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Добавляем параметры запроса к пагинации для сохранения фильтров
        $paginator->appends($request->query());
        
        return view('Events.ActiveEventsBlock', compact('paginator', 'isAdmin', 'usersForFilter'));
    }

    public function expired(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Определяем роли: 1 - Администратор, 2 - Менеджер, 3 - Региональный представитель
        $isAdmin = $user->role_id == 1;
        
        // Получаем список пользователей для фильтра (только для администраторов)
        $usersForFilter = $isAdmin ? User::orderBy('name')->get() : collect();
        
        // Получаем просроченные задачи (expired_at < now и status = 0)
        $productActionsQuery = ProductAction::with(['product', 'user'])
            ->where('status', 0)
            ->where('expired_at', '<', Carbon::now());
            
        $companyActionsQuery = CompanyActions::with(['company', 'user'])
            ->where('status', 0)
            ->where('expired_at', '<', Carbon::now());
            
        $advActionsQuery = AdvAction::with(['advertisement', 'user'])
            ->where('status', 0)
            ->where('expired_at', '<', Carbon::now());
        
        // Если не администратор, показываем только свои задачи
        if (!$isAdmin) {
            $productActionsQuery->where('user_id', $user->id);
            $companyActionsQuery->where('user_id', $user->id);
            $advActionsQuery->where('user_id', $user->id);
        } else {
            // Фильтрация по пользователю для администраторов
            $userId = $request->get('user_id');
            if ($userId) {
                if ($userId === 'system') {
                    // Фильтр для системных записей (user_id = null)
                    $productActionsQuery->whereNull('user_id');
                    $companyActionsQuery->whereNull('user_id');
                    $advActionsQuery->whereNull('user_id');
                } else {
                    $productActionsQuery->where('user_id', $userId);
                    $companyActionsQuery->where('user_id', $userId);
                    $advActionsQuery->where('user_id', $userId);
                }
            }
        }
        
        // Получаем данные
        $productActions = $productActionsQuery->get();
        $companyActions = $companyActionsQuery->get();
        $advActions = $advActionsQuery->get();
        
        // Объединяем все задачи в одну коллекцию с типом
        $allActions = collect();
        
        // Добавляем задачи продуктов
        $productActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'product',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->product_id,
                'related_name' => $action->product->name ?? 'Товар не найден',
                'related_sku' => $action->product->sku ?? '',
                'route' => route('products.show', $action->product_id)
            ]);
        });
        
        // Добавляем задачи компаний
        $companyActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'company',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->company_id,
                'related_name' => $action->company->name ?? 'Компания не найдена',
                'related_sku' => $action->company->sku ?? '',
                'route' => route('companies.show', $action->company_id)
            ]);
        });
        
        // Добавляем задачи объявлений
        $advActions->each(function ($action) use ($allActions) {
            $allActions->push([
                'id' => $action->id,
                'type' => 'advertisement',
                'action' => $action->action,
                'expired_at' => $action->expired_at,
                'created_at' => $action->created_at,
                'user' => $action->user,
                'related_id' => $action->advertisement_id,
                'related_name' => $action->advertisement->title ?? 'Объявление не найдено',
                'related_sku' => '',
                'route' => route('advertisements.show', $action->advertisement_id)
            ]);
        });
        
        // Сортируем по дате истечения (сначала самые просроченные)
        $allActions = $allActions->sortByDesc('expired_at');
        
        // Пагинация - 20 элементов на страницу
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedActions = $allActions->slice($offset, $perPage);
        $total = $allActions->count();
        $lastPage = ceil($total / $perPage);
        
        // Создаем объект пагинации для совместимости с Laravel Pagination
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedActions,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Добавляем параметры запроса к пагинации для сохранения фильтров
        $paginator->appends($request->query());
        
        return view('Events.ExpiredEventsBlock', compact('paginator', 'isAdmin', 'usersForFilter'));
    }

    public function logs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Определяем роли: 1 - Администратор, 2 - Менеджер, 3 - Региональный представитель
        $isAdmin = $user->role_id == 1;
        
        // Получаем список пользователей для фильтра (только для администраторов)
        $usersForFilter = $isAdmin ? User::orderBy('name')->get() : collect();
        
        // Используем UNION для объединения всех логов в один запрос
        $productLogsQuery = ProductLog::select([
            'id',
            'product_id as related_id',
            'log',
            'created_at',
            'user_id',
            'type_id'
        ])
        ->selectRaw("'product' as log_type")
        ->selectRaw("'product' as entity_type");
        
        $companyLogsQuery = CompanyLog::select([
            'id',
            'company_id as related_id',
            'log',
            'created_at',
            'user_id',
            'type_id'
        ])
        ->selectRaw("'company' as log_type")
        ->selectRaw("'company' as entity_type");
        
        $advLogsQuery = AdvLog::select([
            'id',
            'advertisement_id as related_id',
            'log',
            'created_at',
            'user_id',
            'type_id'
        ])
        ->selectRaw("'advertisement' as log_type")
        ->selectRaw("'advertisement' as entity_type");
        
        // Если не администратор, показываем только свои логи
        if (!$isAdmin) {
            $productLogsQuery->where('user_id', $user->id);
            $companyLogsQuery->where('user_id', $user->id);
            $advLogsQuery->where('user_id', $user->id);
        } else {
            // Фильтрация по пользователю для администраторов
            $userId = $request->get('user_id');
            if ($userId) {
                if ($userId === 'system') {
                    // Фильтр для системных записей (user_id = null)
                    $productLogsQuery->whereNull('user_id');
                    $companyLogsQuery->whereNull('user_id');
                    $advLogsQuery->whereNull('user_id');
                } else {
                    $productLogsQuery->where('user_id', $userId);
                    $companyLogsQuery->where('user_id', $userId);
                    $advLogsQuery->where('user_id', $userId);
                }
            }
        }
        
        // Объединяем запросы
        $allLogsQuery = $productLogsQuery->union($companyLogsQuery)->union($advLogsQuery)
            ->orderBy('created_at', 'desc');
        
        // Получаем общее количество для пагинации
        $total = $allLogsQuery->count();
        
        // Пагинация
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        // Получаем данные с пагинацией
        $logs = $allLogsQuery->offset($offset)->limit($perPage)->get();
        
        // Загружаем связанные данные
        $productIds = $logs->where('entity_type', 'product')->pluck('related_id')->unique();
        $companyIds = $logs->where('entity_type', 'company')->pluck('related_id')->unique();
        $advertisementIds = $logs->where('entity_type', 'advertisement')->pluck('related_id')->unique();
        $userIds = $logs->pluck('user_id')->unique()->filter();
        $typeIds = $logs->pluck('type_id')->unique();
        
        // Получаем связанные данные
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $companies = Company::whereIn('id', $companyIds)->get()->keyBy('id');
        $advertisements = Advertisement::whereIn('id', $advertisementIds)->get()->keyBy('id');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        $logTypes = LogType::whereIn('id', $typeIds)->get()->keyBy('id');
        
        // Формируем данные для представления
        $formattedLogs = $logs->map(function ($log) use ($products, $companies, $advertisements, $users, $logTypes) {
            $relatedName = '';
            $relatedSku = '';
            $route = '';
            
            switch ($log->entity_type) {
                case 'product':
                    $product = $products->get($log->related_id);
                    $relatedName = $product ? $product->name : 'Товар не найден';
                    $relatedSku = $product ? $product->sku : '';
                    $route = route('products.show', $log->related_id);
                    break;
                case 'company':
                    $company = $companies->get($log->related_id);
                    $relatedName = $company ? $company->name : 'Компания не найдена';
                    $relatedSku = $company ? $company->sku : '';
                    $route = route('companies.show', $log->related_id);
                    break;
                case 'advertisement':
                    $advertisement = $advertisements->get($log->related_id);
                    $relatedName = $advertisement ? $advertisement->title : 'Объявление не найдено';
                    $relatedSku = '';
                    $route = route('advertisements.show', $log->related_id);
                    break;
            }
            
            return [
                'id' => $log->id,
                'type' => $log->entity_type,
                'log' => $log->log,
                'created_at' => $log->created_at,
                'user' => $users->get($log->user_id),
                'log_type' => $logTypes->get($log->type_id),
                'related_id' => $log->related_id,
                'related_name' => $relatedName,
                'related_sku' => $relatedSku,
                'route' => $route
            ];
        });
        
        // Создаем объект пагинации
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $formattedLogs,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Добавляем параметры запроса к пагинации для сохранения фильтров
        $paginator->appends($request->query());
        
        return view('Events.LogListPage', compact('paginator', 'isAdmin', 'usersForFilter'));
    }
}
