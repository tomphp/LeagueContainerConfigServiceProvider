<?php

namespace TomPHP\ConfigServiceProvider;

use ArrayIterator;
use IteratorAggregate;

final class InflectorConfig implements IteratorAggregate
{
    /**
     * @var array
     */
    private $inflectors;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->inflectors = [];

        foreach ($config as $interfaceName => $methods) {
            $this->inflectors[] = new InflectorDefinition(
                $interfaceName,
                $methods
            );
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->inflectors);
    }
}