<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

abstract class AbstractPluginInstaller
{
    protected $containerCollection;

    public function __construct(ContainerCollection $containerCollection)
    {
        $this->containerCollection = $containerCollection;
    }

    protected abstract function registerActivatePluginHooks();
    protected abstract function registerDeactivatePluginHooks();
}
