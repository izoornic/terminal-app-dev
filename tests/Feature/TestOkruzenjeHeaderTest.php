<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestOkruzenjeHeaderTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_LABEL = 'tracking-widest">TEST</span>';

    public function test_header_ima_test_oznaku_van_produkcije(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee(self::TEST_LABEL, false)
            ->assertSee('bg-amber-200 border-b-4 border-red-600', false);
    }

    public function test_header_nema_test_oznaku_u_produkciji(): void
    {
        $this->app['env'] = 'production';
        $this->actingAs(User::factory()->create());

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee(self::TEST_LABEL, false)
            ->assertDontSee('bg-amber-200', false)
            ->assertSee('bg-white border-b border-gray-100', false);
    }
}
