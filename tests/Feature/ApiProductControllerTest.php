<?php

namespace Tests\Feature;

use App\Enums\TaxType;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiProductControllerTest extends TestCase
{
    #[Test]
    public function product_api_url(): void
    {
        $this->withoutExceptionHandling();

        $this->createProduct();

        $response = $this->get('api/products/');

        $response->assertOk();
        $response->assertSee('Test Product');
        $response->assertDontSee('Test Product 2');
    }

    #[Test]
    public function product_url_with_query_string(): void
    {
        $this->createProduct();

        $response = $this->get('api/products?category_id=1');

        $response->assertOk();
        $response->assertSee('Test Product');
        $response->assertDontSee('Test Product 2');
    }

    #[Test]
    public function product_api_searches_by_reference(): void
    {
        $this->createProductWithReference('Ampli Marshall', 'REF-12345');
        $this->createProductWithReference('Cable Jack', 'REF-99999');

        $response = $this->get('api/products?search=REF-12345');

        $response->assertOk();
        $response->assertJsonFragment(['code' => 'REF-12345']);
        $response->assertJsonMissing(['code' => 'REF-99999']);
    }

    #[Test]
    public function product_api_still_searches_by_name(): void
    {
        $this->createProductWithReference('Ampli Marshall', 'REF-12345');
        $this->createProductWithReference('Cable Jack', 'REF-99999');

        $response = $this->get('api/products?search=Marshall');

        $response->assertOk();
        $response->assertJsonFragment(['code' => 'REF-12345']);
        $response->assertJsonMissing(['code' => 'REF-99999']);
    }

    private function createProductWithReference(string $name, string $code): Product
    {
        $user = $this->createUser();

        $unit = Unit::factory()->create([
            'name' => 'piece',
            'slug' => 'piece',
            'user_id' => $user->id,
        ]);

        return Product::factory()->create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => $code,
            'quantity' => 5,
            'category_id' => null,
            'unit_id' => $unit->id,
            'tax_type' => TaxType::EXCLUSIVE,
        ]);
    }
}
