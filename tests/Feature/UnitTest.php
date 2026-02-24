<?php

namespace Tests\Feature;

use App\Models\Unit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitTest extends TestCase
{
    #[Test]
    public function unauthenticated_user_cannot_access(): void
    {
        $this->get('units/')
            ->assertRedirect('login/');
    }

    #[Test]
    public function logged_user_has_access_to_url(): void
    {
        $this->withoutExceptionHandling();

        $this->createUnit();
        $this->assertDatabaseCount('units', 1)
            ->assertDatabaseHas('units', ['name' => 'piece']);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('units/')
            ->assertOk()
            ->assertViewIs('units.index');
    }

    #[Test]
    public function create_new_unit(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)->get('units/create');

        Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
    }

    #[Test]
    public function edit_unit(): void
    {
        Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
        $this->assertDatabaseCount('units', 1);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('units/piece/edit')
            ->assertOk()
            ->assertViewIs('units.edit')
            ->assertSee('Edit Unit');
    }

    #[Test]
    public function user_can_store_unit(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('units/', [
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('units', 1);
        $this->assertDatabaseHas('units', ['name' => 'Piece']);
    }

    #[Test]
    public function show_unit_returns_not_found(): void
    {
        Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
        $this->assertDatabaseCount('units', 1);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('units/piece/show')
            ->assertNotFound();
    }

    #[Test]
    public function update_unit_request_validation(): void
    {
        $user = $this->createUser();
        $unit = $this->createUnit();

        $response = $this->actingAs($user)->put('units/'.$unit->slug, [
            'name' => '',
            'slug' => '',
        ]);

        $response->assertRedirect();
        $response->assertInvalid(['name', 'slug']);
        $response->assertSessionHasErrors(['name', 'slug']);
    }

    #[Test]
    public function update_unit(): void
    {
        $user = $this->createUser();
        $unit = $this->createUnit();

        $this->assertDatabaseHas('units', ['name' => $unit->name]);

        $response = $this->actingAs($user)->put('units/'.$unit->slug, [
            'name' => 'Meter',
            'slug' => 'meter',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Meter']);
        $response->assertRedirect();
    }

    #[Test]
    public function delete_unit(): void
    {
        $this->withoutExceptionHandling();

        $unit = Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
        $this->assertDatabaseCount('units', 1);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->delete('/units/'.$unit->slug);

        $this->assertDatabaseCount('units', 0);
    }
}
