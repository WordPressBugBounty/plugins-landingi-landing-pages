<?php
namespace Landingi\Wordpress\Plugin\Framework\Wrapper;

trait AdminMenuTrait
{
    public function addAdminMenuPage()
    {
        add_action(self::ACTION_TAG, function() {
            add_menu_page(
                self::PAGE_TITLE,
                self::MENU_TITLE,
                self::CAPABILITY,
                self::MENU_SLUG,
                [$this, 'action'],
                $this->getConfig('plugin_images_path') . self::MENU_ICON
            );
        });
    }

    public function addAdminSubMenuPage($parentSlug)
    {
        add_action(self::ACTION_TAG, function() use ($parentSlug) {
            add_submenu_page(
                $parentSlug,
                self::PAGE_TITLE,
                self::SUBMENU_TITLE,
                self::CAPABILITY,
                self::MENU_SLUG,
                [$this, 'action']
            );
        });
    }

    public function addPostTypeInSubMenu($parentSlug, $postType)
    {
        add_action(self::ACTION_TAG, function() use ($parentSlug, $postType) {
            add_submenu_page(
                $parentSlug,
                self::PAGE_TITLE,
                self::SUBMENU_TITLE,
                self::CAPABILITY,
                'edit.php?post_type=' . $postType,
                null
            );
        });
    }
}
