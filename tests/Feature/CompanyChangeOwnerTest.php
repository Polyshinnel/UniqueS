<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\RoleList;
use App\Models\Warehouses;
use App\Models\Regions;
use App\Models\Sources;
use App\Models\CompanyStatuses;
use App\Models\Product;
use App\Models\Advertisement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyChangeOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $regional;
    protected $warehouse;
    protected $company;

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

        // Создаем пользователей
        $this->admin = User::create([
            'name' => 'Администратор',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'active' => true
        ]);

        $this->manager = User::create([
            'name' => 'Менеджер',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role_id' => $managerRole->id,
            'active' => true
        ]);

        $this->regional = User::create([
            'name' => 'Региональный представитель',
            'email' => 'regional@test.com',
            'password' => bcrypt('password'),
            'role_id' => $regionalRole->id,
            'active' => true
        ]);

        // Создаем регион
        $region = Regions::create([
            'name' => 'Тестовый регион',
            'active' => true
        ]);

        // Создаем склад
        $this->warehouse = Warehouses::create([
            'name' => 'Тестовый склад',
            'active' => true
        ]);

        // Связываем склад с регионом
        $this->warehouse->regions()->attach($region->id);

        // Связываем пользователей со складом
        $this->admin->warehouses()->attach($this->warehouse->id);
        $this->manager->warehouses()->attach($this->warehouse->id);
        $this->regional->warehouses()->attach($this->warehouse->id);

        // Создаем источник
        $source = Sources::create([
            'name' => 'Тестовый источник'
        ]);

        // Создаем статус компании
        $status = CompanyStatuses::create([
            'name' => 'Активная'
        ]);

        // Создаем компанию
        $this->company = Company::create([
            'sku' => 'TEST-001',
            'name' => 'Тестовая компания',
            'inn' => '1234567890',
            'source_id' => $source->id,
            'region_id' => $region->id,
            'regional_user_id' => $this->regional->id,
            'owner_user_id' => $this->admin->id,
            'company_status_id' => $status->id
        ]);

        // Связываем компанию со складом
        $this->company->warehouses()->attach($this->warehouse->id);
    }

    /** @test */
    public function admin_can_change_company_owner()
    {
        $this->actingAs($this->admin);

        $response = $this->patchJson("/company/{$this->company->id}/change-owner", [
            'new_owner_id' => $this->manager->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ответственный успешно изменен'
            ]);

        // Проверяем, что ответственный изменился
        $this->company->refresh();
        $this->assertEquals($this->manager->id, $this->company->owner_user_id);
    }

    /** @test */
    public function manager_cannot_change_company_owner()
    {
        $this->actingAs($this->manager);

        $response = $this->patchJson("/company/{$this->company->id}/change-owner", [
            'new_owner_id' => $this->admin->id
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Недостаточно прав для смены ответственного. Только администраторы могут выполнять эту операцию.'
            ]);
    }

    /** @test */
    public function regional_cannot_change_company_owner()
    {
        $this->actingAs($this->regional);

        $response = $this->patchJson("/company/{$this->company->id}/change-owner", [
            'new_owner_id' => $this->admin->id
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Недостаточно прав для смены ответственного. Только администраторы могут выполнять эту операцию.'
            ]);
    }

    /** @test */
    public function admin_can_get_available_owners()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson("/company/{$this->company->id}/available-owners");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $users = $response->json('users');
        $this->assertCount(2, $users); // Администратор и Менеджер
    }

    /** @test */
    public function changing_owner_updates_products_and_advertisements()
    {
        $this->actingAs($this->admin);

        // Создаем товар
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => $this->company->id,
            'owner_id' => $this->admin->id,
            'warehouse_id' => $this->warehouse->id,
            'category_id' => 1,
            'status_id' => 1,
            'state_id' => 1,
            'available_id' => 1
        ]);

        // Создаем объявление
        $advertisement = Advertisement::create([
            'product_id' => $product->id,
            'title' => 'Тестовое объявление',
            'created_by' => $this->admin->id,
            'status_id' => 1
        ]);

        // Меняем ответственного
        $response = $this->patchJson("/company/{$this->company->id}/change-owner", [
            'new_owner_id' => $this->manager->id
        ]);

        $response->assertStatus(200);

        // Проверяем, что owner_id товара изменился
        $product->refresh();
        $this->assertEquals($this->manager->id, $product->owner_id);

        // Проверяем, что created_by объявления изменился
        $advertisement->refresh();
        $this->assertEquals($this->manager->id, $advertisement->created_by);
    }

    /** @test */
    public function cannot_assign_user_not_attached_to_company_warehouse()
    {
        $this->actingAs($this->admin);

        // Создаем пользователя, не привязанного к складу компании
        $otherUser = User::create([
            'name' => 'Другой пользователь',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->manager->role_id,
            'active' => true
        ]);

        $response = $this->patchJson("/company/{$this->company->id}/change-owner", [
            'new_owner_id' => $otherUser->id
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Указанный пользователь не может быть назначен ответственным. Доступны только администраторы и менеджеры, привязанные к складам компании.'
            ]);
    }

    /** @test */
    public function admin_can_change_company_regional()
    {
        $this->actingAs($this->admin);

        $response = $this->patchJson("/company/{$this->company->id}/change-regional", [
            'new_regional_id' => $this->regional->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Региональный представитель успешно изменен'
            ]);

        // Проверяем, что региональный представитель изменился
        $this->company->refresh();
        $this->assertEquals($this->regional->id, $this->company->regional_user_id);
    }

    /** @test */
    public function manager_cannot_change_company_regional()
    {
        $this->actingAs($this->manager);

        $response = $this->patchJson("/company/{$this->company->id}/change-regional", [
            'new_regional_id' => $this->regional->id
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Недостаточно прав для смены регионального представителя. Только администраторы могут выполнять эту операцию.'
            ]);
    }

    /** @test */
    public function admin_can_get_available_regionals()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson("/company/{$this->company->id}/available-regionals");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $users = $response->json('users');
        $this->assertCount(1, $users); // Только региональный представитель
    }

    /** @test */
    public function changing_regional_updates_products()
    {
        $this->actingAs($this->admin);

        // Создаем товар
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => $this->company->id,
            'owner_id' => $this->admin->id,
            'regional_id' => $this->admin->id, // Изначально админ
            'warehouse_id' => $this->warehouse->id,
            'category_id' => 1,
            'status_id' => 1,
            'state_id' => 1,
            'available_id' => 1
        ]);

        // Меняем регионального представителя
        $response = $this->patchJson("/company/{$this->company->id}/change-regional", [
            'new_regional_id' => $this->regional->id
        ]);

        $response->assertStatus(200);

        // Проверяем, что regional_id товара изменился
        $product->refresh();
        $this->assertEquals($this->regional->id, $product->regional_id);
    }

    /** @test */
    public function cannot_assign_non_regional_user_as_regional()
    {
        $this->actingAs($this->admin);

        $response = $this->patchJson("/company/{$this->company->id}/change-regional", [
            'new_regional_id' => $this->manager->id // Менеджер не может быть региональным представителем
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Указанный пользователь не может быть назначен региональным представителем. Доступны только региональные представители, привязанные к складам компании.'
            ]);
    }
}
