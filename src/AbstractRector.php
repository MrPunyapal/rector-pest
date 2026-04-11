<?php

declare(strict_types=1);

namespace RectorPest;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector as BaseAbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * Base abstract class for all Pest rectors
 * Provides common helper methods for working with Pest's expect() chains
 */
abstract class AbstractRector extends BaseAbstractRector implements DocumentedRuleInterface
{
    /**
     * Check if a method call is part of an expect() chain
     */
    protected function isExpectChain(MethodCall $methodCall): bool
    {
        $root = $this->getExpectChainRoot($methodCall);

        if ($root instanceof FuncCall) {
            return $this->isName($root, 'expect');
        }

        return false;
    }

    /**
     * Get the root expect() function call from a method chain
     */
    protected function getExpectFuncCall(MethodCall $methodCall): ?FuncCall
    {
        $root = $this->getExpectChainRoot($methodCall);

        if ($root instanceof FuncCall && $this->isName($root, 'expect')) {
            return $root;
        }

        if ($root instanceof PropertyFetch && $root->var instanceof FuncCall && $this->isName($root->var, 'expect')) {
            return $root->var;
        }

        return null;
    }

    /**
     * Get the argument passed to expect() from a method chain
     */
    protected function getExpectArgument(MethodCall $methodCall): ?Expr
    {
        $expectCall = $this->getExpectFuncCall($methodCall);

        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        if (! isset($expectCall->args[0])) {
            return null;
        }

        $arg = $expectCall->args[0];

        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }

    /**
     * Check if the expect() argument matches a specific type category
     */
    protected function isExpectValueOfType(MethodCall $methodCall, string $typeCheck): bool
    {
        $expectArgument = $this->getExpectArgument($methodCall);
        if ($expectArgument === null) {
            return false;
        }

        $type = $this->getType($expectArgument);

        return match ($typeCheck) {
            'boolean' => $type->isBoolean()->yes(),
            'string' => $type->isString()->yes(),
            'integer' => $type->isInteger()->yes(),
            'float' => $type->isFloat()->yes(),
            'numeric' => $type->isInteger()->yes() || $type->isFloat()->yes(),
            'array' => $type->isArray()->yes(),
            'object' => $type->isObject()->yes(),
            'iterable' => $type->isIterable()->yes(),
            'null' => $type->isNull()->yes(),
            'scalar' => $type->isScalar()->yes(),
            'callable' => $type->isCallable()->yes(),
            default => false,
        };
    }

    /**
     * Get the root of an expect chain (either FuncCall or PropertyFetch for ->not)
     */
    protected function getExpectChainRoot(MethodCall $methodCall): FuncCall|PropertyFetch|null
    {
        $current = $methodCall->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        // Try to find an underlying FuncCall (expect(...)) even if there are
        // intermediate PropertyFetch nodes (e.g. ->not) whose var is a
        // MethodCall. Walk down through property/method var links to locate
        // the FuncCall if present.
        $search = $current;
        while ($search instanceof PropertyFetch || $search instanceof MethodCall) {
            $search = $search->var;
        }

        if ($search instanceof FuncCall) {
            return $search;
        }

        if ($current instanceof PropertyFetch && $current->var instanceof FuncCall) {
            return $current->var;
        }

        if ($current instanceof FuncCall) {
            return $current;
        }

        if ($current instanceof PropertyFetch) {
            return $current;
        }

        return null;
    }

    /**
     * Check if the expect chain has a ->not modifier
     */
    protected function hasNotModifier(MethodCall $methodCall): bool
    {
        $current = $methodCall->var;

        // Check if the immediate predecessor is a ->not property fetch
        return $current instanceof PropertyFetch && $this->isName($current, 'not');
    }

    /**
     * Collect all method calls in a chain from root to leaf
     *
     * @return array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>, is_property?: bool}>
     */
    protected function collectChainMethods(MethodCall $methodCall): array
    {
        $methods = [];
        $current = $methodCall;

        while (true) {
            if ($current instanceof MethodCall) {
                $methods[] = [
                    'name' => $current->name,
                    'args' => $current->args,
                    'is_property' => false,
                ];

                $next = $current->var;
            } elseif ($current instanceof PropertyFetch) {
                $methods[] = [
                    'name' => $current->name,
                    'args' => [],
                    'is_property' => true,
                ];

                $next = $current->var;
            } else {
                break;
            }

            if ($next instanceof FuncCall) {
                break;
            }

            $current = $next;
        }

        return array_reverse($methods);
    }

    /**
     * Rebuild a method chain from a base expression
     *
     * @param array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>, is_property?: bool}> $methods
     */
    protected function rebuildMethodChain(Expr $base, array $methods): Expr
    {
        $result = $base;

        foreach ($methods as $method) {
            if (! empty($method['is_property'])) {
                $result = new PropertyFetch($result, $method['name']);
            } else {
                $result = new MethodCall($result, $method['name'], $method['args']);
            }
        }

        return $result;
    }
}
