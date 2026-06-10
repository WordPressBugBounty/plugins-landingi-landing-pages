<?php
namespace Landingi\Wordpress\Plugin\Framework\Wrapper;

trait PostTypeTrait
{
    public function addPostType($parameters)
    {
        add_action(self::ACTION_TAG, function() use ($parameters) {
            register_post_type(self::POST_TYPE, $parameters);
            flush_rewrite_rules();
        });
    }

    public function removeCategorySlug()
    {
        add_filter('post_type_link', function ($postLink, $post, $leaveName) {
            if (self::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status) {
                return $postLink;
            }

            return str_replace('/' . $post->post_type . '/', '/', $postLink);
        }, 10, 3);

        add_action('pre_get_posts', function ($query) {
            if (!$query->is_main_query() || 2 !== count($query->query) || !isset($query->query['page'])) {
                return;
            }

            if (is_admin()) {
                return;
            }

            if (!empty($query->query['name']) && empty($query->query['post_type'])) {
                $requestPath = $this->getLandingRequestPath();

                if ('' === $requestPath) {
                    return;
                }

                $landing = get_page_by_path($requestPath, OBJECT, self::POST_TYPE);

                if ($landing instanceof \WP_Post) {
                    $query->set('post_type', [self::POST_TYPE]);
                }
            }
        });
    }

    public function removeQuickEdit()
    {
        add_action('post_row_actions', function ($actions, $post) {
            if (self::POST_TYPE === $post->post_type) {
                unset($actions['inline hide-if-no-js']);
            }

            return $actions;
        }, 10, 2 );
    }

    public function addCustomColumns()
    {
        add_filter('manage_edit-' . self::POST_TYPE . '_columns', function ($columns) {
            return $this->getColumns($columns);
        }) ;

        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', function ($column, $post_id) {
            $this->renderColumns($column, $post_id);
        }, 10, 2 );
    }

    public function addPostTemplate($templatePath)
    {
        add_action('do_parse_request', function($doParse, $wp) use ($templatePath) {
            $landingPath = $this->getLandingRequestPath();

            if ('' === $landingPath) {
                return $doParse;
            }

            $object = get_page_by_path(
                $landingPath,
                OBJECT,
                self::POST_TYPE
            );

            if (isset($object->post_type) && $object->post_type === self::POST_TYPE) {
                $wp->query_vars = ['post_type' => self::POST_TYPE, 'page_id' => $object->ID];
                $wp->public_query_vars = ['p', 'page', 'name', 'year', 'monthnum', 'day', 'hour', 'minute', 'second', 'post_id', 'category', 'author'];

                add_action('template_include', function($originalTemplate) use ($templatePath) {
                    return $templatePath;
                }, 65535);

                return $doParse;
            }

            return $doParse;
        }, 10, 2);
    }

    private function getLandingRequestPath(): string
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $requestPath = is_string($requestPath) ? trim($requestPath, '/') : '';

        $installPath = wp_parse_url(site_url(), PHP_URL_PATH);
        $installPath = is_string($installPath) ? trim($installPath, '/') : '';

        if ('' === $installPath) {
            return $requestPath;
        }

        if ($requestPath === $installPath) {
            return '';
        }

        $installPrefix = $installPath . '/';

        if (!str_starts_with($requestPath, $installPrefix)) {
            return '';
        }

        return substr($requestPath, strlen($installPrefix));
    }
}
