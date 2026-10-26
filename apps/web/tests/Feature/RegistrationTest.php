<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_owner_and_workspace_atomically(): void
    {
        $this->post('/register', [
            'name' => 'Budi',
            'organization_name' => 'Sekolah Uji',
            'email' => 'budi@example.test',
            'password' => 'very-secure-pass-123',
            'password_confirmation' => 'very-secure-pass-123',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $org = Organization::firstOrFail();
        $this->assertSame(1, $org->users()->whereKey(User::firstOrFail()->id)->count());
        $this->assertSame('owner', $org->users()->first()->pivot->role);
    }
}
