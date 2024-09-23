<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Model;

use Landingi\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use Landingi\Wordpress\Plugin\Framework\Model\PostType;
use Landingi\Wordpress\Plugin\Framework\Wrapper\PostTypeTrait;

class LandingPostType extends PostType implements PluginPartInterface
{
    use PostTypeTrait;

    const POST_TYPE = 'landing';
    const ACTION_TAG = 'init';

    /**
     * @var string
     */
    private $templatePath;

    public function __construct($templatePath)
    {
        $this->templatePath = $templatePath;
        $this->parameters = [
            'labels' => [
                'name' => __('Imported Landings'),
                'singular_name' => __('Landing'),
                'add_new_item' => 'Add new Landing',
                'edit_item' => 'Edit Landing',
                'new_item' => 'New Landing',
                'view_item' => 'View Landing',
                'view_items' => 'View Landings',
                'search_items' => 'Search Landings',
                'not_found' => 'No Landings found',
                'not_found_in_trash' => 'No Landings found in trash'
            ],
            'public' => true,
            'has_archive' => false,
            'show_in_menu' => false,
            'publicly_queryable' => true,
            'show_in_nav_menus' => true,
            'map_meta_cap' => true,
            'rewrite' => ['with_front' => false, 'pages' => false],
            'capabilities' => ['create_posts' => false],
            'supports' => ['title']
        ];
    }

    public function getColumns($columns)
    {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Landing page'),
            'url' => __('Page address'),
            'date' => __('Date')
        ];
    }

    public function renderColumns($column, $postId)
    {
        switch ($column) {
            case 'url':
                echo sprintf('<a href="%s" target="_blank">%s</a>', esc_url(get_permalink()), esc_url(get_permalink()));
                break;
            default:
                break;
        }
    }

    public function setAsHomeButton()
    {
        add_filter('post_row_actions', function($actions, $post) {
            if ($post->post_type == self::POST_TYPE) {
                $nonce = wp_create_nonce('setashomepage-token');
                $admin_url = admin_url('edit.php?post_type='.$post->post_type.'&post_name='.$post->post_name.'&land=setashomepage&_wpnonce='.$nonce);

                $actions['set_as_homepage'] = sprintf(
                    '<a href="%s">Set as Homepage</a>',
                    esc_url($admin_url)
                );
            }

            return $actions;
        }, 10, 2 );
    }

    public function initialize()
    {
        $this->addPostType($this->parameters);
        $this->removeCategorySlug();
        $this->addPostTemplate($this->templatePath);
        $this->removeQuickEdit();
        $this->addCustomColumns();
        $this->setAsHomeButton();
    }
}
