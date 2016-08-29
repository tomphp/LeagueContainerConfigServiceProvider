<?php

namespace tests\TomPHP\ConfigServiceProvider\Pimple;

use Interop\Container\ContainerInterface;
use Pimple\Container;

final class PimpleContainerWrapper extends Container
{
    public function get($id)
    {
        return $this[$id];
    }

    public function has($id)
    {
        return isset($this[$id]);
    }
}
