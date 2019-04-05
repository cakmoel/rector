<?php declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 */
final class StartsWithFunctionToNetteUtilsStringsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare string-functions', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, 0, strlen($needle)) === $needle;
        $no = $needle !== substr($content, 0, strlen($needle));
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::startwith($content, $needle);
        $no = !\Nette\Utils\Strings::startwith($content, $needle);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $contentAndNeedle = null;

        if ($node->left instanceof Node\Expr\Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->right, $node->left);
        }

        if ($node->right instanceof Node\Expr\Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->left, $node->right);
        }

        if ($contentAndNeedle === null) {
            return null;
        }

        [$contentNode, $needleNode] = $contentAndNeedle;

        // starts with
        $startsWithStaticCall = $this->createStaticCall('Nette\Utils\Strings', 'startsWith', [
            new Node\Arg($contentNode),
            new Node\Arg($needleNode),
        ]);

        if ($node instanceof Node\Expr\BinaryOp\NotIdentical) {
            return new Node\Expr\BooleanNot($startsWithStaticCall);
        }

        return $startsWithStaticCall;
    }

    /**
     * @return Node\Expr[]|null
     */
    private function matchContentAndNeedleOfSubstrOfVariableLength(Node $node, Node\Expr\Variable $variable): ?array
    {
        if (! $node instanceof Node\Expr\FuncCall) {
            return null;
        }

        if (! $this->isName($node, 'substr')) {
            return null;
        }
        if (! $this->isValue($node->args[1]->value, 0)) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        if (! $node->args[2]->value instanceof Node\Expr\FuncCall) {
            return null;
        }

        if (! $this->isName($node->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var Node\Expr\FuncCall $strlenFuncCall */
        $strlenFuncCall = $node->args[2]->value;
        if ($this->areNodesEqual($strlenFuncCall->args[0]->value, $variable)) {
            return [$node->args[0]->value, $strlenFuncCall->args[0]->value];
        }

        return null;
    }
}