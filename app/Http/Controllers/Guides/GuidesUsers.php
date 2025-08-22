<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Models\RoleList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuidesUsers extends Controller
{
    public function index()
    {
        $users = User::with(['regions', 'role'])->get();
        $regions = Regions::where('active', true)->get();
        $roles = RoleList::all();

        return view('Guides.GuidesUsersPage', compact('users', 'regions', 'roles'));
    }

    /**
     * Очищает номер телефона от лишних символов и форматирует в формат +79030264456
     */
    private function cleanPhoneNumber($phone)
    {
        // Удаляем все символы кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Если номер начинается с 8, заменяем на 7
        if (strlen($phone) === 11 && $phone[0] === '8') {
            $phone = '7' . substr($phone, 1);
        }
        
        // Если номер начинается с 7 и имеет 11 цифр, добавляем +
        if (strlen($phone) === 11 && $phone[0] === '7') {
            return '+' . $phone;
        }
        
        // Если номер имеет 10 цифр и начинается с 9, добавляем +7
        if (strlen($phone) === 10 && $phone[0] === '9') {
            return '+7' . $phone;
        }
        
        // Если номер уже в правильном формате, возвращаем как есть
        if (strlen($phone) === 12 && $phone[0] === '+') {
            return $phone;
        }
        
        // В остальных случаях возвращаем как есть (будет отклонено валидацией)
        return $phone;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'role_id' => 'required|exists:role_lists,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'password' => 'required|string|min:8',
            'has_whatsapp' => 'boolean',
            'has_telegram' => 'boolean',
            'active' => 'boolean',
        ]);

        // Очищаем и форматируем номер телефона
        $cleanPhone = $this->cleanPhoneNumber($validated['phone']);
        
        // Дополнительная валидация телефона
        if (!preg_match('/^\+7[9]\d{9}$/', $cleanPhone)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['phone' => 'Номер телефона должен быть в формате +7(999)999-99-99 и начинаться с 9 после кода страны']);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $cleanPhone,
                'role_id' => $validated['role_id'],
                'has_whatsapp' => $request->has('has_whatsapp'),
                'has_telegram' => $request->has('has_telegram'),
                'active' => $request->has('active'),
                'password' => Hash::make($validated['password']),
            ]);

            // Создаем записи в таблице users_to_regions
            foreach ($validated['regions'] as $regionId) {
                DB::table('users_to_regions')->insert([
                    'user_id' => $user->id,
                    'region_id' => $regionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            
            // Возвращаем данные созданного пользователя для отображения в модальном окне
            return redirect()->route('users.index')->with([
                'success' => 'Сотрудник успешно добавлен',
                'created_user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $validated['password'] // Возвращаем оригинальный пароль
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при добавлении сотрудника');
        }
    }

    public function edit(User $user)
    {
        return response()->json($user->load(['regions', 'role']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role_id' => 'required|exists:role_lists,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'password' => 'nullable|string|min:8',
            'has_whatsapp' => 'boolean',
            'has_telegram' => 'boolean',
            'active' => 'boolean',
        ]);

        // Очищаем и форматируем номер телефона
        $cleanPhone = $this->cleanPhoneNumber($validated['phone']);
        
        // Дополнительная валидация телефона
        if (!preg_match('/^\+7[9]\d{9}$/', $cleanPhone)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['phone' => 'Номер телефона должен быть в формате +7(999)999-99-99 и начинаться с 9 после кода страны']);
        }

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $cleanPhone,
                'role_id' => $validated['role_id'],
                'has_whatsapp' => $request->has('has_whatsapp'),
                'has_telegram' => $request->has('has_telegram'),
                'active' => $request->has('active'),
            ];

            // Обновляем пароль только если он предоставлен
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            // Обновляем связи с регионами
            DB::table('users_to_regions')->where('user_id', $user->id)->delete();
            foreach ($validated['regions'] as $regionId) {
                DB::table('users_to_regions')->insert([
                    'user_id' => $user->id,
                    'region_id' => $regionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Сотрудник успешно обновлен');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при обновлении сотрудника');
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            // Удаляем связи с регионами
            DB::table('users_to_regions')->where('user_id', $user->id)->delete();
            
            // Удаляем пользователя
            $user->delete();

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Сотрудник успешно удален');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при удалении сотрудника');
        }
    }
}
