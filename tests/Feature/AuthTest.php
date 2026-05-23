<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_factory_state_sets_is_admin_true(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertTrue($user->is_admin);
    }
}
