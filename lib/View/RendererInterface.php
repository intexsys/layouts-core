<?php

namespace Netgen\BlockManager\View;

interface RendererInterface
{
    /**
     * Renders the value object.
     *
     * @param mixed $valueObject
     * @param string $context
     * @param array $parameters
     *
     * @return string
     */
    public function renderValueObject($valueObject, $context = ViewInterface::CONTEXT_DEFAULT, array $parameters = array());
}
