<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Product;
use App\Models\Advertisement;
use App\Models\CompanyStatuses;
use App\Models\ProductStatus;
use App\Models\AdvertisementStatus;
use App\Models\CompanyActions;
use App\Models\ProductAction;
use App\Models\AdvAction;
use App\Models\CompanyLog;
use App\Models\ProductLog;
use App\Models\AdvLog;
use App\Models\LogType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CompanyRefuseStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;
    protected $refuseCompanyStatus;
    protected $refuseProductStatus;
    protected $archiveAdvertisementStatus;
    protected $systemLogType;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем пользователя
        $this->user = User::factory()->create();

        // Создаем статусы
        $this->refuseCompanyStatus = CompanyStatuses::create(['name' => 'Отказ']);
        $this->refuseProductStatus = ProductStatus::create(['name' => 'Отказ']);
        $this->archiveAdvertisementStatus = AdvertisementStatus::create(['name' => 'Архив']);

        // Создаем тип лога
        $this->systemLogType = LogType::create(['name' => 'Системный']);

        // Создаем компанию
        $this->company = Company::factory()->create([
            'company_status_id' => CompanyStatuses::create(['name' => 'В работе'])->id,
            'owner_user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_creates_company_action_when_company_status_changed_to_refuse()
    {
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что создана задача для компании
        $this->assertDatabaseHas('company_actions', [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'action' => 'Актуализировать данные, уточнить по оборудованию и ценам',
            'status' => false
        ]);
    }

    /** @test */
    public function it_updates_product_statuses_when_company_status_changed_to_refuse()
    {
        // Создаем товары для компании
        $product1 = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create(['name' => 'В работе'])->id
        ]);

        $product2 = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create(['name' => 'В продаже'])->id
        ]);

        // Товар в статусе "Продано" не должен измениться
        $soldProductStatus = ProductStatus::create(['name' => 'Продано']);
        $product3 = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => $soldProductStatus->id
        ]);

        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что статусы товаров обновились
        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'status_id' => $this->refuseProductStatus->id
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product2->id,
            'status_id' => $this->refuseProductStatus->id
        ]);

        // Проверяем, что товар в статусе "Продано" не изменился
        $this->assertDatabaseHas('products', [
            'id' => $product3->id,
            'status_id' => $soldProductStatus->id
        ]);
    }

    /** @test */
    public function it_creates_product_logs_and_actions_when_company_status_changed_to_refuse()
    {
        // Создаем товар для компании
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create(['name' => 'В работе'])->id,
            'owner_id' => $this->user->id
        ]);

        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создан лог для товара
        $this->assertDatabaseHas('product_logs', [
            'product_id' => $product->id,
            'type_id' => $this->systemLogType->id,
            'log' => "В связи с переводом компании \"{$this->company->name}\" в статус Отказ, товар переводится в статус Отказ.",
            'user_id' => null
        ]);

        // Проверяем, что создана задача для товара
        $this->assertDatabaseHas('product_actions', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'action' => 'Актуализировать данные по товару, информации о проверке, погрузке, демонтаже, комплектации и стоимости.',
            'status' => false
        ]);
    }

    /** @test */
    public function it_updates_advertisement_statuses_when_company_status_changed_to_refuse()
    {
        // Создаем товар для компании
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create(['name' => 'В работе'])->id
        ]);

        // Создаем объявления для товара
        $activeAdvertisementStatus = AdvertisementStatus::create(['name' => 'Активное']);
        $advertisement1 = Advertisement::factory()->create([
            'product_id' => $product->id,
            'status_id' => $activeAdvertisementStatus->id
        ]);

        $advertisement2 = Advertisement::factory()->create([
            'product_id' => $product->id,
            'status_id' => $activeAdvertisementStatus->id
        ]);

        // Объявление в статусе "Продано" не должно измениться
        $soldAdvertisementStatus = AdvertisementStatus::create(['name' => 'Продано']);
        $advertisement3 = Advertisement::factory()->create([
            'product_id' => $product->id,
            'status_id' => $soldAdvertisementStatus->id
        ]);

        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что статусы объявлений обновились на "Архив"
        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement1->id,
            'status_id' => $this->archiveAdvertisementStatus->id
        ]);

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement2->id,
            'status_id' => $this->archiveAdvertisementStatus->id
        ]);

        // Проверяем, что объявление в статусе "Продано" не изменилось
        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement3->id,
            'status_id' => $soldAdvertisementStatus->id
        ]);
    }

    /** @test */
    public function it_creates_advertisement_logs_and_actions_when_company_status_changed_to_refuse()
    {
        // Создаем товар для компании
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => ProductStatus::create(['name' => 'В работе'])->id
        ]);

        // Создаем объявление для товара
        $activeAdvertisementStatus = AdvertisementStatus::create(['name' => 'Активное']);
        $advertisement = Advertisement::factory()->create([
            'product_id' => $product->id,
            'status_id' => $activeAdvertisementStatus->id,
            'created_by' => $this->user->id
        ]);

        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создан лог для объявления
        $this->assertDatabaseHas('adv_logs', [
            'advertisement_id' => $advertisement->id,
            'type_id' => $this->systemLogType->id,
            'log' => "В связи с переводом компании \"{$this->company->name}\" в статус Отказ, объявление переводится в статус Архив.",
            'user_id' => null
        ]);

        // Проверяем, что создана задача для объявления
        $this->assertDatabaseHas('adv_actions', [
            'advertisement_id' => $advertisement->id,
            'user_id' => $this->user->id,
            'action' => 'Актуализировать данные по объявлению, скорректировать текст объявления, условия продажи и стоимость.',
            'status' => false
        ]);
    }

    /** @test */
    public function it_does_not_affect_products_and_advertisements_in_excluded_statuses()
    {
        // Создаем товары в исключенных статусах
        $soldProductStatus = ProductStatus::create(['name' => 'Продано']);
        $refuseProductStatus = ProductStatus::create(['name' => 'Отказ']);

        $soldProduct = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => $soldProductStatus->id
        ]);

        $refuseProduct = Product::factory()->create([
            'company_id' => $this->company->id,
            'status_id' => $refuseProductStatus->id
        ]);

        // Создаем объявления в исключенных статусах
        $soldAdvertisementStatus = AdvertisementStatus::create(['name' => 'Продано']);
        $archiveAdvertisementStatus = AdvertisementStatus::create(['name' => 'Архив']);

        $soldAdvertisement = Advertisement::factory()->create([
            'product_id' => $soldProduct->id,
            'status_id' => $soldAdvertisementStatus->id
        ]);

        $archiveAdvertisement = Advertisement::factory()->create([
            'product_id' => $refuseProduct->id,
            'status_id' => $archiveAdvertisementStatus->id
        ]);

        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что статусы товаров в исключенных статусах не изменились
        $this->assertDatabaseHas('products', [
            'id' => $soldProduct->id,
            'status_id' => $soldProductStatus->id
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $refuseProduct->id,
            'status_id' => $refuseProductStatus->id
        ]);

        // Проверяем, что статусы объявлений в исключенных статусах не изменились
        $this->assertDatabaseHas('advertisements', [
            'id' => $soldAdvertisement->id,
            'status_id' => $soldAdvertisementStatus->id
        ]);

        $this->assertDatabaseHas('advertisements', [
            'id' => $archiveAdvertisement->id,
            'status_id' => $archiveAdvertisementStatus->id
        ]);
    }

    /** @test */
    public function it_creates_company_log_with_correct_information()
    {
        // Выполняем запрос на изменение статуса
        $response = $this->actingAs($this->user)
            ->patchJson("/company/{$this->company->id}/status", [
                'status_id' => $this->refuseCompanyStatus->id,
                'comment' => 'Тестовый комментарий'
            ]);

        $response->assertStatus(200);

        // Проверяем, что создан лог компании
        $this->assertDatabaseHas('company_logs', [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'log' => 'Смена статуса с \'В работе\' на \'Отказ\'. Комментарий: Тестовый комментарий'
        ]);
    }
} 