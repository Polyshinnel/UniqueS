<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyContacts;
use App\Models\CompanyContactsPhones;
use App\Models\Sources;
use App\Models\User;
use App\Models\Warehouses;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with([
            'contacts' => function($query) {
                $query->where('main_contact', true);
            },
            'contacts.phones',
            'addresses' => function($query) {
                $query->where('main_address', true);
            },
            'regional',
            'owner',
            'status'
        ])->get();

        $warehouses = Warehouses::all();
        $sources = Sources::all();
        $regionals = User::where('role_id', 3)
            ->where('active', true)
            ->get();
        $regions = \App\Models\Regions::all();

        return view('Company.CompanyPage', compact('companies', 'warehouses', 'sources', 'regionals', 'regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'source_id' => 'required|exists:sources,id',
            'region_id' => 'required|exists:users,id',
            'region' => 'required|exists:regions,id',
            'inn' => 'nullable|string',
            'name' => 'required|string',
            'addresses' => 'required|array',
            'addresses.*' => 'required|string',
            'main_address' => 'array',
            'contact_name' => 'required|array',
            'contact_name.*' => 'required|string',
            'phones' => 'required|array',
            'phones.*' => 'required|array',
            'phones.*.*' => 'required|string',
            'position' => 'required|array',
            'position.*' => 'required|string',
            'main_contact' => 'array',
            'email' => 'required|email',
            'site' => 'required|url',
            'common_info' => 'required|string',
        ]);

        try {
            $company = Company::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'inn' => $validated['inn'],
                'source_id' => $validated['source_id'],
                'region_id' => $validated['region'],
                'regional_user_id' => $validated['region_id'],
                'owner_user_id' => 1,
                'email' => $validated['email'],
                'site' => $validated['site'],
                'common_info' => $validated['common_info'],
                'company_status_id' => 1,
            ]);

            // Создание связи компании со складом
            $company->warehouses()->attach($validated['warehouse_id']);

            // Сохранение адресов
            foreach ($validated['addresses'] as $index => $address) {
                CompanyAddress::create([
                    'company_id' => $company->id,
                    'address' => $address,
                    'main_address' => isset($validated['main_address'][$index]) && $validated['main_address'][$index],
                ]);
            }

            // Сохранение контактных лиц
            foreach ($validated['contact_name'] as $index => $contactName) {
                $contact = CompanyContacts::create([
                    'company_id' => $company->id,
                    'name' => $contactName,
                    'position' => $validated['position'][$index],
                    'email' => $validated['email'],
                    'main_contact' => isset($validated['main_contact'][$index]) && $validated['main_contact'][$index],
                ]);

                // Сохранение телефонов для каждого контакта
                if (isset($validated['phones'][$index])) {
                    foreach ($validated['phones'][$index] as $phone) {
                        CompanyContactsPhones::create([
                            'company_contact_id' => $contact->id,
                            'phone' => $phone,
                        ]);
                    }
                }
            }

            return redirect()->route('companies.show', $company)
                ->with('success', 'Компания успешно создана');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании компании: ' . $e->getMessage()]);
        }
    }

    public function show(Company $company)
    {
        $company->load([
            'contacts' => function($query) {
                $query->with('phones');
            },
            'addresses',
            'regional',
            'owner',
            'status',
            'region',
            'source'
        ]);

        return view('Company.CompanyShowPage', compact('company'));
    }
}
