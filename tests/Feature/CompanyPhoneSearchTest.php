<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Regions;
use App\Models\Sources;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPhoneSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create(['role_id' => 2, 'active' => true]);
        $this->company = Company::factory()->create([
            'name' => 'Поставщик оборудования',
            'sku' => 'SUPPLIER-ABC',
            'source_id' => Sources::create(['name' => 'Тестовый источник'])->id,
            'region_id' => Regions::create(['name' => 'Тестовый регион', 'active' => true])->id,
            'regional_user_id' => $this->manager->id,
            'owner_user_id' => $this->manager->id,
            'company_status_id' => 1,
        ]);

        $mainContact = $this->company->contacts()->create([
            'name' => 'Основной контакт',
            'main_contact' => true,
        ]);
        $mainContact->phones()->create(['phone' => '+7 (812) 600-20-30']);

        $otherContact = $this->company->contacts()->create([
            'name' => 'Дополнительный контакт',
            'main_contact' => false,
        ]);
        $otherContact->phones()->createMany([
            ['phone' => '+7 (900) 111-22-33'],
            ['phone' => '+7 (921) 345-67-89'],
        ]);

        $this->actingAs($this->manager);
    }

    /** @dataProvider searchQueries */
    public function test_searches_all_contact_phones_and_preserves_text_search(string $search, bool $matches): void
    {
        $this->get('/company?'.http_build_query(['search' => $search]))
            ->assertOk()
            ->assertViewHas('companies', function ($companies) use ($matches) {
                return $companies->pluck('id')->all() === ($matches ? [$this->company->id] : []);
            });
    }

    public static function searchQueries(): array
    {
        return [
            'main contact' => ['812', true],
            'additional contact' => ['111', true],
            'second phone of additional contact' => ['345', true],
            'across formatting separators' => ['567', true],
            'number ending' => ['789', true],
            'formatted fragment' => ['45-67', true],
            'full formatted number' => ['+7 (921) 345-67-89', true],
            'full unformatted number' => ['79213456789', true],
            'two digits' => ['34', false],
            'formatting does not count towards minimum' => ['3-4', false],
            'unmatched fragment' => ['999', false],
            'digits must be consecutive' => ['357', false],
            'text containing digits is not a phone' => ['abc345', false],
            'company name' => ['оборудования', true],
            'company sku' => ['SUPPLIER-ABC', true],
        ];
    }

    public function test_phone_search_respects_company_access_and_filters_without_duplicates(): void
    {
        $this->company->contacts()->first()->phones()->create(['phone' => '345']);

        $otherOwner = User::factory()->create(['role_id' => 2, 'active' => true]);
        $hiddenCompany = $this->company->replicate();
        $hiddenCompany->sku = 'HIDDEN-ABC';
        $hiddenCompany->owner_user_id = $otherOwner->id;
        $hiddenCompany->save();
        $hiddenCompany->contacts()->create(['name' => 'Чужой контакт'])
            ->phones()->create(['phone' => '345']);

        $this->get('/company?search=345')
            ->assertOk()
            ->assertViewHas('companies', fn ($companies) => $companies->pluck('id')->all() === [$this->company->id]);

        $this->get('/company?search=345&status_id=2')
            ->assertOk()
            ->assertViewHas('companies', fn ($companies) => $companies->isEmpty());
    }
}
