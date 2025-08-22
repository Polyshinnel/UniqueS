<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Company;
use App\Models\ProductCategories;
use App\Models\ProductStatus;
use App\Models\Warehouses;
use App\Models\LogType;
use App\Models\ProductLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCharacteristicsLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_characteristics_update_creates_system_log()
    {
        // Создаем необходимые данные
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь'
        ]);

        $company = Company::factory()->create([
            'owner_user_id' => $user->id
        ]);

        $category = ProductCategories::factory()->create();
        $status = ProductStatus::factory()->create();
        $warehouse = Warehouses::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'warehouse_id' => $warehouse->id,
            'main_chars' => 'Старые характеристики',
            'complectation' => 'Старая комплектация',
            'mark' => 'Старая оценка'
        ]);

        // Создаем тип лога "Системный"
        $systemLogType = LogType::create([
            'name' => 'Системный',
            'color' => '#6c757d'
        ]);

        // Аутентифицируем пользователя
        $this->actingAs($user);

        // Выполняем запрос на обновление характеристик
        $response = $this->patchJson("/product/{$product->id}/characteristics", [
            'main_chars' => 'Новые характеристики',
            'complectation' => 'Новая комплектация',
            'mark' => 'Новая оценка'
        ]);

        // Проверяем успешность ответа
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Характеристики товара успешно обновлены'
                ]);

        // Проверяем, что товар обновился
        $product->refresh();
        $this->assertEquals('Новые характеристики', $product->main_chars);
        $this->assertEquals('Новая комплектация', $product->complectation);
        $this->assertEquals('Новая оценка', $product->mark);

        // Проверяем, что создалась запись в логах
        $log = ProductLog::where('product_id', $product->id)->first();
        
        $this->assertNotNull($log);
        $this->assertEquals($systemLogType->id, $log->type_id);
        $this->assertNull($log->user_id); // От имени системы
        $this->assertEquals('Пользователь Тестовый Пользователь изменил блок Характеристик товара', $log->log);
    }

    public function test_characteristics_update_without_user_creates_system_log()
    {
        // Создаем необходимые данные без пользователя
        $company = Company::factory()->create();

        $category = ProductCategories::factory()->create();
        $status = ProductStatus::factory()->create();
        $warehouse = Warehouses::factory()->create();

        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'warehouse_id' => $warehouse->id,
            'main_chars' => 'Старые характеристики'
        ]);

        // Создаем тип лога "Системный"
        $systemLogType = LogType::create([
            'name' => 'Системный',
            'color' => '#6c757d'
        ]);

        // Выполняем запрос без аутентификации
        $response = $this->patchJson("/product/{$product->id}/characteristics", [
            'main_chars' => 'Новые характеристики'
        ]);

        // Проверяем успешность ответа
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Характеристики товара успешно обновлены'
                ]);

        // Проверяем, что создалась запись в логах
        $log = ProductLog::where('product_id', $product->id)->first();
        
        $this->assertNotNull($log);
        $this->assertEquals($systemLogType->id, $log->type_id);
        $this->assertNull($log->user_id); // От имени системы
        $this->assertEquals('Пользователь Неизвестный пользователь изменил блок Характеристик товара', $log->log);
    }
}
