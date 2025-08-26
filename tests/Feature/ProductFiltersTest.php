<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductStatus;
use App\Models\Company;
use App\Models\Regions;
use App\Models\Warehouses;
use App\Models\RoleList;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем роли
        $adminRole = RoleList::create([
            'name' => 'Администратор',
            'can_view_companies' => 3,
            'can_view_products' => 3,
            'can_view_advertise' => 3
        ]);
        
        $managerRole = RoleList::create([
            'name' => 'Менеджер',
            'can_view_companies' => 1,
            'can_view_products' => 1,
            'can_view_advertise' => 2
        ]);
        
        // Создаем категории
        $category1 = ProductCategories::create(['name' => 'Категория 1', 'active' => true]);
        $category2 = ProductCategories::create(['name' => 'Категория 2', 'active' => true]);
        
        // Создаем статусы
        $status1 = ProductStatus::create(['name' => 'Статус 1', 'active' => true]);
        $status2 = ProductStatus::create(['name' => 'Статус 2', 'active' => true]);
        
        // Создаем регионы
        $region1 = Regions::create(['name' => 'Регион 1', 'active' => true]);
        $region2 = Regions::create(['name' => 'Регион 2', 'active' => true]);
        
        // Создаем склады
        $warehouse1 = Warehouses::create(['name' => 'Склад 1']);
        $warehouse2 = Warehouses::create(['name' => 'Склад 2']);
        
        // Связываем склады с регионами
        $warehouse1->regions()->attach($region1->id);
        $warehouse2->regions()->attach($region2->id);
        
        // Создаем пользователей
        $admin = User::create([
            'name' => 'Администратор',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'active' => true
        ]);
        
        $manager = User::create([
            'name' => 'Менеджер',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role_id' => $managerRole->id,
            'active' => true
        ]);
        
        // Создаем компании
        $company1 = Company::create([
            'sku' => 'COMP001',
            'name' => 'Компания 1',
            'source_id' => 1,
            'region_id' => $region1->id,
            'regional_user_id' => $admin->id,
            'owner_user_id' => $manager->id,
            'email' => 'company1@test.com',
            'site' => 'http://company1.com',
            'common_info' => 'Информация о компании 1',
            'company_status_id' => 1
        ]);
        
        $company2 = Company::create([
            'sku' => 'COMP002',
            'name' => 'Компания 2',
            'source_id' => 1,
            'region_id' => $region2->id,
            'regional_user_id' => $admin->id,
            'owner_user_id' => $manager->id,
            'email' => 'company2@test.com',
            'site' => 'http://company2.com',
            'common_info' => 'Информация о компании 2',
            'company_status_id' => 1
        ]);
        
        // Связываем компании со складами
        $company1->warehouses()->attach($warehouse1->id);
        $company2->warehouses()->attach($warehouse2->id);
        
        // Создаем товары
        Product::create([
            'name' => 'Товар 1',
            'sku' => 'PROD001',
            'category_id' => $category1->id,
            'company_id' => $company1->id,
            'owner_id' => $manager->id,
            'regional_id' => $admin->id,
            'status_id' => $status1->id,
            'warehouse_id' => $warehouse1->id,
            'product_address' => 'Адрес 1',
            'add_expenses' => 0
        ]);
        
        Product::create([
            'name' => 'Товар 2',
            'sku' => 'PROD002',
            'category_id' => $category2->id,
            'company_id' => $company2->id,
            'owner_id' => $manager->id,
            'regional_id' => $admin->id,
            'status_id' => $status2->id,
            'warehouse_id' => $warehouse2->id,
            'product_address' => 'Адрес 2',
            'add_expenses' => 0
        ]);
    }

    public function test_admin_can_see_all_filters()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Категория 1');
        $response->assertSee('Категория 2');
        $response->assertSee('Компания 1');
        $response->assertSee('Компания 2');
        $response->assertSee('Статус 1');
        $response->assertSee('Статус 2');
        $response->assertSee('Регион 1');
        $response->assertSee('Регион 2');
    }

    public function test_manager_can_see_limited_filters()
    {
        $manager = User::where('email', 'manager@test.com')->first();
        
        $this->actingAs($manager);
        
        $response = $this->get(route('products.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Категория 1');
        $response->assertSee('Категория 2');
        $response->assertSee('Компания 1');
        $response->assertSee('Компания 2');
        $response->assertSee('Статус 1');
        $response->assertSee('Статус 2');
        $response->assertSee('Регион 1');
        $response->assertSee('Регион 2');
    }

    public function test_filter_by_category()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $category1 = ProductCategories::where('name', 'Категория 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['category_id' => $category1->id]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_filter_by_company()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $company1 = Company::where('name', 'Компания 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['company_id' => $company1->id]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_filter_by_status()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $status1 = ProductStatus::where('name', 'Статус 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['status_id' => $status1->id]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_filter_by_region()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $region1 = Regions::where('name', 'Регион 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['region_id' => $region1->id]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_multiple_filters()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $category1 = ProductCategories::where('name', 'Категория 1')->first();
        $company1 = Company::where('name', 'Компания 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', [
            'category_id' => $category1->id,
            'company_id' => $company1->id
        ]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_search_by_name()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['search' => 'Товар 1']));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_search_by_sku()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['search' => 'PROD001']));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_search_by_address()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', ['search' => 'Адрес 1']));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }

    public function test_search_with_filters()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $category1 = ProductCategories::where('name', 'Категория 1')->first();
        
        $this->actingAs($admin);
        
        $response = $this->get(route('products.index', [
            'search' => 'Товар 1',
            'category_id' => $category1->id
        ]));
        
        $response->assertStatus(200);
        $response->assertSee('Товар 1');
        $response->assertDontSee('Товар 2');
    }
}
