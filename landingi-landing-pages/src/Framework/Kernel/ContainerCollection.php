<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

class ContainerCollection
{
    protected $containers = [];
    protected static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getContainers()
    {
        return $this->containers;
    }

    public function setContainers($containers)
    {
        $this->containers = $containers;
    }

    public function get($containerSlug)
    {
        return $this->containers[$containerSlug];
    }

    public function set($containerSlug, $container)
    {
        $this->containers[$containerSlug] = $container;
    }
}
