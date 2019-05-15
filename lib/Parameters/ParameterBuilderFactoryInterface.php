<?php

declare(strict_types=1);

namespace Netgen\Layouts\Parameters;

interface ParameterBuilderFactoryInterface
{
    /**
     * Returns the new instance of parameter builder.
     */
    public function createParameterBuilder(array $config = []): ParameterBuilderInterface;
}
