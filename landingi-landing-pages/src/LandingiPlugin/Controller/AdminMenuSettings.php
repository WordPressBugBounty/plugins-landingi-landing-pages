<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Controller;

use Landingi\Wordpress\Plugin\Framework\Controller\AbstractController;
use Landingi\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use Landingi\Wordpress\Plugin\Framework\Wrapper\AdminMenuTrait;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\PluginInstaller;

class AdminMenuSettings extends AbstractController implements PluginPartInterface
{
    use AdminMenuTrait;

    const ACTION_TAG = 'admin_menu';
    const PAGE_TITLE = 'Landingi Settings';
    const SUBMENU_TITLE = 'Settings';
    const MENU_SLUG = 'landingi_settings';
    const CAPABILITY = 'manage_options';

    public function action()
    {
        if (!current_user_can('manage_options')) {
            show_message('<div class="notice notice-error is-dismissible"><p>Access denied. You need to be an administrator to view this page!</p></div>');
            die();
        }

        $returnData = [];
        $landingiToken = $this->request->getPostParameter('landingi_token');
        $nonce = $this->request->getPostParameter('_wpnonce');

        if (isset($landingiToken)) {
            if (!wp_verify_nonce($nonce, 'update-token')) {
                show_message('<div class="notice notice-error is-dismissible"><p>Wrong nonce passed. Try again!</p></div>');
                die();
            }

            update_option(PluginInstaller::PLUGIN_LANDINGI_TOKEN, $landingiToken);
            $returnData['message'] = show_message('<div class="notice notice-success is-dismissible"><p>Token updated!</p></div>');
        }

        $returnData['landingiToken'] = get_option(PluginInstaller::PLUGIN_LANDINGI_TOKEN);
        $returnData['nonce'] = wp_create_nonce('update-token');
        $this->response($this->render('admin_menu_settings.html.twig', $returnData));
    }

    public function initialize()
    {
        $this->addAdminSubMenuPage(AdminMenuAvailableLandings::MENU_SLUG);
    }
}
