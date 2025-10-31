<?php

namespace MrPunyapal\RectorPest\Tests\Fixture;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private $value;

    protected function setUp(): void
    {
        parent::setUp();
        $this->value = 42;
    }

    public function testItWorks(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
    }

    public function test_user_can_login(): void
    {
        $expected = 'user@example.com';
        $actual = 'user@example.com';
        
        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testArrayOperations(): void
    {
        $array = [1, 2, 3];
        
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
    }

    public function testNullValues(): void
    {
        $value = null;
        
        $this->assertNull($value);
        $this->assertEmpty([]);
    }

    protected function tearDown(): void
    {
        $this->value = null;
        parent::tearDown();
    }
}
