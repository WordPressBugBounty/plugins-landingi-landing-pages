<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin;

use Landingi\Wordpress\Plugin\Framework\Kernel\PluginKernel;
use Landingi\Wordpress\Plugin\LandingiPlugin\Controller\AdminMenuImportedLandings;
use Landingi\Wordpress\Plugin\LandingiPlugin\Controller\LandingPostController;
use Landingi\Wordpress\Plugin\LandingiPlugin\Model\LandingPostType;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClientService;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\LandendApiClientService;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\PluginInstaller;
use Landingi\Wordpress\Plugin\LandingiPlugin\Controller\AdminMenuSettings;
use Landingi\Wordpress\Plugin\LandingiPlugin\Controller\AdminMenuAvailableLandings;

class LandingiWordpressPlugin extends PluginKernel
{
    const NAME = 'Landingi Landing Pages';
    const REQUIRED_PHP = '5.5';

    protected function initializeContainers()
    {
        $this->containerCollection->set('service.plugin.installer', new PluginInstaller(
            $this->containerCollection,
            $this->getConfig('landingi_plugin_path')
        ));

        $this->containerCollection->set('service.api.client', new ApiClientService(
            $this->getConfig('landingi_api_url'),
            $this->containerCollection->get('service.plugin.installer')->getToken()
        ));

        $this->containerCollection->set('service.api.landend.client', new LandendApiClientService(
            $this->getConfig('landingi_export_url')
        ));

        $this->containerCollection->set(
            'model.landing.post.type',
            new LandingPostType($this->getConfig('landingi_singlepost_path'))
        );

        $this->containerCollection->set('controller.admin.menu.available_landings', new AdminMenuAvailableLandings(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->containerCollection->get('service.api.client'),
            $this->containerCollection->get('model.landing.post.type'),
            $this->configCollection
        ));

        $this->containerCollection->set('controller.admin.menu.imported_landings', new AdminMenuImportedLandings());

        $this->containerCollection->set('controller.admin.menu.settings', new AdminMenuSettings(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->configCollection
        ));

        $this->containerCollection->set('postcontroller.landing', new LandingPostController(
            $this->containerCollection->get('framework.twig'),
            $this->containerCollection->get('framework.http.request'),
            $this->configCollection,
            $this->containerCollection->get('service.api.landend.client')
        ));

        $this->compileCollections();
    }

    private function compileCollections()
    {
        $this->containerCollection->get('framework.post.type.collection')->addPostType(
            $this->containerCollection->get('model.landing.post.type')
        );
    }
}
