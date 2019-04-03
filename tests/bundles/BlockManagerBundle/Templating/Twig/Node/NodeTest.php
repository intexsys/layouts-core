<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerBundle\Tests\Templating\Twig\Node;

use Twig\Environment;
use Twig\Test\NodeTestCase;

abstract class NodeTest extends NodeTestCase
{
    protected function getNodeGetter(string $name, int $lineNo = 0): string
    {
        $line = $lineNo > 0 ? "// line {$lineNo}\n" : '';

        if (Environment::VERSION_ID >= 20000) {
            return sprintf('%s(isset($context["%s"]) || array_key_exists("%s", $context) ? $context["%s"] : (function () { throw new RuntimeError(\'Variable "%s" does not exist.\', 1, $this->source); })())', $line, $name, $name, $name, $name);
        }

        return sprintf('%s($context["%s"] ?? $this->getContext($context, "%s"))', $line, $name, $name);
    }
}
