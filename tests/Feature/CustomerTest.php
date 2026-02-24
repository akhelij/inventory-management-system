<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    #[Test]
    public function unauthenticated_user_cannot_access(): void
    {
        $this->get('customers/')
            ->assertRedirect('login/');
    }

    #[Test]
    public function logged_user_has_access_to_url(): void
    {
        $this->withoutExceptionHandling();

        $this->createCustomer();
        $this->assertDatabaseCount('customers', 1)
            ->assertDatabaseHas('customers', ['name' => 'Customer 1']);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('customers/')
            ->assertOk()
            ->assertViewIs('customers.index');
    }

    #[Test]
    public function user_can_use_create_view(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('customers/create')
            ->assertViewIs('customers.create');
    }

    #[Test]
    public function user_can_delete_customer(): void
    {
        $this->withoutExceptionHandling();

        $customer = $this->createCustomer();

        $this->assertDatabaseHas('customers', ['name' => 'Customer 1']);
        $this->assertDatabaseCount('customers', 1);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->delete('/customers/'.$customer->id);

        $this->assertDatabaseCount('customers', 0);
    }

    #[Test]
    public function user_can_see_show_view(): void
    {
        $user = $this->createUser();
        $customer = $this->createCustomer();

        $this->actingAs($user)
            ->get('customers/'.$customer->id)
            ->assertOk()
            ->assertViewIs('customers.show');
    }

    #[Test]
    public function user_can_see_edit_view(): void
    {
        $user = $this->createUser();
        $customer = $this->createCustomer();

        $this->actingAs($user)
            ->get('customers/'.$customer->id.'/edit')
            ->assertOk()
            ->assertViewIs('customers.edit');
    }
}
