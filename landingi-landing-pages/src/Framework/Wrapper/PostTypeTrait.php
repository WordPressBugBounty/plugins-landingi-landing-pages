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

            if (!empty($query->query['name'])) {
                $query->set('post_type', array_merge(get_post_types(), [self::POST_TYPE]));
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
            $currentUrl = parse_url(esc_url_raw(add_query_arg([])));
            $landingPath = isset($currentUrl['host']) ?
                sprintf('%s://%s%s', $currentUrl['scheme'], $currentUrl['host'], $currentUrl['path']) :
                $currentUrl['path'];

            $object = get_page_by_path(
                trim($landingPath, '/'),
                OBJECT,
                self::POST_TYPE
            );

            if (isset($object->post_type) && $object->post_type === 'landing') {
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
}
