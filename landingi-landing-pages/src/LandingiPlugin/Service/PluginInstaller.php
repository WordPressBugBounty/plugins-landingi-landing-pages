<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service;

use Landingi\Wordpress\Plugin\Framework\Kernel\ContainerCollection;
use Landingi\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use Landingi\Wordpress\Plugin\Framework\Kernel\AbstractPluginInstaller;
use Landingi\Wordpress\Plugin\LandingiPlugin\LandingiWordpressPlugin;

class PluginInstaller extends AbstractPluginInstaller implements PluginPartInterface
{
    const PLUGIN_LANDINGI_TOKEN = 'landingi_plugin_token';

    private $pluginPath;

    public function __construct(ContainerCollection $containerCollection, $pluginPath)
    {
        parent::__construct($containerCollection);
        $this->pluginPath = $pluginPath;
    }

    private function createLandingiOptions()
    {
        add_option(self::PLUGIN_LANDINGI_TOKEN, 'Token');
    }

    private function createLandingPostType()
    {
        $landingPostType = $this->containerCollection->get('model.landing.post.type');
        $landingPostType->initialize();
    }

    public function getToken()
    {
        return get_option(self::PLUGIN_LANDINGI_TOKEN);
    }

    protected function registerActivatePluginHooks()
    {
        register_activation_hook($this->pluginPath, function () {
            $this->checkRequirements();
            $this->createLandingiOptions();
            $this->createLandingPostType();
            flush_rewrite_rules();
        });
    }

    private function checkRequirements() {
        if (version_compare(PHP_VERSION, LandingiWordpressPlugin::REQUIRED_PHP, '<')) {
            deactivate_plugins(plugin_basename($this->pluginPath));

            wp_die(sprintf(
                'The %s plugin requires PHP version %s. Your server is running version %s.',
                LandingiWordpressPlugin::NAME,
                LandingiWordpressPlugin::REQUIRED_PHP,
                PHP_VERSION
            ));
        }
    }

    protected function registerDeactivatePluginHooks()
    {
        register_deactivation_hook($this->pluginPath, function () {
            flush_rewrite_rules();
        });
    }

    public function initialize()
    {
        $this->registerActivatePluginHooks();
        $this->registerDeactivatePluginHooks();
    }
}
