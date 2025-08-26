<?php

namespace App\Helpers;

use App\Models\Advertisement;
use App\Models\User;

class AdvertisementHelper
{
    /**
     * Проверяет, может ли пользователь видеть информацию о поставщике
     */
    public static function canViewSupplierInfo(Advertisement $advertisement, ?User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Администраторы могут видеть всю информацию
        if ($user->role && $user->role->name === 'Администратор') {
            return true;
        }

        // Проверяем, является ли пользователь владельцем объявления
        if ($advertisement->created_by === $user->id) {
            return true;
        }

        // Проверяем, является ли пользователь владельцем товара
        if ($advertisement->product && $advertisement->product->owner_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, может ли пользователь редактировать объявление
     */
    public static function canEditAdvertisement(Advertisement $advertisement, ?User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Администраторы могут редактировать все объявления
        if ($user->role && $user->role->name === 'Администратор') {
            return true;
        }

        // Проверяем, является ли пользователь владельцем объявления
        if ($advertisement->created_by === $user->id) {
            return true;
        }

        // Проверяем, является ли пользователь владельцем товара
        if ($advertisement->product && $advertisement->product->owner_id === $user->id) {
            return true;
        }

        return false;
    }
}
