<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

use Landingi\Wordpress\Plugin\Framework\Event\PostTemplateFilter;
use Landingi\Wordpress\Plugin\Framework\Http\Request;
use Landingi\Wordpress\Plugin\Framework\Model\PostTypeCollection;
use Landingi\Wordpress\Plugin\Framework\Util\TwigService;

abstract class PluginKernel
{
    protected ContainerCollection $containerCollection;
    protected ConfigCollection $configCollection;

    public function __construct()
    {
        $this->containerCollection = ContainerCollection::getInstance();
        $this->configCollection = ConfigCollection::getInstance();
    }

    protected static ?PluginKernel $instance = null;

    public static function getInstance(): PluginKernel
    {
        if (self::$instance === null) {
            $class = static::class;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    public function addConfig($key, $value): void
    {
        $this->configCollection->set($key, $value);
    }

    public function getConfig($key)
    {
        return $this->configCollection->get($key);
    }

    private function initializeKernelContainers(): void
    {
        $this->containerCollection->set('framework.kernel', $this);
        $this->containerCollection->set('framework.http.request', new Request($_GET, $_POST, $_COOKIE, $_SERVER));
        $this->containerCollection->set('framework.twig', new TwigService($this->configCollection));
        $this->containerCollection->set('framework.post.type.collection', new PostTypeCollection());
        $this->containerCollection->set('framework.post.template.filter', new PostTemplateFilter($this->containerCollection));
    }

    abstract protected function initializeContainers();

    public function initialize(): void
    {
        $this->initializeKernelContainers();
        $this->initializeContainers();

        array_map(
            static function ($component) {
                if ($component instanceof PluginPartInterface) {
                    $component->initialize();
                }
            },
            $this->containerCollection->getContainers()
        );
    }

    public function dispatchPost($landingPost = null)
    {
        if ($landingPost === null) {
            return $this->containerCollection->get('postcontroller.' . get_queried_object()->post_type)->action();
        }

        return $this->containerCollection->get('postcontroller.landing')->action($landingPost);
    }
}
