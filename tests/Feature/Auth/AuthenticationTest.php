<?php

namespace Tests\Feature\Auth;

use App\Providers\AppServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    #[Test]
    public function login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    #[Test]
    public function authenticated_user_is_redirected_from_login(): void
    {
        $this->withoutExceptionHandling();

        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(AppServiceProvider::HOME);
        $response->assertRedirect('/dashboard');
    }

    #[Test]
    public function users_cannot_authenticate_with_invalid_password(): void
    {
        $user = $this->createUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
