<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\LogType;
use App\Models\Company;
use App\Models\ProductCategories;
use App\Models\ProductStatus;
use App\Models\Warehouses;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCommentaryLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем необходимые данные для тестов
        $this->createTestData();
    }

    private function createTestData()
    {
        // Создаем тип лога "Системный"
        LogType::create([
            'name' => 'Системный',
            'color' => '#6c757d'
        ]);

        // Создаем склад
        $warehouse = Warehouses::create([
            'name' => 'Тестовый склад',
            'address' => 'Тестовый адрес'
        ]);

        // Создаем компанию
        $company = Company::create([
            'name' => 'Тестовая компания',
            'sku' => 'TEST001',
            'company_status_id' => 1,
            'owner_user_id' => 1
        ]);

        // Создаем категорию товара
        $category = ProductCategories::create([
            'name' => 'Тестовая категория'
        ]);

        // Создаем статус товара
        $status = ProductStatus::create([
            'name' => 'Тестовый статус',
            'color' => '#133E71',
            'active' => true
        ]);
    }

    public function test_commentary_log_creation_on_update()
    {
        // Создаем товар
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => Company::first()->id,
            'category_id' => ProductCategories::first()->id,
            'status_id' => ProductStatus::first()->id,
            'warehouse_id' => Warehouses::first()->id,
            'common_commentary_after' => 'Старый комментарий'
        ]);

        // Проверяем, что изначально логов нет
        $this->assertEquals(0, ProductLog::where('product_id', $product->id)->count());

        // Обновляем комментарий
        $response = $this->put(route('products.update', $product), [
            'company_id' => $product->company_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'status_id' => $product->status_id,
            'common_commentary_after' => 'Новый комментарий'
        ]);

        // Проверяем, что лог создался
        $this->assertEquals(1, ProductLog::where('product_id', $product->id)->count());

        $log = ProductLog::where('product_id', $product->id)->first();
        
        // Проверяем содержимое лога
        $this->assertEquals('Системный', $log->type->name);
        $this->assertNull($log->user_id); // От имени системы
        $this->assertStringContainsString('Изменен Общий комментарий после осмотра', $log->log);
        $this->assertStringContainsString('Старый комментарий', $log->log);
        $this->assertStringContainsString('Новый комментарий', $log->log);
    }

    public function test_commentary_log_creation_on_ajax_update()
    {
        // Создаем товар
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => Company::first()->id,
            'category_id' => ProductCategories::first()->id,
            'status_id' => ProductStatus::first()->id,
            'warehouse_id' => Warehouses::first()->id,
            'common_commentary_after' => 'Старый комментарий'
        ]);

        // Проверяем, что изначально логов нет
        $this->assertEquals(0, ProductLog::where('product_id', $product->id)->count());

        // Обновляем комментарий через AJAX
        $response = $this->post(route('products.updateComment', $product), [
            'field' => 'common_commentary_after',
            'value' => 'Новый комментарий'
        ]);

        // Проверяем, что лог создался
        $this->assertEquals(1, ProductLog::where('product_id', $product->id)->count());

        $log = ProductLog::where('product_id', $product->id)->first();
        
        // Проверяем содержимое лога
        $this->assertEquals('Системный', $log->type->name);
        $this->assertNull($log->user_id); // От имени системы
        $this->assertStringContainsString('Изменен Общий комментарий после осмотра', $log->log);
        $this->assertStringContainsString('Старый комментарий', $log->log);
        $this->assertStringContainsString('Новый комментарий', $log->log);
    }

    public function test_no_log_creation_when_commentary_not_changed()
    {
        // Создаем товар
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => Company::first()->id,
            'category_id' => ProductCategories::first()->id,
            'status_id' => ProductStatus::first()->id,
            'warehouse_id' => Warehouses::first()->id,
            'common_commentary_after' => 'Тестовый комментарий'
        ]);

        // Обновляем товар без изменения комментария
        $response = $this->put(route('products.update', $product), [
            'company_id' => $product->company_id,
            'category_id' => $product->category_id,
            'name' => 'Новое название',
            'status_id' => $product->status_id,
            'common_commentary_after' => 'Тестовый комментарий' // То же значение
        ]);

        // Проверяем, что лог не создался
        $this->assertEquals(0, ProductLog::where('product_id', $product->id)->count());
    }

    public function test_log_creation_with_empty_commentary()
    {
        // Создаем товар с пустым комментарием
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => Company::first()->id,
            'category_id' => ProductCategories::first()->id,
            'status_id' => ProductStatus::first()->id,
            'warehouse_id' => Warehouses::first()->id,
            'common_commentary_after' => null
        ]);

        // Обновляем комментарий на заполненный
        $response = $this->put(route('products.update', $product), [
            'company_id' => $product->company_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'status_id' => $product->status_id,
            'common_commentary_after' => 'Новый комментарий'
        ]);

        // Проверяем, что лог создался
        $this->assertEquals(1, ProductLog::where('product_id', $product->id)->count());

        $log = ProductLog::where('product_id', $product->id)->first();
        
        // Проверяем, что в логе упоминается "пустой комментарий"
        $this->assertStringContainsString('пустой комментарий', $log->log);
        $this->assertStringContainsString('Новый комментарий', $log->log);
    }

    public function test_log_creation_when_commentary_becomes_empty()
    {
        // Создаем товар с комментарием
        $product = Product::create([
            'name' => 'Тестовый товар',
            'company_id' => Company::first()->id,
            'category_id' => ProductCategories::first()->id,
            'status_id' => ProductStatus::first()->id,
            'warehouse_id' => Warehouses::first()->id,
            'common_commentary_after' => 'Старый комментарий'
        ]);

        // Обновляем комментарий на пустой
        $response = $this->put(route('products.update', $product), [
            'company_id' => $product->company_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'status_id' => $product->status_id,
            'common_commentary_after' => null
        ]);

        // Проверяем, что лог создался
        $this->assertEquals(1, ProductLog::where('product_id', $product->id)->count());

        $log = ProductLog::where('product_id', $product->id)->first();
        
        // Проверяем, что в логе упоминается "пустой комментарий"
        $this->assertStringContainsString('Старый комментарий', $log->log);
        $this->assertStringContainsString('пустой комментарий', $log->log);
    }
} 