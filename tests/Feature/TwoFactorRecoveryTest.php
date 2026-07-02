<?php

namespace Tests\Feature;

use App\Http\Livewire\TwoFactorRecovery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_id_1_can_regenerate_recovery_codes(): void
    {
        $admin = User::factory()->create(); // prvi kreirani korisnik -> id = 1
        $this->assertSame(1, $admin->id);
        $this->actingAs($admin);

        $target = $this->userWithTwoFactor();
        $oldCodes = $target->recoveryCodes();

        $component = Livewire::test(TwoFactorRecovery::class)
            ->call('confirmGenerateShowModal', $target->id)
            ->call('generateRecoveryCodes')
            ->assertSet('confirmingGenerationVisible', false)
            ->assertSet('generatedForName', $target->name);

        $this->assertCount(8, $component->get('recoveryCodes'));

        $newCodes = $target->fresh()->recoveryCodes();
        $this->assertCount(8, $newCodes);
        $this->assertCount(8, array_diff($oldCodes, $newCodes));
    }

    public function test_only_users_with_two_factor_enabled_are_listed(): void
    {
        $this->actingAs(User::factory()->create()); // id = 1 (admin)

        $enabled = $this->userWithTwoFactor();
        $disabled = User::factory()->create();

        Livewire::test(TwoFactorRecovery::class)
            ->assertSee($enabled->email)
            ->assertDontSee($disabled->email);
    }

    public function test_user_other_than_id_1_cannot_generate_codes(): void
    {
        $target = $this->userWithTwoFactor(); // id = 1
        $oldCodes = $target->recoveryCodes();

        $this->actingAs(User::factory()->create()); // id = 2 -> nije admin

        Livewire::test(TwoFactorRecovery::class)
            ->call('confirmGenerateShowModal', $target->id)
            ->call('generateRecoveryCodes');

        $this->assertSame($oldCodes, $target->fresh()->recoveryCodes());
    }

    private function userWithTwoFactor(): User
    {
        $user = User::factory()->create();
        app(EnableTwoFactorAuthentication::class)($user);

        return $user->fresh();
    }
}
