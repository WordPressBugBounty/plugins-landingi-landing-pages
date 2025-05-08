<?php
namespace Landingi\Wordpress\Plugin\Framework\Event;

use Landingi\Wordpress\Plugin\Framework\Kernel\ContainerCollection;

abstract class AbstractEvent
{
    protected ContainerCollection $containerCollection;
    protected array $filterArguments;

    public function __construct(ContainerCollection $containerCollection)
    {
        $this->containerCollection = $containerCollection;
    }

    abstract public function filter();
}
