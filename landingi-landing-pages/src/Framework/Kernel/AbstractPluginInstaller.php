<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

abstract class AbstractPluginInstaller
{
    protected ContainerCollection $containerCollection;

    public function __construct(ContainerCollection $containerCollection)
    {
        $this->containerCollection = $containerCollection;
    }

    abstract protected function registerActivatePluginHooks();
    abstract protected function registerDeactivatePluginHooks();
}
