<?php

namespace Tests\Feature;

use App\Models\Regions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyCreateRegionsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_only_see_their_assigned_regions()
    {
        // Создаем регионы
        $region1 = Regions::factory()->create(['name' => 'Москва', 'active' => true]);
        $region2 = Regions::factory()->create(['name' => 'Санкт-Петербург', 'active' => true]);
        $region3 = Regions::factory()->create(['name' => 'Новосибирск', 'active' => true]);

        // Создаем пользователя
        $user = User::factory()->create(['role_id' => 2]); // Обычный пользователь

        // Назначаем пользователю только первые два региона
        $user->regions()->attach([$region1->id, $region2->id]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Переходим на страницу создания компании
        $response = $this->get(route('companies.create'));

        $response->assertStatus(200);

        // Проверяем, что в представлении есть только доступные регионы
        $response->assertSee('Москва');
        $response->assertSee('Санкт-Петербург');
        $response->assertDontSee('Новосибирск');
    }

    public function test_admin_can_see_all_regions()
    {
        // Создаем регионы
        $region1 = Regions::factory()->create(['name' => 'Москва', 'active' => true]);
        $region2 = Regions::factory()->create(['name' => 'Санкт-Петербург', 'active' => true]);
        $region3 = Regions::factory()->create(['name' => 'Новосибирск', 'active' => true]);

        // Создаем администратора
        $admin = User::factory()->create(['role_id' => 1]); // Администратор

        // Авторизуем администратора
        $this->actingAs($admin);

        // Переходим на страницу создания компании
        $response = $this->get(route('companies.create'));

        $response->assertStatus(200);

        // Проверяем, что администратор видит все регионы
        $response->assertSee('Москва');
        $response->assertSee('Санкт-Петербург');
        $response->assertSee('Новосибирск');
    }

    public function test_user_cannot_create_company_with_inaccessible_region()
    {
        // Создаем регионы
        $accessibleRegion = Regions::factory()->create(['name' => 'Москва', 'active' => true]);
        $inaccessibleRegion = Regions::factory()->create(['name' => 'Санкт-Петербург', 'active' => true]);

        // Создаем пользователя
        $user = User::factory()->create(['role_id' => 2]);

        // Назначаем пользователю только один регион
        $user->regions()->attach([$accessibleRegion->id]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Пытаемся создать компанию с недоступным регионом
        $response = $this->post(route('companies.store'), [
            'sku' => 'TEST-001',
            'warehouse_id' => 1,
            'source_id' => 1,
            'region_id' => 1,
            'region' => $inaccessibleRegion->id, // Недоступный регион
            'name' => 'Test Company',
            'addresses' => ['Test Address'],
            'contact_name' => ['Test Contact'],
            'phones' => [['+7 999 999 99 99']],
            'position' => ['Manager'],
            'email' => 'test@example.com',
            'site' => 'https://example.com',
            'common_info' => 'Test info',
        ]);

        // Проверяем, что получили ошибку
        $response->assertSessionHasErrors(['region']);
        $response->assertSessionHasErrors(['region' => 'Выбранный регион недоступен для вашего пользователя']);
    }

    public function test_get_regionals_by_region()
    {
        // Создаем регионы
        $region1 = Regions::factory()->create(['name' => 'Москва', 'active' => true]);
        $region2 = Regions::factory()->create(['name' => 'Санкт-Петербург', 'active' => true]);

        // Создаем региональных представителей
        $regional1 = User::factory()->create([
            'name' => 'Иван Иванов',
            'role_id' => 3,
            'active' => true
        ]);
        $regional2 = User::factory()->create([
            'name' => 'Петр Петров',
            'role_id' => 3,
            'active' => true
        ]);
        $regional3 = User::factory()->create([
            'name' => 'Сидор Сидоров',
            'role_id' => 3,
            'active' => true
        ]);

        // Назначаем региональных представителей к регионам
        $regional1->regions()->attach([$region1->id, $region2->id]); // Работает в обоих регионах
        $regional2->regions()->attach([$region1->id]); // Работает только в Москве
        $regional3->regions()->attach([$region2->id]); // Работает только в СПб

        // Создаем пользователя
        $user = User::factory()->create(['role_id' => 2]);
        $user->regions()->attach([$region1->id, $region2->id]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Тестируем получение региональных представителей для Москвы
        $response = $this->get(route('companies.regionals-by-region', $region1->id));
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(2, $data); // Должно быть 2 регионала для Москвы
        $this->assertContains('Иван Иванов', collect($data)->pluck('name')->toArray());
        $this->assertContains('Петр Петров', collect($data)->pluck('name')->toArray());
        $this->assertNotContains('Сидор Сидоров', collect($data)->pluck('name')->toArray());

        // Тестируем получение региональных представителей для СПб
        $response = $this->get(route('companies.regionals-by-region', $region2->id));
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(2, $data); // Должно быть 2 регионала для СПб
        $this->assertContains('Иван Иванов', collect($data)->pluck('name')->toArray());
        $this->assertContains('Сидор Сидоров', collect($data)->pluck('name')->toArray());
        $this->assertNotContains('Петр Петров', collect($data)->pluck('name')->toArray());
    }

    public function test_validate_regional_belongs_to_selected_region()
    {
        // Создаем регионы
        $region1 = Regions::factory()->create(['name' => 'Москва', 'active' => true]);
        $region2 = Regions::factory()->create(['name' => 'Санкт-Петербург', 'active' => true]);

        // Создаем региональных представителей
        $regional1 = User::factory()->create([
            'name' => 'Иван Иванов',
            'role_id' => 3,
            'active' => true
        ]);
        $regional2 = User::factory()->create([
            'name' => 'Петр Петров',
            'role_id' => 3,
            'active' => true
        ]);

        // Назначаем региональных представителей к разным регионам
        $regional1->regions()->attach([$region1->id]);
        $regional2->regions()->attach([$region2->id]);

        // Создаем пользователя
        $user = User::factory()->create(['role_id' => 2]);
        $user->regions()->attach([$region1->id, $region2->id]);

        // Авторизуем пользователя
        $this->actingAs($user);

        // Пытаемся создать компанию с региональным представителем, который не прикреплен к выбранному региону
        $response = $this->post(route('companies.store'), [
            'sku' => 'TEST-001',
            'warehouse_id' => 1,
            'source_id' => 1,
            'region_id' => $regional2->id, // Региональный представитель из СПб
            'region' => $region1->id, // Но регион Москва
            'name' => 'Test Company',
            'addresses' => ['Test Address'],
            'contact_name' => ['Test Contact'],
            'phones' => [['+7 999 999 99 99']],
            'position' => ['Manager'],
            'email' => 'test@example.com',
            'site' => 'https://example.com',
            'common_info' => 'Test info',
        ]);

        // Проверяем, что получили ошибку
        $response->assertSessionHasErrors(['region_id']);
        $response->assertSessionHasErrors(['region_id' => 'Выбранный региональный представитель не прикреплен к данному региону']);
    }
} 