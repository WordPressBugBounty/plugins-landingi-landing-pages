<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

class ContainerCollection
{
    protected array $containers = [];
    private static ?ContainerCollection $instance = null;

    public static function getInstance(): ContainerCollection
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getContainers(): array
    {
        return $this->containers;
    }

    public function setContainers($containers): void
    {
        $this->containers = $containers;
    }

    public function get($containerSlug)
    {
        return $this->containers[$containerSlug];
    }

    public function set($containerSlug, $container): void
    {
        $this->containers[$containerSlug] = $container;
    }
}
