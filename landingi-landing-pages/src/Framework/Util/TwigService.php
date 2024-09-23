<?php
namespace Landingi\Wordpress\Plugin\Framework\Util;

use Landingi\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigService
{
    private $twig;
    private $config;

    public function __construct(ConfigCollection $config)
    {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../../../templates');
        $this->twig = new Twig_Environment($loader);
        $this->config = $config;
    }

    public function getEngine()
    {
        return $this->twig;
    }

    public function render($template, $variables)
    {
        $variables = array_merge($variables, $this->config->getConfigs());
        return $this->twig->render($template, $variables);
    }
}
