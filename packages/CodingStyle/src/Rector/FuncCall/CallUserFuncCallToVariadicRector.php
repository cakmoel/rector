<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/function.call-user-func-array.php#117655
 *
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\CallUserFuncCallToVariadicRector\CallUserFuncCallToVariadicRectorTest
 */
final class CallUserFuncCallToVariadicRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace call_user_func_call with variadic', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        call_user_func_array('some_function', $items);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        some_function(...$items);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'call_user_func_array')) {
            return null;
        }

        $functionName = $this->getValue($node->args[0]->value);

        $args = [];
        $args[] = new Arg($node->args[1]->value, false, true);
        return $this->createFunction($functionName, $args);
    }
}
