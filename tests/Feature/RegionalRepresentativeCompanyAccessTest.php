<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\RoleList;
use App\Models\CompanyStatus;
use App\Models\Regions;
use App\Models\Warehouses;
use App\Models\Sources;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegionalRepresentativeCompanyAccessTest extends TestCase
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
        
        $regionalRole = RoleList::create([
            'name' => 'Региональный представитель',
            'can_view_companies' => 0,
            'can_view_products' => 1,
            'can_view_advertise' => 0
        ]);
        
        // Создаем статусы компаний
        $activeStatus = CompanyStatus::create(['name' => 'Активна']);
        $holdStatus = CompanyStatus::create(['name' => 'Холд']);
        $refuseStatus = CompanyStatus::create(['name' => 'Отказ']);
        
        // Создаем регионы и склады
        $region = Regions::create(['name' => 'Тестовый регион']);
        $warehouse = Warehouses::create(['name' => 'Тестовый склад']);
        $warehouse->regions()->attach($region->id);
        
        $source = Sources::create(['name' => 'Тестовый источник']);
    }

    public function test_regional_representative_sees_companies_where_he_is_regional()
    {
        // Создаем пользователей
        $regionalUser = User::create([
            'name' => 'Региональный представитель',
            'email' => 'regional@test.com',
            'password' => bcrypt('password'),
            'role_id' => 3, // Региональный представитель
            'active' => true
        ]);
        
        $ownerUser = User::create([
            'name' => 'Владелец компании',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role_id' => 2, // Менеджер
            'active' => true
        ]);
        
        $adminUser = User::create([
            'name' => 'Администратор',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => 1, // Администратор
            'active' => true
        ]);
        
        // Создаем компании
        $company1 = Company::create([
            'sku' => 'TEST001',
            'name' => 'Компания 1',
            'inn' => '1234567890',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $regionalUser->id, // Региональный представитель
            'owner_user_id' => $ownerUser->id, // Владелец
            'email' => 'company1@test.com',
            'phone' => '+1234567890',
            'site' => 'http://company1.test',
            'common_info' => 'Тестовая компания 1',
            'company_status_id' => 1 // Активна
        ]);
        
        $company2 = Company::create([
            'sku' => 'TEST002',
            'name' => 'Компания 2',
            'inn' => '0987654321',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $adminUser->id, // Другой региональный представитель
            'owner_user_id' => $ownerUser->id, // Владелец
            'email' => 'company2@test.com',
            'phone' => '+0987654321',
            'site' => 'http://company2.test',
            'common_info' => 'Тестовая компания 2',
            'company_status_id' => 1 // Активна
        ]);
        
        $company3 = Company::create([
            'sku' => 'TEST003',
            'name' => 'Компания 3',
            'inn' => '1122334455',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $regionalUser->id, // Региональный представитель
            'owner_user_id' => $adminUser->id, // Другой владелец
            'email' => 'company3@test.com',
            'phone' => '+1122334455',
            'site' => 'http://company3.test',
            'common_info' => 'Тестовая компания 3',
            'company_status_id' => 1 // Активна
        ]);
        
        // Привязываем склады к компаниям
        $company1->warehouses()->attach(1);
        $company2->warehouses()->attach(1);
        $company3->warehouses()->attach(1);
        
        // Авторизуемся как региональный представитель
        $this->actingAs($regionalUser);
        
        // Переходим на страницу создания товара
        $response = $this->get('/product/create');
        
        $response->assertStatus(200);
        
        // Проверяем, что в списке компаний есть только те, где он региональный представитель
        $response->assertSee('Компания 1'); // Должна быть видна
        $response->assertSee('Компания 3'); // Должна быть видна
        $response->assertDontSee('Компания 2'); // Не должна быть видна
    }

    public function test_regional_representative_cannot_access_companies_where_he_is_not_regional()
    {
        // Создаем пользователей
        $regionalUser = User::create([
            'name' => 'Региональный представитель',
            'email' => 'regional@test.com',
            'password' => bcrypt('password'),
            'role_id' => 3, // Региональный представитель
            'active' => true
        ]);
        
        $ownerUser = User::create([
            'name' => 'Владелец компании',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role_id' => 2, // Менеджер
            'active' => true
        ]);
        
        // Создаем компанию, где региональный представитель НЕ назначен
        $company = Company::create([
            'sku' => 'TEST001',
            'name' => 'Компания без регионального',
            'inn' => '1234567890',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $ownerUser->id, // Другой пользователь как региональный
            'owner_user_id' => $ownerUser->id,
            'email' => 'company@test.com',
            'phone' => '+1234567890',
            'site' => 'http://company.test',
            'common_info' => 'Тестовая компания',
            'company_status_id' => 1 // Активна
        ]);
        
        $company->warehouses()->attach(1);
        
        // Авторизуемся как региональный представитель
        $this->actingAs($regionalUser);
        
        // Пытаемся создать товар для недоступной компании
        $response = $this->post('/product', [
            'company_id' => $company->id,
            'category_id' => 1,
            'name' => 'Тестовый товар',
            'product_address' => 'Тестовый адрес'
        ]);
        
        // Должна быть ошибка валидации
        $response->assertSessionHasErrors(['company_id']);
    }

    public function test_manager_sees_only_his_owned_companies()
    {
        // Создаем пользователей
        $managerUser = User::create([
            'name' => 'Менеджер',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role_id' => 2, // Менеджер
            'active' => true
        ]);
        
        $otherUser = User::create([
            'name' => 'Другой пользователь',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role_id' => 2, // Менеджер
            'active' => true
        ]);
        
        // Создаем компании
        $ownedCompany = Company::create([
            'sku' => 'OWNED001',
            'name' => 'Моя компания',
            'inn' => '1234567890',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $otherUser->id,
            'owner_user_id' => $managerUser->id, // Владелец - менеджер
            'email' => 'owned@test.com',
            'phone' => '+1234567890',
            'site' => 'http://owned.test',
            'common_info' => 'Моя компания',
            'company_status_id' => 1
        ]);
        
        $otherCompany = Company::create([
            'sku' => 'OTHER001',
            'name' => 'Чужая компания',
            'inn' => '0987654321',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $managerUser->id,
            'owner_user_id' => $otherUser->id, // Владелец - другой пользователь
            'email' => 'other@test.com',
            'phone' => '+0987654321',
            'site' => 'http://other.test',
            'common_info' => 'Чужая компания',
            'company_status_id' => 1
        ]);
        
        $ownedCompany->warehouses()->attach(1);
        $otherCompany->warehouses()->attach(1);
        
        // Авторизуемся как менеджер
        $this->actingAs($managerUser);
        
        // Переходим на страницу создания товара
        $response = $this->get('/product/create');
        
        $response->assertStatus(200);
        
        // Проверяем, что видна только его компания
        $response->assertSee('Моя компания');
        $response->assertDontSee('Чужая компания');
    }

    public function test_admin_sees_all_companies()
    {
        // Создаем пользователей
        $adminUser = User::create([
            'name' => 'Администратор',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => 1, // Администратор
            'active' => true
        ]);
        
        $otherUser = User::create([
            'name' => 'Другой пользователь',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role_id' => 2, // Менеджер
            'active' => true
        ]);
        
        // Создаем компании
        $company1 = Company::create([
            'sku' => 'COMP001',
            'name' => 'Компания 1',
            'inn' => '1234567890',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $otherUser->id,
            'owner_user_id' => $otherUser->id,
            'email' => 'company1@test.com',
            'phone' => '+1234567890',
            'site' => 'http://company1.test',
            'common_info' => 'Компания 1',
            'company_status_id' => 1
        ]);
        
        $company2 = Company::create([
            'sku' => 'COMP002',
            'name' => 'Компания 2',
            'inn' => '0987654321',
            'source_id' => 1,
            'region_id' => 1,
            'regional_user_id' => $otherUser->id,
            'owner_user_id' => $otherUser->id,
            'email' => 'company2@test.com',
            'phone' => '+0987654321',
            'site' => 'http://company2.test',
            'common_info' => 'Компания 2',
            'company_status_id' => 1
        ]);
        
        $company1->warehouses()->attach(1);
        $company2->warehouses()->attach(1);
        
        // Авторизуемся как администратор
        $this->actingAs($adminUser);
        
        // Переходим на страницу создания товара
        $response = $this->get('/product/create');
        
        $response->assertStatus(200);
        
        // Проверяем, что видны все компании
        $response->assertSee('Компания 1');
        $response->assertSee('Компания 2');
    }
}
