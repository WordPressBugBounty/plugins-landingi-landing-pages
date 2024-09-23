<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

use Landingi\Wordpress\Plugin\Framework\Event\PostTemplateFilter;
use Landingi\Wordpress\Plugin\Framework\Http\Request;
use Landingi\Wordpress\Plugin\Framework\Model\PostTypeCollection;
use Landingi\Wordpress\Plugin\Framework\Util\TwigService;

abstract class PluginKernel
{
    protected $containerCollection;
    protected $configCollection;

    public function __construct()
    {
        $this->containerCollection = ContainerCollection::getInstance();
        $this->configCollection = ConfigCollection::getInstance();
    }

    protected static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $class = get_called_class();
            self::$instance = new $class();
        }

        return self::$instance;
    }

    public function addConfig($key, $value)
    {
        $this->configCollection->set($key, $value);
    }

    public function getConfig($key)
    {
        return $this->configCollection->get($key);
    }

    private function initializeKernelContainers()
    {
        $this->containerCollection->set('framework.kernel', $this);
        $this->containerCollection->set('framework.http.request', new Request($_GET, $_POST, $_COOKIE, $_SERVER));
        $this->containerCollection->set('framework.twig', new TwigService($this->configCollection));
        $this->containerCollection->set('framework.post.type.collection', new PostTypeCollection());
        $this->containerCollection->set('framework.post.template.filter', new PostTemplateFilter($this->containerCollection));
    }

    protected abstract function initializeContainers();

    public function initialize()
    {
        $this->initializeKernelContainers();
        $this->initializeContainers();

        array_map(
            function ($component) {
                if ($component instanceof PluginPartInterface) {
                    $component->initialize();
                }
            },
            $this->containerCollection->getContainers()
        );
    }

    public function dispatchPost($landingPost = null)
    {
        if ($landingPost == null) {
            return $this->containerCollection->get('postcontroller.' . get_queried_object()->post_type)->action();
        }

        return $this->containerCollection->get('postcontroller.landing')->action($landingPost);
    }
}
