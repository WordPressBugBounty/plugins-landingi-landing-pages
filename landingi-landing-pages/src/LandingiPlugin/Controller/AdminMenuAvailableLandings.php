<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Controller;

use Landingi\Wordpress\Plugin\Framework\Controller\AbstractController;
use Landingi\Wordpress\Plugin\Framework\Http\Request;
use Landingi\Wordpress\Plugin\Framework\Kernel\ConfigCollection;
use Landingi\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;
use Landingi\Wordpress\Plugin\Framework\Model\Post;
use Landingi\Wordpress\Plugin\Framework\Util\TwigService;
use Landingi\Wordpress\Plugin\Framework\Wrapper\AdminMenuTrait;
use Landingi\Wordpress\Plugin\LandingiPlugin\Model\LandingCollection;
use Landingi\Wordpress\Plugin\LandingiPlugin\Model\LandingPostType;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\InvalidTokenException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient\LandingiApiErrorException;
use Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClientService;

class AdminMenuAvailableLandings extends AbstractController implements PluginPartInterface
{
    use AdminMenuTrait;

    const ACTION_TAG = 'admin_menu';
    const PAGE_TITLE = 'Available Landings';
    const SUBMENU_TITLE = 'Available Landings';
    const MENU_SLUG = 'landingi';
    const MENU_ICON = 'landingi_logo.png';
    const MENU_TITLE = 'Landingi';
    const CAPABILITY = 'manage_options';
    const TWIG_TEMPLATE = 'admin_menu_available_landings.html.twig';
    const TWIG_TEMPLATE_SUCCESS = 'admin_menu_publish_landing_success.html.twig';

    /**
     * @var ApiClientService
     */
    private $apiClientService;

    /**
     * @var LandingPostType
     */
    private $landingPostType;

    /**
     * @param TwigService $twigService
     * @param Request $request
     * @param ApiClientService $apiClientService
     * @param LandingPostType $landingPostType
     * @param ConfigCollection $configCollection
     */
    public function __construct(
        TwigService $twigService,
        Request $request,
        ApiClientService $apiClientService,
        LandingPostType $landingPostType,
        ConfigCollection $configCollection
    ) {
        parent::__construct($twigService, $request, $configCollection);
        $this->apiClientService = $apiClientService;
        $this->landingPostType = $landingPostType;
    }

    public function action()
    {
        if (!current_user_can('publish_pages')) {
            show_message('<div class="notice notice-error is-dismissible"><p>Access denied. You need to be able to publish pages!</p></div>');
            die();
        }

        $landingId = $this->request->getPostParameter('landingId');
        $landingName = $this->request->getPostParameter('landingName');
        $nonce = $this->request->getPostParameter('_wpnonce');
        $isImportRequest = isset($landingId, $landingName);

        if ($isImportRequest && !wp_verify_nonce($nonce, 'import-token')) {
            show_message('<div class="notice notice-error is-dismissible"><p>Wrong nonce passed. Try again!</p></div>');
            die();
        }

        $landingSearchPhrase = $this->request->getGetParameter('s');
        $page = (int) $this->request->getGetParameter('landingiPage');
        $page = isset($page) && $page > 0 ? $page : 1;

        try {
            $response = $this->apiClientService->getLandingsForAccount($page, $landingSearchPhrase);
        } catch (InvalidTokenException $exception) {
            return $this->response($this->render(self::TWIG_TEMPLATE, [
                'error' => $exception->getMessage(),
                'settings_url' => admin_url('admin.php?page=' . AdminMenuSettings::MENU_SLUG)
            ]));
        } catch (LandingiApiErrorException $exception) {
            return $this->response($this->render(self::TWIG_TEMPLATE, [
                'error' => $exception->getMessage(),
                'settings_url' => admin_url('admin.php?page=' . AdminMenuSettings::MENU_SLUG)
            ]));
        }

        $landings = new LandingCollection();
        $landings->createFromApiResponse($response);

        if ($isImportRequest) {
            $landingPost = new Post($landingName, json_encode($landings->getLanding($landingId)), $this->landingPostType);
            $landingPost->create();

            return $this->response($this->render(self::TWIG_TEMPLATE_SUCCESS, [
                'url' => admin_url('edit.php?post_type=' . LandingPostType::POST_TYPE)
            ]));
        }

        $maxPage = (int) ceil($landings->getCount() / 10);

        return $this->response($this->render(self::TWIG_TEMPLATE, [
            'nonce' => wp_create_nonce('import-token'),
            'landings' => $landings->getLandings(),
            'currentPage' => $page,
            'maxPage' => $maxPage,
            'queryUrl' => menu_page_url(self::MENU_SLUG, 0),
            'searchPhrase' => $landingSearchPhrase,
        ]));
    }

    public function initialize()
    {
        $this->addAdminMenuPage();
        $this->addAdminSubMenuPage(self::MENU_SLUG);
    }
}
