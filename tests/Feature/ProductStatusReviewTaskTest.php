<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\ProductStatus;
use App\Models\Advertisement;
use App\Models\AdvertisementStatus;
use App\Models\AdvAction;
use App\Models\Company;
use App\Models\Regions;
use App\Models\Warehouses;
use App\Models\RoleList;
use App\Models\LogType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductStatusReviewTaskTest extends TestCase
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
        
        // Создаем пользователей
        $admin = User::create([
            'name' => 'Администратор',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'active' => true
        ]);
        
        $creator = User::create([
            'name' => 'Создатель объявления',
            'email' => 'creator@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'active' => true
        ]);
        
        // Создаем категории
        $category = ProductCategories::create(['name' => 'Тестовая категория', 'active' => true]);
        
        // Создаем статусы товаров
        $inWorkStatus = ProductStatus::create(['name' => 'В работе', 'active' => true]);
        $reviewStatus = ProductStatus::create(['name' => 'Ревизия', 'active' => true]);
        
        // Создаем статусы объявлений
        $activeAdvStatus = AdvertisementStatus::create(['name' => 'Активное', 'active' => true]);
        $reviewAdvStatus = AdvertisementStatus::create(['name' => 'Ревизия', 'active' => true]);
        
        // Создаем регион
        $region = Regions::create(['name' => 'Тестовый регион', 'active' => true]);
        
        // Создаем склад
        $warehouse = Warehouses::create(['name' => 'Тестовый склад']);
        $warehouse->regions()->attach($region->id);
        
        // Создаем компанию
        $company = Company::create([
            'name' => 'Тестовая компания',
            'owner_user_id' => $admin->id,
            'regional_user_id' => $admin->id,
            'company_status_id' => 1
        ]);
        $company->warehouses()->attach($warehouse->id);
        
        // Создаем товар
        $this->product = Product::create([
            'name' => 'Тестовый товар',
            'sku' => 'TEST001',
            'category_id' => $category->id,
            'company_id' => $company->id,
            'owner_id' => $admin->id,
            'regional_id' => $admin->id,
            'status_id' => $inWorkStatus->id,
            'warehouse_id' => $warehouse->id,
            'product_address' => 'Тестовый адрес',
            'add_expenses' => 0
        ]);
        
        // Создаем объявление
        $this->advertisement = Advertisement::create([
            'product_id' => $this->product->id,
            'title' => 'Тестовое объявление',
            'category_id' => $category->id,
            'status_id' => $activeAdvStatus->id,
            'created_by' => $creator->id
        ]);
        
        // Создаем типы логов
        LogType::create(['name' => 'Системный']);
        LogType::create(['name' => 'Комментарий']);
        
        $this->admin = $admin;
        $this->creator = $creator;
        $this->reviewStatus = $reviewStatus;
    }

    public function test_product_status_change_to_review_creates_advertisement_task()
    {
        // Проверяем, что изначально нет задач в объявлении
        $this->assertEquals(0, AdvAction::where('advertisement_id', $this->advertisement->id)->count());
        
        // Меняем статус товара на "Ревизия"
        $this->actingAs($this->admin);
        
        $response = $this->post(route('products.update-status', $this->product), [
            'status_id' => $this->reviewStatus->id,
            'comment' => 'Тестовый комментарий для смены статуса'
        ]);
        
        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Проверяем, что статус товара изменился
        $this->product->refresh();
        $this->assertEquals('Ревизия', $this->product->status->name);
        
        // Проверяем, что статус объявления изменился на "Ревизия"
        $this->advertisement->refresh();
        $this->assertEquals('Ревизия', $this->advertisement->status->name);
        
        // Проверяем, что создалась задача в объявлении
        $task = AdvAction::where('advertisement_id', $this->advertisement->id)->first();
        $this->assertNotNull($task);
        
        // Проверяем детали задачи
        $this->assertEquals($this->advertisement->id, $task->advertisement_id);
        $this->assertEquals($this->creator->id, $task->user_id);
        $this->assertEquals('Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.', $task->action);
        $this->assertFalse($task->status);
        
        // Проверяем, что срок выполнения задачи - 7 дней от текущего времени
        $expectedExpiredAt = now()->addDays(7);
        $this->assertTrue($task->expired_at->diffInMinutes($expectedExpiredAt) < 5); // Разница менее 5 минут
    }

    public function test_product_status_change_to_other_status_does_not_create_task()
    {
        // Создаем другой статус товара
        $otherStatus = ProductStatus::create(['name' => 'В продаже', 'active' => true]);
        
        // Проверяем, что изначально нет задач в объявлении
        $this->assertEquals(0, AdvAction::where('advertisement_id', $this->advertisement->id)->count());
        
        // Меняем статус товара на другой статус (не "Ревизия")
        $this->actingAs($this->admin);
        
        $response = $this->post(route('products.update-status', $this->product), [
            'status_id' => $otherStatus->id,
            'comment' => 'Тестовый комментарий для смены статуса'
        ]);
        
        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Проверяем, что статус товара изменился
        $this->product->refresh();
        $this->assertEquals('В продаже', $this->product->status->name);
        
        // Проверяем, что НЕ создалась задача в объявлении
        $this->assertEquals(0, AdvAction::where('advertisement_id', $this->advertisement->id)->count());
    }

    public function test_multiple_advertisements_get_tasks_when_product_status_changes_to_review()
    {
        // Создаем второе объявление для того же товара
        $secondAdvertisement = Advertisement::create([
            'product_id' => $this->product->id,
            'title' => 'Второе тестовое объявление',
            'category_id' => $this->product->category_id,
            'status_id' => AdvertisementStatus::where('name', 'Активное')->first()->id,
            'created_by' => $this->creator->id
        ]);
        
        // Проверяем, что изначально нет задач в объявлениях
        $this->assertEquals(0, AdvAction::where('advertisement_id', $this->advertisement->id)->count());
        $this->assertEquals(0, AdvAction::where('advertisement_id', $secondAdvertisement->id)->count());
        
        // Меняем статус товара на "Ревизия"
        $this->actingAs($this->admin);
        
        $response = $this->post(route('products.update-status', $this->product), [
            'status_id' => $this->reviewStatus->id,
            'comment' => 'Тестовый комментарий для смены статуса'
        ]);
        
        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Проверяем, что создались задачи в обоих объявлениях
        $this->assertEquals(1, AdvAction::where('advertisement_id', $this->advertisement->id)->count());
        $this->assertEquals(1, AdvAction::where('advertisement_id', $secondAdvertisement->id)->count());
        
        // Проверяем, что оба объявления перешли в статус "Ревизия"
        $this->advertisement->refresh();
        $secondAdvertisement->refresh();
        $this->assertEquals('Ревизия', $this->advertisement->status->name);
        $this->assertEquals('Ревизия', $secondAdvertisement->status->name);
    }
}
