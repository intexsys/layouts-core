<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Templating\Twig\NodeVisitor;

use Netgen\Bundle\LayoutsBundle\Templating\Twig\Node\DefaultContext as DefaultContextNode;
use Netgen\Bundle\LayoutsBundle\Templating\Twig\Node\RenderZone as RenderZoneNode;
use Symfony\Bridge\Twig\NodeVisitor\Scope;
use Twig\Environment;
use Twig\Node\BlockNode;
use Twig\Node\Expression\AssignNameExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\NodeVisitor\AbstractNodeVisitor;

final class DefaultContext extends AbstractNodeVisitor
{
    /**
     * @var \Symfony\Bridge\Twig\NodeVisitor\Scope|null
     */
    private $scope;

    public function __construct()
    {
        $this->scope = new Scope();
    }

    public function getPriority(): int
    {
        return -10;
    }

    protected function doEnterNode(Node $node, Environment $env): Node
    {
        if (!$this->scope instanceof Scope) {
            return $node;
        }

        if ($node instanceof BlockNode || $node instanceof ModuleNode) {
            $this->scope = $this->scope->enter();
        }

        if ($node instanceof DefaultContextNode) {
            if ($node->getNode('expr') instanceof ConstantExpression) {
                $this->scope->set('context', $node->getNode('expr'));

                return $node;
            }

            $var = $this->getVarName();
            $name = new AssignNameExpression($var, $node->getTemplateLine());
            $this->scope->set('context', new NameExpression($var, $node->getTemplateLine()));

            return new SetNode(false, new Node([$name]), new Node([$node->getNode('expr')]), $node->getTemplateLine());
        }

        if (!$this->scope->has('context')) {
            return $node;
        }

        if ($node instanceof RenderZoneNode && !$node->hasNode('context')) {
            $node->setNode('context', $this->scope->get('context'));
        }

        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env)
    {
        if ($node instanceof DefaultContextNode) {
            return false;
        }

        if ($this->scope instanceof Scope && ($node instanceof BlockNode || $node instanceof ModuleNode)) {
            $this->scope = $this->scope->leave();
        }

        return $node;
    }

    private function getVarName(): string
    {
        return sprintf('__internal_%s', hash('sha256', uniqid((string) mt_rand(), true), false));
    }
}
