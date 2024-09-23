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
    const DEFAULT_ENCODING_CHARSET = 'UTF-8';

    /**
     * @var DOMDocument
     */
    private $domDocument;

    /**
     * @param string $domContent
     * @throws EmptyDomContentException
     */
    public function __construct($domContent)
    {
        if (empty($domContent)) {
            throw new EmptyDomContentException('Dom content cannot be empty');
        }

        libxml_use_internal_errors(true);
        $this->domDocument = new DOMDocument();

        // The mbstring extension is required but if the server does not have it installed then polyfill is used as a replacement
        $this->domDocument->loadHTML(
            mb_convert_encoding($domContent, 'HTML-ENTITIES', self::DEFAULT_ENCODING_CHARSET),
            LIBXML_NOERROR
        );
    }

    /**
     * @param string $regex
     * @param WrappedNode $node
     */
    public function insertAfterScriptSourceRegex($regex, WrappedNode $node)
    {
        $scriptNodes = $this->domDocument->getElementsByTagName('script');

        if ($scriptNodes->length === 0) {
            throw new NodeDoesNotExistsException('Script node does not exists in current context');
        }

        /** @var DOMNode $scriptNode */
        foreach ($scriptNodes as $key => $scriptNode) {
            if ($scriptNode->hasAttributes()) {
                if ($sourceAttribute = $scriptNode->attributes->getNamedItem('src')) {
                    if ($this->hasNextNode($scriptNodes, $key) && $this->matchRegex($sourceAttribute, $regex)) {
                        $this->getHeadNode()->insertBefore($node->getDomNode(), $scriptNodes[$key + 1]);
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function save()
    {
        return html_entity_decode($this->domDocument->saveHTML(), ENT_HTML5, self::DEFAULT_ENCODING_CHARSET);
    }

    /**
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->domDocument;
    }

    /**
     * @param int $offset
     * @throws NodeDoesNotExistsException
     * @return DOMNode
     */
    protected function getHeadNode($offset = 0)
    {
        $headNodes = $this->domDocument->getElementsByTagName('head');

        if ($headNodes->length === 0) {
            throw new NodeDoesNotExistsException('Head node does not exists in current context');
        }

        return $headNodes->item($offset);
    }

    /**
     * @param DOMNodeList $scriptNodes
     * @param int $key
     * @return bool
     */
    private function hasNextNode(DOMNodeList $scriptNodes, $key)
    {
        return isset($scriptNodes[$key + 1]);
    }

    /**
     * @param DOMNode|null $sourceAttribute
     * @param string $regex
     * @return bool
     */
    private function matchRegex(DOMNode $sourceAttribute, $regex)
    {
        return $sourceAttribute !== null && preg_match($regex, $sourceAttribute->textContent);
    }
}
