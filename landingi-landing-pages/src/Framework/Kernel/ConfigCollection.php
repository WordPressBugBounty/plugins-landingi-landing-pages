<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

class ConfigCollection
{
    protected $configs = [];
    protected static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }

    public function get($configSlug)
    {
        return $this->configs[$configSlug];
    }

    public function set($configSlug, $value)
    {
        $this->configs[$configSlug] = $value;
    }
}
