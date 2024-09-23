<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Nodes;

interface WrappedNode
{
    /**
     * @return \DOMNode
     */
    public function getDomNode();
}
