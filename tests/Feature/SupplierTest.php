<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    #[Test]
    public function unauthenticated_user_cannot_access(): void
    {
        $this->get('suppliers/')
            ->assertRedirect('login/');
    }

    #[Test]
    public function logged_user_has_access_to_url(): void
    {
        $this->withoutExceptionHandling();

        $this->createSupplier();
        $this->assertDatabaseCount('suppliers', 1)
            ->assertDatabaseHas('suppliers', ['name' => 'Thomann']);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('suppliers/')
            ->assertOk()
            ->assertViewIs('suppliers.index');
    }

    #[Test]
    public function user_can_use_create_view(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('suppliers/create')
            ->assertViewIs('suppliers.create');
    }

    #[Test]
    public function user_can_see_edit_view(): void
    {
        $user = $this->createUser();
        $supplier = $this->createSupplier();

        $this->actingAs($user)
            ->get('suppliers/'.$supplier->id.'/edit')
            ->assertOk()
            ->assertViewIs('suppliers.edit');
    }

    #[Test]
    public function user_can_see_show_view(): void
    {
        $user = $this->createUser();
        $supplier = $this->createSupplier();

        $this->actingAs($user)
            ->get('suppliers/'.$supplier->id)
            ->assertOk()
            ->assertViewIs('suppliers.show');
    }

    #[Test]
    public function user_can_delete_supplier(): void
    {
        $supplier = $this->createSupplier();

        $this->assertDatabaseHas('suppliers', ['name' => 'Thomann']);
        $this->assertDatabaseCount('suppliers', 1);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->delete('/suppliers/'.$supplier->id);

        $this->assertDatabaseCount('suppliers', 0);
    }
}
