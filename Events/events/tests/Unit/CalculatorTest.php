<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Calculator;

class CalculatorTest extends TestCase
{
    /** @test */
    public function it_can_add_two_numbers()
    {
        $calculator = new Calculator();

        $result = $calculator->add(8, 9);

        $this->assertEquals(17, $result);
    }
}
