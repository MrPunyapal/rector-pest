<?php

declare(strict_types=1);

namespace RectorPest\Support;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/**
 * Shared utility for detecting and inspecting Pest function calls.
 */
final class PestFunctionDetector
{
    /** @var list<string> */
    private const ALL_FUNCTIONS = [
        'it',
        'test',
        'todo',
        'describe',
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
    ];

    /** @var array<string, int> */
    private const CLOSURE_FUNCTIONS = [
        'it' => 1,
        'test' => 1,
        'describe' => 1,
        'beforeEach' => 0,
        'afterEach' => 0,
        'beforeAll' => 0,
        'afterAll' => 0,
    ];

    /** @var list<string> */
    private const TEST_FUNCTIONS = ['it', 'test'];

    public static function getFunctionName(FuncCall $node): ?string
    {
        if (! $node->name instanceof Name) {
            return null;
        }

        $name = $node->name->toString();

        return in_array($name, self::ALL_FUNCTIONS, true) ? $name : null;
    }

    public static function isTestFunction(FuncCall $node): bool
    {
        $name = self::getFunctionName($node);

        return $name !== null && in_array($name, self::TEST_FUNCTIONS, true);
    }

    public static function isDescribeFunction(FuncCall $node): bool
    {
        return self::getFunctionName($node) === 'describe';
    }

    public static function extractClosure(FuncCall $node): Closure|ArrowFunction|null
    {
        $name = self::getFunctionName($node);
        if ($name === null) {
            return null;
        }

        $closureArgIndex = self::CLOSURE_FUNCTIONS[$name] ?? null;
        if ($closureArgIndex === null) {
            return null;
        }

        $args = $node->getArgs();

        if (! isset($args[$closureArgIndex])) {
            return null;
        }

        $value = $args[$closureArgIndex]->value;

        if ($value instanceof Closure || $value instanceof ArrowFunction) {
            return $value;
        }

        return null;
    }

    public static function extractDescription(FuncCall $node): ?string
    {
        if (! self::isTestFunction($node)) {
            return null;
        }

        $args = $node->getArgs();
        if ($args === []) {
            return null;
        }

        $firstArg = $args[0]->value;
        if ($firstArg instanceof String_) {
            return $firstArg->value;
        }

        return null;
    }
}
