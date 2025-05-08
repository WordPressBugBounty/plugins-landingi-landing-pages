<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

class ConfigCollection
{
    protected array $configs = [];
    private static ?ConfigCollection $instance = null;

    public static function getInstance(): ConfigCollection
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }

    public function setConfigs($configs): void
    {
        $this->configs = $configs;
    }

    public function get($configSlug)
    {
        return $this->configs[$configSlug];
    }

    public function set($configSlug, $value): void
    {
        $this->configs[$configSlug] = $value;
    }
}
