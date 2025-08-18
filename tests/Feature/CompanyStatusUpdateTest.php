<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Product;
use App\Models\CompanyStatuses;
use App\Models\ProductStatus;
use App\Models\CompanyActions;
use App\Models\CompanyLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $holdStatus;
    protected $refuseStatus;
    protected $holdProductStatus;
    protected $refuseProductStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем пользователя
        $this->user = User::factory()->create();

        // Создаем статусы компаний
        $this->holdStatus = CompanyStatuses::create([
            'name' => 'Холд',
            'color' => '#DF7F2B',
            'active' => true,
        ]);

        $this->refuseStatus = CompanyStatuses::create([
            'name' => 'Отказ',
            'color' => '#F60F0F',
            'active' => true,
        ]);

        // Создаем статусы товаров
        $this->holdProductStatus = ProductStatus::create([
            'name' => 'Холд',
            'color' => '#FFC107',
            'active' => false,
            'must_active_adv' => false,
        ]);

        $this->refuseProductStatus = ProductStatus::create([
            'name' => 'Отказ',
            'color' => '#DC3545',
            'active' => false,
            'must_active_adv' => false,
        ]);

        // Создаем компанию
        $this->company = Company::factory()->create([
            'company_status_id' => CompanyStatuses::create([
                'name' => 'В работе',
                'color' => '#133E71',
                'active' => true,
            ])->id,
        ]);

        // Создаем товары для компании
        Product::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create([
                'name' => 'В продаже',
                'color' => '#28A745',
                'active' => true,
                'must_active_adv' => true,
            ])->id,
        ]);

        // Создаем тип лога
        LogType::create([
            'name' => 'Комментарий',
            'color' => '#133E71',
        ]);
    }

    public function test_company_status_update_to_hold_updates_products()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->holdStatus->id,
                'comment' => 'Тестовый комментарий для Холд'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Проверяем, что статус компании обновился
        $this->company->refresh();
        $this->assertEquals($this->holdStatus->id, $this->company->company_status_id);

        // Проверяем, что статусы товаров обновились
        $products = $this->company->products;
        foreach ($products as $product) {
            $this->assertEquals($this->holdProductStatus->id, $product->status_id);
        }

        // Проверяем, что создался лог
        $log = CompanyLog::where('company_id', $this->company->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Холд', $log->log);
        $this->assertStringContainsString('Обновлен статус 3 товаров', $log->log);
    }

    public function test_company_status_update_to_refuse_creates_action()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseStatus->id,
                'comment' => 'Тестовый комментарий для Отказ'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Проверяем, что статус компании обновился
        $this->company->refresh();
        $this->assertEquals($this->refuseStatus->id, $this->company->company_status_id);

        // Проверяем, что статусы товаров обновились
        $products = $this->company->products;
        foreach ($products as $product) {
            $this->assertEquals($this->refuseProductStatus->id, $product->status_id);
        }

        // Проверяем, что создалось действие
        $action = CompanyActions::where('company_id', $this->company->id)->latest()->first();
        $this->assertNotNull($action);
        $this->assertEquals('Уточнить наличие станков и дальнейший статус компании', $action->action);
        $this->assertEquals($this->user->id, $action->user_id);
        $this->assertFalse($action->status);
        $this->assertEquals(now()->addMonths(6)->format('Y-m-d'), $action->expired_at->format('Y-m-d'));

        // Проверяем, что создался лог
        $log = CompanyLog::where('company_id', $this->company->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Отказ', $log->log);
        $this->assertStringContainsString('Обновлен статус 3 товаров', $log->log);
        $this->assertStringContainsString('Создано действие', $log->log);
    }

    public function test_company_status_update_to_other_status_does_not_update_products()
    {
        $otherStatus = CompanyStatuses::create([
            'name' => 'Вторая очередь',
            'color' => '#35A645',
            'active' => true,
        ]);

        $originalProductStatuses = $this->company->products->pluck('status_id')->toArray();

        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $otherStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Проверяем, что статус компании обновился
        $this->company->refresh();
        $this->assertEquals($otherStatus->id, $this->company->company_status_id);

        // Проверяем, что статусы товаров НЕ изменились
        $this->company->load('products');
        $newProductStatuses = $this->company->products->pluck('status_id')->toArray();
        $this->assertEquals($originalProductStatuses, $newProductStatuses);

        // Проверяем, что действие НЕ создалось
        $action = CompanyActions::where('company_id', $this->company->id)->latest()->first();
        $this->assertNull($action);
    }

    public function test_company_without_products_handled_correctly()
    {
        // Удаляем все товары компании
        $this->company->products()->delete();

        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->holdStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Проверяем, что создался лог с сообщением об отсутствии товаров
        $log = CompanyLog::where('company_id', $this->company->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Товары для обновления не найдены', $log->log);
    }
}
