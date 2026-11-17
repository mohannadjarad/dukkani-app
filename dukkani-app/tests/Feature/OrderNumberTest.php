<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Services\OrderService;
use ReflectionMethod;
use Tests\TestCase;

class OrderNumberTest extends TestCase
{
    public function test_every_store_gets_its_own_order_prefix(): void
    {
        $this->assertSame('SC', $this->prefix(new Store(['name' => 'Sweets City', 'slug' => 'sweets-city'])));
        $this->assertSame('QA', $this->prefix(new Store(['name' => 'قطعة السعادة', 'slug' => 'qitat-alsaada'])));
        $this->assertSame('AB', $this->prefix(new Store(['name' => 'أمل بيكنز', 'slug' => 'amal-bakes'])));
        $this->assertSame('ZZ', $this->prefix(new Store([
            'name' => 'Sweets City',
            'slug' => 'sweets-city',
            'order_prefix' => 'zz',
        ])));
    }

    protected function prefix(Store $store): string
    {
        $method = new ReflectionMethod(OrderService::class, 'orderPrefix');
        $method->setAccessible(true);

        return $method->invoke(app(OrderService::class), $store);
    }
}
