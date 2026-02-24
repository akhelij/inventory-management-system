<?php

namespace Tests\Feature;

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
}
