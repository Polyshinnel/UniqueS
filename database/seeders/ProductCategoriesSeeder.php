<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategories;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем таблицу
        ProductCategories::truncate();

        // Создаем корневые категории
        $metalworking = ProductCategories::create([
            'name' => 'Металлообработка',
            'parent_id' => 0,
            'active' => true
        ]);

        $woodworking = ProductCategories::create([
            'name' => 'Деревообработка',
            'parent_id' => 0,
            'active' => true
        ]);

        $electronics = ProductCategories::create([
            'name' => 'Электроника',
            'parent_id' => 0,
            'active' => true
        ]);

        // Подкатегории для металлообработки
        $lathes = ProductCategories::create([
            'name' => 'Токарные станки',
            'parent_id' => $metalworking->id,
            'active' => true
        ]);

        $milling = ProductCategories::create([
            'name' => 'Фрезерные станки',
            'parent_id' => $metalworking->id,
            'active' => true
        ]);

        $grinding = ProductCategories::create([
            'name' => 'Шлифовальные станки',
            'parent_id' => $metalworking->id,
            'active' => true
        ]);

        // Подкатегории для токарных станков
        ProductCategories::create([
            'name' => 'Токарно-винторезные станки',
            'parent_id' => $lathes->id,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Токарно-карусельные станки',
            'parent_id' => $lathes->id,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Токарно-револьверные станки',
            'parent_id' => $lathes->id,
            'active' => true
        ]);

        // Подкатегории для фрезерных станков
        ProductCategories::create([
            'name' => 'Горизонтально-фрезерные станки',
            'parent_id' => $milling->id,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Вертикально-фрезерные станки',
            'parent_id' => $milling->id,
            'active' => true
        ]);

        // Подкатегории для деревообработки
        $saws = ProductCategories::create([
            'name' => 'Пилы',
            'parent_id' => $woodworking->id,
            'active' => true
        ]);

        $planers = ProductCategories::create([
            'name' => 'Рейсмусы',
            'parent_id' => $woodworking->id,
            'active' => true
        ]);

        // Подкатегории для пил
        ProductCategories::create([
            'name' => 'Ленточные пилы',
            'parent_id' => $saws->id,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Дисковые пилы',
            'parent_id' => $saws->id,
            'active' => true
        ]);

        // Подкатегории для электроники
        ProductCategories::create([
            'name' => 'Компьютеры',
            'parent_id' => $electronics->id,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Принтеры',
            'parent_id' => $electronics->id,
            'active' => true
        ]);

        // Категории без подкатегорий (должны быть доступны для выбора)
        ProductCategories::create([
            'name' => 'Шлифовальные станки по дереву',
            'parent_id' => 0,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Сверлильные станки',
            'parent_id' => 0,
            'active' => true
        ]);

        ProductCategories::create([
            'name' => 'Сварочное оборудование',
            'parent_id' => 0,
            'active' => true
        ]);
    }
} 