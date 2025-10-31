<?php

/**
 * This file demonstrates advanced PHPUnit to Pest migration scenarios
 */

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AdvancedTest extends TestCase
{
    private $connection;
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = new Connection();
        $this->database = new Database($this->connection);
    }

    public function testDatabaseConnection(): void
    {
        $this->assertNotNull($this->connection);
        $this->assertTrue($this->connection->isConnected());
        $this->assertInstanceOf(Connection::class, $this->connection);
    }

    public function test_query_execution(): void
    {
        $result = $this->database->query('SELECT * FROM users');
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(5, $result);
        $this->assertArrayHasKey('id', $result[0]);
    }

    public function testStringOperations(): void
    {
        $text = 'Hello World';
        
        $this->assertIsString($text);
        $this->assertStringContainsString('World', $text);
        $this->assertEquals('HELLO WORLD', strtoupper($text));
    }

    public function testNumericComparisons(): void
    {
        $value = 42;
        
        $this->assertIsInt($value);
        $this->assertGreaterThan(40, $value);
        $this->assertLessThanOrEqual(42, $value);
        $this->assertGreaterThanOrEqual(42, $value);
        $this->assertLessThan(50, $value);
    }

    public function testBooleanValues(): void
    {
        $flag = true;
        $disabled = false;
        
        $this->assertIsBool($flag);
        $this->assertTrue($flag);
        $this->assertFalse($disabled);
        $this->assertNotTrue($disabled);
    }

    public function testFloatingPointNumbers(): void
    {
        $price = 19.99;
        
        $this->assertIsFloat($price);
        $this->assertEquals(19.99, $price);
    }

    public function testObjectComparisons(): void
    {
        $user1 = new User('John');
        $user2 = new User('Jane');
        
        $this->assertIsObject($user1);
        $this->assertNotSame($user1, $user2);
        $this->assertNotEquals($user1->getName(), $user2->getName());
    }

    public function testArrayContains(): void
    {
        $fruits = ['apple', 'banana', 'orange'];
        
        $this->assertContains('banana', $fruits);
        $this->assertNotContains('grape', $fruits);
    }

    public function testEmptyAndNull(): void
    {
        $emptyArray = [];
        $nullValue = null;
        $nonEmpty = [1, 2, 3];
        
        $this->assertEmpty($emptyArray);
        $this->assertNotEmpty($nonEmpty);
        $this->assertNull($nullValue);
        $this->assertNotNull($nonEmpty);
    }

    protected function tearDown(): void
    {
        $this->database->disconnect();
        $this->connection->close();
        parent::tearDown();
    }
}
