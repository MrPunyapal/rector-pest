<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use RectorPest\Analyzer\HookSemanticAnalyzer;
use RectorPest\Analyzer\PestChainAnalyzer;
use RectorPest\Analyzer\SemanticExpectationAnalyzer;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Support\PestFunctionDetector;

it('finds invalid describe hooks recursively', function (): void {
    $describeCall = findFirstNode(
        <<<'PHP'
describe('users', function (): void {
    beforeAll(function (): void {});

    describe('nested', function (): void {
        afterAll(function (): void {});
        beforeEach(function (): void {});
    });
});
PHP,
        static fn (Node $node): bool => $node instanceof FuncCall && PestFunctionDetector::isDescribeFunction($node),
    );

    $closure = PestFunctionDetector::extractClosure($describeCall);

    expect($closure)->toBeInstanceOf(Closure::class);

    $invalidHooks = HookSemanticAnalyzer::findInvalidDescribeHooks($closure, [
        'beforeAll' => 'beforeEach',
        'afterAll' => 'afterEach',
    ]);

    expect(array_map(
        PestFunctionDetector::getFunctionName(...),
        $invalidHooks,
    ))->toBe(['beforeAll', 'afterAll']);
});

it('identifies Pest test and expect chains', function (): void {
    $repeatCall = findFirstNode(
        <<<'PHP'
it('retries', function (): void {
    expect('pest')->toBeString();
})->group('slow')->repeat(0);
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'repeat',
    );

    $expectCall = findFirstNode(
        <<<'PHP'
expect('pest')->not->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    expect(PestChainAnalyzer::isPestTestChain($repeatCall))->toBeTrue();
    expect(PestChainAnalyzer::isExpectChain($expectCall))->toBeTrue();
    expect(PestChainAnalyzer::hasNotModifier($expectCall))->toBeTrue();
    expect(PestChainAnalyzer::getExpectArgument($expectCall))->toBeInstanceOf(String_::class);
});

it('classifies literal type expectations conservatively', function (): void {
    $redundantMatcher = findFirstNode(
        <<<'PHP'
expect('pest')->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    $impossibleMatcher = findFirstNode(
        <<<'PHP'
expect('pest')->toBeInt();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeInt',
    );

    $negatedRedundantMatcher = findFirstNode(
        <<<'PHP'
expect(123)->not->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    expect(SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($redundantMatcher)?->issueIdentifier)
        ->toBe(PestSemanticIssues::REDUNDANT_EXPECTATION);

    expect(SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($impossibleMatcher)?->issueIdentifier)
        ->toBe(PestSemanticIssues::IMPOSSIBLE_EXPECTATION);

    expect(SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($negatedRedundantMatcher)?->issueIdentifier)
        ->toBe(PestSemanticIssues::REDUNDANT_EXPECTATION);
});

/**
 * @param callable(Node): bool $filter
 */
function findFirstNode(string $code, callable $filter): Node
{
    $parser = (new ParserFactory())->createForNewestSupportedVersion();
    $nodeFinder = new NodeFinder();
    $nodes = $parser->parse("<?php\n\n" . $code);

    expect($nodes)->not->toBeNull();

    $node = $nodeFinder->findFirst($nodes, $filter);

    expect($node)->toBeInstanceOf(Node::class);

    return $node;
}
