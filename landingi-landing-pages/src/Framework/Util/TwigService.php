<?php
namespace Landingi\Wordpress\Plugin\Framework\Util;

use Landingi\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private Environment $twig;
    private ConfigCollection $config;

    public function __construct(ConfigCollection $config)
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
        $this->config = $config;
    }

    public function getEngine(): Environment
    {
        return $this->twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render($template, $variables): string
    {
        $variables = array_merge($variables, $this->config->getConfigs());

        return $this->twig->render($template, $variables);
    }
}
