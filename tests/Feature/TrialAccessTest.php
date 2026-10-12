<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\TrialController;
use App\Http\Middleware\EnsureTrialActive;
use App\Models\SecurityGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrialAccessTest extends TestCase
{
    use RefreshDatabase;

    private string $videoDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Fixture video so the player and stream routes have something to serve
        $this->videoDir = sys_get_temp_dir() . '/etrm-videos-' . uniqid();
        mkdir($this->videoDir);
        file_put_contents("{$this->videoDir}/introduction-voiced.mp4", str_repeat("\0", 1024));
        config(['etrm.training_video_dir' => $this->videoDir]);
    }

    protected function tearDown(): void
    {
        @unlink("{$this->videoDir}/introduction-voiced.mp4");
        @rmdir($this->videoDir);

        parent::tearDown();
    }

    private function trialUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role'             => 'trader',
            'trial_expires_at' => now()->addMinutes(User::TRIAL_MINUTES),
        ], $attributes));
        $user->securityGroups()->attach(SecurityGroup::where('name', User::TRIAL_GROUP)->value('id'));

        return $user;
    }

    public function test_trial_access_security_group_exists(): void
    {
        $this->assertDatabaseHas('security_groups', ['name' => User::TRIAL_GROUP, 'is_active' => true]);
    }

    public function test_starting_a_trial_creates_a_one_hour_account_and_logs_in(): void
    {
        $this->freezeTime();

        $this->post(route('trial.start'))
            ->assertRedirect(route('dashboard'))
            ->assertCookie(TrialController::COOKIE);

        $user = User::whereNotNull('trial_expires_at')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('trader', $user->role);
        $this->assertSame(now()->addHour()->getTimestamp(), $user->trial_expires_at->getTimestamp());
        $this->assertTrue($user->securityGroups()->where('name', User::TRIAL_GROUP)->exists());
    }

    public function test_same_browser_cannot_start_a_second_trial(): void
    {
        $this->withCookie(TrialController::COOKIE, '1')
            ->post(route('trial.start'))
            ->assertRedirect(route('register'));

        $this->assertSame(0, User::count());
    }

    public function test_trial_starts_are_limited_per_ip(): void
    {
        for ($i = 0; $i < TrialController::MAX_PER_IP_PER_DAY; $i++) {
            $this->post(route('trial.start'));
            $this->app['auth']->guard('web')->logout();
        }

        $this->post(route('trial.start'))->assertRedirect(route('register'));
        $this->assertSame(TrialController::MAX_PER_IP_PER_DAY, User::count());
    }

    public function test_active_trial_can_use_the_portal(): void
    {
        $this->actingAs($this->trialUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Trial:');
    }

    public function test_expired_trial_is_logged_out_and_sent_to_register(): void
    {
        $user = $this->trialUser();
        $this->travel(User::TRIAL_MINUTES + 1)->minutes();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('status', EnsureTrialActive::EXPIRED_MESSAGE);

        $this->assertGuest();
    }

    public function test_trial_user_cannot_export_reports(): void
    {
        $this->actingAs($this->trialUser())
            ->post(route('risk.reports.generate'), ['report_type' => 'pnl', 'reporting_date' => now()->toDateString()])
            ->assertForbidden();
    }

    public function test_trial_user_cannot_download_videos_and_sees_no_download_button(): void
    {
        $user = $this->trialUser();

        $this->actingAs($user)->get(route('training.videos.download', 'introduction'))->assertForbidden();
        $this->actingAs($user)->get(route('training.videos.show', 'introduction'))
            ->assertOk()
            ->assertDontSee(route('training.videos.download', 'introduction'))
            ->assertSee('controlsList="nodownload"', false);
    }

    public function test_registered_user_can_stream_and_download_videos(): void
    {
        $user = User::factory()->create(['role' => 'trader']);

        $this->actingAs($user)->get(route('training.videos.stream', 'introduction'))->assertOk();
        $this->actingAs($user)->get(route('training.videos.download', 'introduction'))->assertOk();
    }

    public function test_videos_are_not_reachable_without_login(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('videos'));
        $this->get(route('training.videos.stream', 'introduction'))->assertRedirect(route('login'));
    }

    public function test_trial_users_are_not_offered_as_traders(): void
    {
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->trialUser(['name' => 'Trial Person']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('trades.create'))->assertOk()->assertDontSee('Trial Person');
    }

    public function test_purge_removes_expired_trials_and_their_trades(): void
    {
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $expired = $this->trialUser();
        $active  = $this->trialUser();
        $regular = User::factory()->create(['role' => 'trader']);

        DB::table('reports')->insert([
            'report_type' => 'pnl', 'reporting_date' => now()->toDateString(), 'parameters' => '{}',
            'file_format' => 'csv', 'generated_by' => $expired->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($expired)->post(route('trades.store'), [
            'trade_date' => now()->toDateString(), 'buy_sell' => 'Buy',
            'start_date' => now()->toDateString(), 'end_date' => now()->addMonth()->toDateString(),
            'internal_bu_id'  => \App\Models\Party::where('internal_external', 'Internal')->value('id'),
            'portfolio_id'    => \App\Models\Portfolio::value('id'),
            'counterparty_id' => \App\Models\Party::where('internal_external', 'External')->value('id'),
            'product_id'      => \App\Models\Product::value('id'),
            'quantity' => 100, 'volume_type' => 'Fixed', 'uom_id' => \App\Models\Uom::value('id'),
            'fixed_float' => 'Fixed', 'fixed_price' => 10, 'currency_id' => \App\Models\Currency::value('id'),
        ])->assertRedirect(route('trades.index'));
        $this->assertDatabaseHas('trades', ['created_by' => $expired->id]);

        $this->travel(User::TRIAL_MINUTES + 25 * 60)->minutes();
        $active->update(['trial_expires_at' => now()->addHour()]);

        $this->artisan('etrm:purge-trials')->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelExists($active);
        $this->assertModelExists($regular);
        $this->assertDatabaseMissing('reports', ['generated_by' => $expired->id]);
        $this->assertDatabaseMissing('trades', ['created_by' => $expired->id]);
        $this->assertDatabaseMissing('audit_logs', ['user_id' => $expired->id]);
    }
}
