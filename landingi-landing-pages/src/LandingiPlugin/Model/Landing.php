<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Model;

use JsonSerializable;

class Landing implements JsonSerializable
{
    private $id;
    private $name;
    private $hash;
    private $slug;
    private bool $isStatic;
    private $content;
    private $testId;

    public function __construct($id, $name, $hash, $slug, $isStatic = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hash = $hash;
        $this->slug = $slug;
        $this->isStatic = filter_var($isStatic, FILTER_VALIDATE_BOOLEAN);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setTestId($tid)
    {
        $this->testId = $tid;
    }

    public function getTestId()
    {
        return $this->testId;
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        $data['is_static'] = $data['isStatic'];
        unset($data['isStatic']);

        return $data;
    }
}
