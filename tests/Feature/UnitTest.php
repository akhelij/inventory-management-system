<?php

namespace Tests\Feature;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cant_has_access(): void
    {
        $response = $this->get('units/');

        $response
            ->assertStatus(302)
            ->assertRedirect('login/');
    }

    public function test_logged_user_has_access_to_url(): void
    {
        $this->withoutExceptionHandling();

        // Create Unit
        $this->createUnit();
        $this->assertDatabaseCount('units', 1)
            ->assertDatabaseHas('units', ['name' => 'piece']);

        $user = $this->createUser();
        $response = $this->actingAs($user)
            ->get('units/');

        $response->assertStatus(200)
            ->assertViewIs('units.index');
    }

    public function test_create_new_unit(): void
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

    public function test_edit_unit(): void
    {
        Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
        $this->assertDatabaseCount('units', 1);

        $user = $this->createUser();
        $response = $this->actingAs($user)->get('units/piece/edit');

        $response->assertStatus(200)
            ->assertViewIs('units.edit')
            ->assertSee('Edit Unit');
    }

    public function test_user_can_store_unit(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('units/', [
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('units', 1);
        $this->assertDatabaseHas('units', [
            'name' => 'Piece',
        ]);
    }

    public function test_show_unit(): void
    {
        $unit = Unit::create([
            'name' => 'Piece',
            'slug' => 'piece',
            'short_code' => 'pc',
        ]);

        $this->assertDatabaseHas('units', ['name' => 'Piece']);
        $this->assertDatabaseCount('units', 1);

        $user = $this->createUser();
        $response = $this->actingAs($user)->get('units/piece/show');

        $response->assertStatus(404);
    }

    public function test_update_unit_request_validation(): void
    {
        // $this->withoutExceptionHandling();

        $user = $this->createUser();
        $unit = $this->createUnit();

        $response = $this->actingAs($user)->put('units/'.$unit->slug, [
            'name' => '',
            'slug' => '',
        ]);

        $response->assertStatus(302);
        $response->assertInvalid(['name', 'slug']);
        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_update_unit(): void
    {
        // $this->withoutExceptionHandling();

        $user = $this->createUser();
        $unit = $this->createUnit();

        $this->assertDatabaseHas('units', [
            'name' => $unit->name,
        ]);

        $response = $this->actingAs($user)->put('units/'.$unit->slug, [
            'name' => 'Meter',
            'slug' => 'meter',
        ]);

        $this->assertDatabaseHas('units', [
            'name' => 'Meter',
        ]);

        $response->assertRedirect();
    }

    public function test_delete_unit(): void
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
