<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Model;

use JsonSerializable;

class Landing implements JsonSerializable
{
    private $id;
    private $name;
    private $hash;
    private $slug;
    private $content;
    private $testId;

    public function __construct($id, $name, $hash, $slug)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hash = $hash;
        $this->slug = $slug;
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

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
