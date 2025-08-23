<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\Product;
use App\Models\ProductStatus;
use App\Models\AdvertisementStatus;
use App\Models\ProductCategories;
use App\Models\Company;
use App\Models\LogType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvertisementStatusProductCheckTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $advertisement;
    protected $holdStatus;
    protected $refuseStatus;
    protected $revisionStatus;
    protected $activeStatus;
    protected $reserveStatus;

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
        $this->holdStatus = ProductStatus::create(['name' => 'Холд', 'active' => true]);
        $this->refuseStatus = ProductStatus::create(['name' => 'Отказ', 'active' => true]);

        // Создаем статусы объявлений
        $this->revisionStatus = AdvertisementStatus::create(['name' => 'Ревизия', 'color' => '#FFA500', 'is_published' => false]);
        $this->activeStatus = AdvertisementStatus::create(['name' => 'Активное', 'color' => '#28A745', 'is_published' => true]);
        $this->reserveStatus = AdvertisementStatus::create(['name' => 'Резерв', 'color' => '#17A2B8', 'is_published' => true]);
        AdvertisementStatus::create(['name' => 'Холд', 'color' => '#FFC107', 'is_published' => false]);
        AdvertisementStatus::create(['name' => 'Архив', 'color' => '#6C757D', 'is_published' => false]);

        // Создаем пользователя
        $this->user = User::factory()->create();

        // Создаем компанию
        $company = Company::factory()->create(['owner_user_id' => $this->user->id]);

        // Создаем категорию
        $category = ProductCategories::factory()->create();

        // Создаем товар в статусе "В работе"
        $this->product = Product::factory()->create([
            'company_id' => $company->id,
            'owner_id' => $this->user->id,
            'category_id' => $category->id,
            'status_id' => ProductStatus::where('name', 'В работе')->first()->id
        ]);

        // Создаем объявление
        $this->advertisement = Advertisement::factory()->create([
            'product_id' => $this->product->id,
            'created_by' => $this->user->id,
            'status_id' => AdvertisementStatus::where('name', 'Холд')->first()->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_revision_when_product_in_hold()
    {
        // Устанавливаем товар в статус "Холд"
        $this->product->update(['status_id' => $this->holdStatus->id]);

        // Пытаемся изменить статус объявления на "Ревизия"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->revisionStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Ревизия', так как связанный товар находится в статусе 'Холд'. Сначала переведите товар из статуса 'Холд'.",
            'product_status' => 'Холд',
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_active_when_product_in_hold()
    {
        // Устанавливаем товар в статус "Холд"
        $this->product->update(['status_id' => $this->holdStatus->id]);

        // Пытаемся изменить статус объявления на "Активное"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->activeStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Активное', так как связанный товар находится в статусе 'Холд'. Сначала переведите товар из статуса 'Холд'.",
            'product_status' => 'Холд',
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_reserve_when_product_in_hold()
    {
        // Устанавливаем товар в статус "Холд"
        $this->product->update(['status_id' => $this->holdStatus->id]);

        // Пытаемся изменить статус объявления на "Резерв"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->reserveStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Резерв', так как связанный товар находится в статусе 'Холд'. Сначала переведите товар из статуса 'Холд'.",
            'product_status' => 'Холд',
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_revision_when_product_in_refuse()
    {
        // Устанавливаем товар в статус "Отказ"
        $this->product->update(['status_id' => $this->refuseStatus->id]);

        // Пытаемся изменить статус объявления на "Ревизия"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->revisionStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Ревизия', так как связанный товар находится в статусе 'Отказ'. Сначала переведите товар из статуса 'Отказ'.",
            'product_status' => 'Отказ',
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_active_when_product_in_refuse()
    {
        // Устанавливаем товар в статус "Отказ"
        $this->product->update(['status_id' => $this->refuseStatus->id]);

        // Пытаемся изменить статус объявления на "Активное"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->activeStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Активное', так как связанный товар находится в статусе 'Отказ'. Сначала переведите товар из статуса 'Отказ'.",
            'product_status' => 'Отказ',
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_change_advertisement_status_to_reserve_when_product_in_refuse()
    {
        // Устанавливаем товар в статус "Отказ"
        $this->product->update(['status_id' => $this->refuseStatus->id]);

        // Пытаемся изменить статус объявления на "Резерв"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->reserveStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => "Нельзя перевести объявление в статус 'Резерв', так как связанный товар находится в статусе 'Отказ'. Сначала переведите товар из статуса 'Отказ'.",
            'product_status' => 'Отказ',
            'product_id' => $this->product->id
        ]);
    }

    public function test_can_change_advertisement_status_when_product_not_in_restricted_statuses()
    {
        // Товар остается в статусе "В работе" (не ограниченный статус)

        // Пытаемся изменить статус объявления на "Активное"
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $this->activeStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос успешен
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что статус объявления изменился
        $this->advertisement->refresh();
        $this->assertEquals('Активное', $this->advertisement->status->name);
    }

    public function test_can_change_advertisement_status_to_other_statuses_when_product_in_hold()
    {
        // Устанавливаем товар в статус "Холд"
        $this->product->update(['status_id' => $this->holdStatus->id]);

        // Пытаемся изменить статус объявления на "Архив" (не ограниченный статус)
        $archiveStatus = AdvertisementStatus::where('name', 'Архив')->first();
        
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$this->advertisement->id}/status", [
                'status_id' => $archiveStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос успешен
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что статус объявления изменился
        $this->advertisement->refresh();
        $this->assertEquals('Архив', $this->advertisement->status->name);
    }

    public function test_returns_error_when_advertisement_has_no_product()
    {
        // Создаем объявление без товара
        $advertisementWithoutProduct = Advertisement::factory()->create([
            'product_id' => null,
            'created_by' => $this->user->id,
            'status_id' => AdvertisementStatus::where('name', 'Холд')->first()->id
        ]);

        // Пытаемся изменить статус объявления
        $response = $this->actingAs($this->user)
            ->patchJson("/advertisements/{$advertisementWithoutProduct->id}/status", [
                'status_id' => $this->activeStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        // Проверяем, что запрос отклонен
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Объявление не связано с товаром'
        ]);
    }
}
