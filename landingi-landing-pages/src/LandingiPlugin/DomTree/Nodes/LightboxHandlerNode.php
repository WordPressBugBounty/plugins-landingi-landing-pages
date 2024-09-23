<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Nodes;

use Landingi\Wordpress\Plugin\LandingiPlugin\Model\Landing;

class LightboxHandlerNode implements WrappedNode
{
    /**
     * @var \DOMDocument
     */
    private $domDocument;

    /**
     * @var Landing
     */
    private $landing;

    /**
     * @var string
     */
    private $siteUrl;

    /**
     * @var string
     */
    private $postName;

    /**
     * @var string
     */
    private $exportUrl;

    /**
     * @param \DOMDocument $domDocument
     * @param Landing $landing
     * @param string $siteUrl
     * @param string $postName
     * @param string $exportUrl
     */
    public function __construct(\DOMDocument $domDocument, Landing $landing, $siteUrl, $postName, $exportUrl)
    {
        $this->domDocument = $domDocument;
        $this->landing = $landing;
        $this->siteUrl = $siteUrl;
        $this->postName = $postName;
        $this->exportUrl = $exportUrl;
    }

    /**
     * @return string
     */
    private function getValue()
    {
        $redirectUrl = sprintf('%s/%s', $this->siteUrl, $this->postName);

        return <<<JS
if (typeof Lightbox !== 'undefined') {
    Lightbox.init({
        exportUrl: '{$this->exportUrl}',
        hash: '{$this->landing->getHash()}',
        tid: '{$this->landing->getTestId()}',
        redirectUrl: '{$redirectUrl}'
    });
    Lightbox.register();
}
JS;
    }

    /**
     * @inheritDoc
     */
    public function getDomNode()
    {
        return $this->domDocument->createElement('script', $this->getValue());
    }
}
