<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\DomTree;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Exception\EmptyDomContentException;
use Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Exception\NodeDoesNotExistsException;
use Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Nodes\WrappedNode;

class DomDocumentWrapper
{
    public const DEFAULT_ENCODING_CHARSET = 'UTF-8';

    private DOMDocument $domDocument;

    /**
     * @throws EmptyDomContentException
     */
    public function __construct(string $domContent)
    {
        if (empty($domContent)) {
            throw new EmptyDomContentException('Dom content cannot be empty');
        }

        libxml_use_internal_errors(true);
        $this->domDocument = new DOMDocument();

        // The mbstring extension is required but if the server does not have it installed then polyfill is used as a replacement
        $this->domDocument->loadHTML(
            mb_encode_numericentity($domContent, [0x80, 0x10FFFF, 0, ~0], self::DEFAULT_ENCODING_CHARSET),
            LIBXML_NOERROR
        );
    }

    public function insertAfterScriptSourceRegex(string $regex, WrappedNode $node): void
    {
        $scriptNodes = $this->domDocument->getElementsByTagName('script');

        if ($scriptNodes->length === 0) {
            throw new NodeDoesNotExistsException('Script node does not exists in current context');
        }

        /** @var DOMNode $scriptNode */
        foreach ($scriptNodes as $key => $scriptNode) {
            if (
                $scriptNode->hasAttributes()
                && ($sourceAttribute = $scriptNode->attributes->getNamedItem('src'))
                && $this->hasNextNode($scriptNodes, $key)
                && $this->matchRegex($sourceAttribute, $regex)
            ) {
                $this->getHeadNode()->insertBefore($node->getDomNode(), $scriptNodes[$key + 1]);
            }
        }
    }

    public function save(): string
    {
        return html_entity_decode(
            $this->domDocument->saveHTML(),
            ENT_HTML5,
            self::DEFAULT_ENCODING_CHARSET
        );
    }

    public function getDomDocument(): DOMDocument
    {
        return $this->domDocument;
    }

    /**
     * @throws NodeDoesNotExistsException
     */
    protected function getHeadNode(int $offset = 0): DOMNode
    {
        $headNodes = $this->domDocument->getElementsByTagName('head');

        if ($headNodes->length === 0) {
            throw new NodeDoesNotExistsException('Head node does not exists in current context');
        }

        return $headNodes->item($offset);
    }

    private function hasNextNode(DOMNodeList $scriptNodes, int $key): bool
    {
        return isset($scriptNodes[$key + 1]);
    }

    private function matchRegex(?DOMNode $sourceAttribute, string $regex): bool
    {
        return $sourceAttribute !== null && preg_match($regex, $sourceAttribute->textContent);
    }
}
