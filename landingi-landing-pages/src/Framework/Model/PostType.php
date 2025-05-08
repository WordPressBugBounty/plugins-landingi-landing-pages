<?php
namespace Landingi\Wordpress\Plugin\Framework\Model;

abstract class PostType
{
    protected array $parameters = [];

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
