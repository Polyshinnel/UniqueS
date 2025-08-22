<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\LogType;
use App\Models\AdvLog;
use App\Models\ProductCategories;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdvertisementBlockLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_technical_characteristics_update_creates_system_log()
    {
        // Создаем пользователя
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь'
        ]);

        // Создаем необходимые данные
        $category = ProductCategories::create(['name' => 'Тестовая категория']);
        $product = Product::create([
            'name' => 'Тестовый продукт',
            'category_id' => $category->id,
            'owner_id' => $user->id
        ]);

        // Создаем объявление
        $advertisement = Advertisement::create([
            'title' => 'Тестовое объявление',
            'category_id' => $category->id,
            'product_id' => $product->id,
            'created_by' => $user->id,
            'technical_characteristics' => 'Старые характеристики'
        ]);

        // Выполняем запрос на обновление
        $response = $this->actingAs($user)
            ->patchJson("/advertisements/{$advertisement->id}/comment", [
                'field' => 'technical_characteristics',
                'value' => 'Новые характеристики'
            ]);

        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что объявление обновилось
        $advertisement->refresh();
        $this->assertEquals('Новые характеристики', $advertisement->technical_characteristics);

        // Проверяем, что создался системный лог
        $systemLogType = LogType::where('name', 'Системный')->first();
        $this->assertNotNull($systemLogType);

        $log = AdvLog::where('advertisement_id', $advertisement->id)
            ->where('type_id', $systemLogType->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals("Пользователь Тестовый Пользователь изменил Технические характеристики", $log->log);
        $this->assertNull($log->user_id); // От имени системы
    }

    public function test_main_info_update_creates_system_log()
    {
        // Создаем пользователя
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь'
        ]);

        // Создаем необходимые данные
        $category = ProductCategories::create(['name' => 'Тестовая категория']);
        $product = Product::create([
            'name' => 'Тестовый продукт',
            'category_id' => $category->id,
            'owner_id' => $user->id
        ]);

        // Создаем объявление
        $advertisement = Advertisement::create([
            'title' => 'Тестовое объявление',
            'category_id' => $category->id,
            'product_id' => $product->id,
            'created_by' => $user->id,
            'main_info' => 'Старая информация'
        ]);

        // Выполняем запрос на обновление
        $response = $this->actingAs($user)
            ->patchJson("/advertisements/{$advertisement->id}/comment", [
                'field' => 'main_info',
                'value' => 'Новая информация'
            ]);

        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что объявление обновилось
        $advertisement->refresh();
        $this->assertEquals('Новая информация', $advertisement->main_info);

        // Проверяем, что создался системный лог
        $systemLogType = LogType::where('name', 'Системный')->first();
        $this->assertNotNull($systemLogType);

        $log = AdvLog::where('advertisement_id', $advertisement->id)
            ->where('type_id', $systemLogType->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals("Пользователь Тестовый Пользователь изменил Основная информация", $log->log);
        $this->assertNull($log->user_id); // От имени системы
    }

    public function test_additional_info_update_creates_system_log()
    {
        // Создаем пользователя
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь'
        ]);

        // Создаем необходимые данные
        $category = ProductCategories::create(['name' => 'Тестовая категория']);
        $product = Product::create([
            'name' => 'Тестовый продукт',
            'category_id' => $category->id,
            'owner_id' => $user->id
        ]);

        // Создаем объявление
        $advertisement = Advertisement::create([
            'title' => 'Тестовое объявление',
            'category_id' => $category->id,
            'product_id' => $product->id,
            'created_by' => $user->id,
            'additional_info' => 'Старая дополнительная информация'
        ]);

        // Выполняем запрос на обновление
        $response = $this->actingAs($user)
            ->patchJson("/advertisements/{$advertisement->id}/comment", [
                'field' => 'additional_info',
                'value' => 'Новая дополнительная информация'
            ]);

        // Проверяем успешность запроса
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что объявление обновилось
        $advertisement->refresh();
        $this->assertEquals('Новая дополнительная информация', $advertisement->additional_info);

        // Проверяем, что создался системный лог для дополнительной информации
        $systemLogType = LogType::where('name', 'Системный')->first();
        $this->assertNotNull($systemLogType);

        $log = AdvLog::where('advertisement_id', $advertisement->id)
            ->where('type_id', $systemLogType->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals("Пользователь Тестовый Пользователь изменил Дополнительная информация", $log->log);
        $this->assertNull($log->user_id); // От имени системы
    }
}
