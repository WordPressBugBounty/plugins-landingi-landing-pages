<?php
namespace Landingi\Wordpress\Plugin\Framework\Kernel;

use Landingi\Wordpress\Plugin\Framework\Event\PostTemplateFilter;
use Landingi\Wordpress\Plugin\Framework\Http\Request;
use Landingi\Wordpress\Plugin\Framework\Model\PostTypeCollection;
use Landingi\Wordpress\Plugin\Framework\Util\TwigService;
use Throwable;

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
        if ($landingPost !== null) {
            return $this->containerCollection->get('postcontroller.landing')->action($landingPost);
        }

        $post = $this->determinePostObject();

        if (isset($post->post_type)) {
            $serviceName = "postcontroller.{$post->post_type}";
            $controller = $this->containerCollection->get($serviceName);

            if (isset($controller)) {
                return $controller->action($post);
            }
        }

        return null;
    }

    private function determinePostObject(): \WP_Post|null
    {
        try {
            $queriedObject = get_queried_object();
        } catch (Throwable) {
            $queriedObject = null;
        }

        if (isset($queriedObject->post_type)) {
            return $queriedObject;
        }

        global $post;

        if (isset($post->post_type)) {
            return $post;
        }

        try {
            $id = get_the_ID();

            if ($id) {
                $postById = get_post($id);

                if (isset($postById->post_type)) {
                    return $postById;
                }
            }
        } catch (Throwable) {
            // No id found
        }

        if (function_exists('url_to_postid')) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $postIdFromUrl = url_to_postid($uri);

            if ($postIdFromUrl) {
                $postById = get_post($postIdFromUrl);

                if (isset($postById->post_type)) {
                    return $postById;
                }
            }
        }

        if (function_exists('pll_get_post')) {
            $baseId = $queriedObject->ID ?? $post->ID ?? null;

            if ($baseId) {
                $polylangCurrentLanguage = function_exists('pll_current_language') ? pll_current_language() : null;
                $altId = $polylangCurrentLanguage
                    ? pll_get_post($baseId, $polylangCurrentLanguage)
                    : pll_get_post($baseId);

                if ($altId) {
                    $postById = get_post($altId);

                    if ($postById->post_type) {
                        return $postById;
                    }
                }
            }
        }

        $pageName = isset($_GET['pagename']) ? sanitize_text_field($_GET['pagename']) : null;
        $slug = get_query_var('name') ?: $pageName;

        if ($slug) {
            $found = get_posts([
                'name' => $slug,
                'post_type' => 'landing',
                'post_status' => 'publish',
                'numberposts' => 1
            ]);

            if (!empty($found)) {
                return $found[0];
            }
        }

        return null;
    }
}
