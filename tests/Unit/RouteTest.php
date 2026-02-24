<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RouteTest extends TestCase
{
    #[Test]
    public function login_route(): void
    {
        $this->get(route('login'))->assertOk();
    }

    #[Test]
    public function register_route(): void
    {
        $this->get(route('register'))->assertOk();
    }

    #[Test]
    public function dashboard_route_redirects_unauthorized_user_to_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect('/login');
    }
}
