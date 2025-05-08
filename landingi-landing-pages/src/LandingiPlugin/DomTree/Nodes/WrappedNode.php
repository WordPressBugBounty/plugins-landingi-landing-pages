<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\DomTree\Nodes;

interface WrappedNode
{
    public function getDomNode(): \DOMNode;
}
