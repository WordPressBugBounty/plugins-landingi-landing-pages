<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Nodes;

use Landingi\Wordpress\Plugin\LandingiPlugin\Model\Landing;

class LightboxHandlerNode implements WrappedNode
{
    private \DOMDocument $domDocument;

    private Landing $landing;

    private string $siteUrl;

    private string $postName;

    private string $exportUrl;

    public function __construct(
        \DOMDocument $domDocument,
        Landing $landing,
        string $siteUrl,
        string $postName,
        string $exportUrl
    ) {
        $this->domDocument = $domDocument;
        $this->landing = $landing;
        $this->siteUrl = $siteUrl;
        $this->postName = $postName;
        $this->exportUrl = $exportUrl;
    }

    public function getDomNode(): \DOMNode
    {
        return $this->domDocument->createElement('script', $this->getValue());
    }

    private function getValue(): string
    {
        $redirectUrl = sprintf('%s/%s', $this->siteUrl, $this->postName);

        return <<<JS
if (typeof Lightbox !== 'undefined') {
    Lightbox.init({
        exportUrl: '$this->exportUrl',
        hash: '{$this->landing->getHash()}',
        tid: '{$this->landing->getTestId()}',
        redirectUrl: '$redirectUrl'
    });
    Lightbox.register();
}
JS;
    }
}
