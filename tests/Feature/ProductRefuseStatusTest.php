<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\Advertisement;
use App\Models\AdvertisementStatus;
use App\Models\ProductAction;
use App\Models\AdvAction;
use App\Models\ProductLog;
use App\Models\AdvLog;
use App\Models\User;
use App\Models\Company;
use App\Models\ProductCategories;
use App\Models\LogType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductRefuseStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $product;
    protected $activeAdvertisement;
    protected $soldAdvertisement;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем необходимые данные для тестов
        $this->createTestData();
    }

    private function createTestData()
    {
        // Создаем типы логов
        LogType::create(['name' => 'Системный']);
        LogType::create(['name' => 'Комментарий']);

        // Создаем статусы товаров
        ProductStatus::create(['name' => 'В работе', 'active' => true]);
        ProductStatus::create(['name' => 'Отказ', 'active' => true]);

        // Создаем статусы объявлений
        AdvertisementStatus::create(['name' => 'Активное', 'color' => '#28A745', 'is_published' => true]);
        AdvertisementStatus::create(['name' => 'Холд', 'color' => '#FFC107', 'is_published' => false]);
        AdvertisementStatus::create(['name' => 'Архив', 'color' => '#6C757D', 'is_published' => false]);
        AdvertisementStatus::create(['name' => 'Продано', 'color' => '#6F42C1', 'is_published' => true]);

        // Создаем пользователей
        $owner = User::factory()->create();
        $creator = User::factory()->create();

        // Создаем компанию
        $company = Company::factory()->create(['owner_user_id' => $owner->id]);

        // Создаем категорию
        $category = ProductCategories::factory()->create();

        // Создаем товар
        $this->product = Product::factory()->create([
            'company_id' => $company->id,
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'status_id' => ProductStatus::where('name', 'В работе')->first()->id
        ]);

        // Создаем объявления
        $this->activeAdvertisement = Advertisement::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $creator->id,
            'status_id' => AdvertisementStatus::where('name', 'Активное')->first()->id
        ]);

        $this->soldAdvertisement = Advertisement::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $creator->id,
            'status_id' => AdvertisementStatus::where('name', 'Продано')->first()->id
        ]);
    }

    public function test_product_refuse_status_creates_task()
    {
        $refuseStatus = ProductStatus::where('name', 'Отказ')->first();
        
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs(User::find($this->product->owner_id))
            ->patchJson("/product/{$this->product->id}/status", [
                'status_id' => $refuseStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что статус товара изменился
        $this->product->refresh();
        $this->assertEquals('Отказ', $this->product->status->name);

        // Проверяем, что создалась задача для товара
        $productAction = ProductAction::where('product_id', $this->product->id)
            ->where('action', 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.')
            ->first();

        $this->assertNotNull($productAction);
        $this->assertEquals($this->product->owner_id, $productAction->user_id);
        $this->assertFalse($productAction->status);
        $this->assertEquals(now()->addMonths(6)->format('Y-m-d'), $productAction->expired_at->format('Y-m-d'));
    }

    public function test_product_refuse_status_updates_advertisements()
    {
        $refuseStatus = ProductStatus::where('name', 'Отказ')->first();
        
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs(User::find($this->product->owner_id))
            ->patchJson("/product/{$this->product->id}/status", [
                'status_id' => $refuseStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что активное объявление перешло в статус Архив
        $this->activeAdvertisement->refresh();
        $this->assertEquals('Архив', $this->activeAdvertisement->status->name);

        // Проверяем, что проданное объявление осталось в статусе Продано
        $this->soldAdvertisement->refresh();
        $this->assertEquals('Продано', $this->soldAdvertisement->status->name);
    }

    public function test_product_refuse_status_creates_advertisement_logs()
    {
        $refuseStatus = ProductStatus::where('name', 'Отказ')->first();
        
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs(User::find($this->product->owner_id))
            ->patchJson("/product/{$this->product->id}/status", [
                'status_id' => $refuseStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создался лог для активного объявления
        $advLog = AdvLog::where('advertisement_id', $this->activeAdvertisement->id)
            ->where('log', 'В связи с переводом товара в статус Отказ, объявление переводится в статус Архив.')
            ->first();

        $this->assertNotNull($advLog);
        $this->assertNull($advLog->user_id); // От имени системы

        // Проверяем, что НЕ создался лог для проданного объявления
        $soldAdvLog = AdvLog::where('advertisement_id', $this->soldAdvertisement->id)
            ->where('log', 'В связи с переводом товара в статус Отказ, объявление переводится в статус Архив.')
            ->first();

        $this->assertNull($soldAdvLog);
    }

    public function test_product_refuse_status_creates_advertisement_tasks()
    {
        $refuseStatus = ProductStatus::where('name', 'Отказ')->first();
        
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs(User::find($this->product->owner_id))
            ->patchJson("/product/{$this->product->id}/status", [
                'status_id' => $refuseStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создалась задача для активного объявления
        $advAction = AdvAction::where('advertisement_id', $this->activeAdvertisement->id)
            ->where('action', 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.')
            ->first();

        $this->assertNotNull($advAction);
        $this->assertEquals($this->activeAdvertisement->created_by, $advAction->user_id);
        $this->assertFalse($advAction->status);
        $this->assertEquals(now()->addMonths(6)->format('Y-m-d'), $advAction->expired_at->format('Y-m-d'));

        // Проверяем, что НЕ создалась задача для проданного объявления
        $soldAdvAction = AdvAction::where('advertisement_id', $this->soldAdvertisement->id)
            ->where('action', 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.')
            ->first();

        $this->assertNull($soldAdvAction);
    }

    public function test_product_refuse_status_creates_product_log()
    {
        $refuseStatus = ProductStatus::where('name', 'Отказ')->first();
        
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs(User::find($this->product->owner_id))
            ->patchJson("/product/{$this->product->id}/status", [
                'status_id' => $refuseStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создался лог для товара
        $productLog = ProductLog::where('product_id', $this->product->id)
            ->where('log', 'Смена статуса товара с \'В работе\' на \'Отказ\'. Комментарий: Тестовый комментарий')
            ->first();

        $this->assertNotNull($productLog);
        $this->assertEquals($this->product->owner_id, $productLog->user_id);
    }
} 