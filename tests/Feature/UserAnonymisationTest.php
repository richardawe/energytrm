<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Party;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Trade;
use App\Models\Uom;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAnonymisationTest extends TestCase
{
    use RefreshDatabase;

    private function tradePayload(array $overrides = []): array
    {
        return array_merge([
            'trade_date'      => now()->toDateString(),
            'buy_sell'        => 'Buy',
            'start_date'      => now()->toDateString(),
            'end_date'        => now()->addMonth()->toDateString(),
            'internal_bu_id'  => Party::where('internal_external', 'Internal')->value('id'),
            'portfolio_id'    => Portfolio::value('id'),
            'counterparty_id' => Party::where('internal_external', 'External')->value('id'),
            'product_id'      => Product::value('id'),
            'quantity'        => 1000,
            'volume_type'     => 'Fixed',
            'uom_id'          => Uom::value('id'),
            'fixed_float'     => 'Fixed',
            'fixed_price'     => 50,
            'currency_id'     => Currency::value('id'),
        ], $overrides);
    }

    public function test_pseudonym_hides_real_name_from_other_traders(): void
    {
        $alice = User::factory()->create(['name' => 'Alice Real', 'role' => 'trader']);
        $bob   = User::factory()->create(['name' => 'Bob Real', 'role' => 'trader']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($bob);
        $this->assertSame($alice->pseudonym(), $alice->display_name);
        $this->assertStringNotContainsString('Alice', $alice->display_name);
        $this->assertSame('Bob Real', $bob->display_name);

        $this->actingAs($admin);
        $this->assertSame('Alice Real', $alice->display_name);
    }

    public function test_trade_page_does_not_show_other_traders_real_name(): void
    {
        $this->seed(MasterDataSeeder::class);
        $alice = User::factory()->create(['name' => 'Alice Realname', 'role' => 'trader']);
        $bob   = User::factory()->create(['name' => 'Bob Realname', 'role' => 'trader']);

        $this->actingAs($alice)->post(route('trades.store'), $this->tradePayload())->assertRedirect();
        $trade = Trade::firstOrFail();

        $this->actingAs($bob)->get(route('trades.show', $trade))
            ->assertOk()
            ->assertDontSee('Alice Realname')
            ->assertSee($alice->pseudonym());
    }

    public function test_trade_form_does_not_list_other_users_for_traders(): void
    {
        $this->seed(MasterDataSeeder::class);
        User::factory()->create(['name' => 'Someone Else', 'role' => 'trader']);
        $trader = User::factory()->create(['role' => 'trader']);

        $this->actingAs($trader)->get(route('trades.create'))
            ->assertOk()
            ->assertDontSee('Someone Else')
            ->assertDontSee('name="trader_id"', false);
    }

    public function test_trader_cannot_book_trade_under_someone_elses_name(): void
    {
        $this->seed(MasterDataSeeder::class);
        $victim = User::factory()->create(['role' => 'trader']);
        $trader = User::factory()->create(['role' => 'trader']);

        $this->actingAs($trader)
            ->post(route('trades.store'), $this->tradePayload(['trader_id' => $victim->id]))
            ->assertRedirect();

        $this->assertSame($trader->id, Trade::firstOrFail()->trader_id);
    }

    public function test_trader_cannot_reassign_trade_on_amendment(): void
    {
        $this->seed(MasterDataSeeder::class);
        $victim = User::factory()->create(['role' => 'trader']);
        $trader = User::factory()->create(['role' => 'trader']);

        $this->actingAs($trader)->post(route('trades.store'), $this->tradePayload());
        $trade = Trade::firstOrFail();

        $this->actingAs($trader)
            ->put(route('trades.update', $trade), $this->tradePayload(['trader_id' => $victim->id]))
            ->assertRedirect();

        $this->assertSame($trader->id, $trade->fresh()->trader_id);
    }

    public function test_admin_can_book_on_behalf_of_a_trader(): void
    {
        $this->seed(MasterDataSeeder::class);
        $trader = User::factory()->create(['role' => 'trader']);
        $admin  = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('trades.store'), $this->tradePayload(['trader_id' => $trader->id]))
            ->assertRedirect();

        $this->assertSame($trader->id, Trade::firstOrFail()->trader_id);
    }
}
