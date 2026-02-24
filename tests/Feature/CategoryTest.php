<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    #[Test]
    public function unauthenticated_user_cannot_access(): void
    {
        $this->get('categories/')
            ->assertRedirect('login/');
    }

    #[Test]
    public function logged_user_has_access_to_url(): void
    {
        $this->withoutExceptionHandling();

        $this->createCategory();
        $this->assertDatabaseCount('categories', 1)
            ->assertDatabaseHas('categories', ['name' => 'Speakers']);

        $user = $this->createUser();

        $this->actingAs($user)
            ->get('categories/')
            ->assertOk()
            ->assertViewIs('categories.index');
    }

    #[Test]
    public function user_can_use_create_view(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('categories/create')
            ->assertViewIs('categories.create');
    }

    #[Test]
    public function user_can_see_edit_view(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();

        $this->actingAs($user)
            ->get('categories/'.$category->slug.'/edit')
            ->assertOk()
            ->assertViewIs('categories.edit');
    }

    #[Test]
    public function user_can_see_show_view(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();

        $this->actingAs($user)
            ->get('categories/'.$category->slug)
            ->assertOk()
            ->assertViewIs('categories.show');
    }

    #[Test]
    public function user_can_delete_category(): void
    {
        $category = $this->createCategory();

        $this->assertDatabaseHas('categories', ['name' => 'Speakers']);
        $this->assertDatabaseCount('categories', 1);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->delete('/categories/'.$category->slug);

        $this->assertDatabaseCount('categories', 0);
    }
}
