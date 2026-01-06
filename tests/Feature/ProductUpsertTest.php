<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductUpsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_product_upsert_by_sku()
    {
        Product::create(['sku' => 'SKU1', 'name' => 'Old', 'price' => 100]);

        Product::updateOrInsert(
            ['sku' => 'SKU1'],
            ['name' => 'New', 'price' => 200]
        );

        $this->assertEquals(
            1,
            Product::where('sku', 'SKU1')->count()
        );

        $this->assertEquals(
            'New',
            Product::where('sku', 'SKU1')->first()->name
        );
    }

}
