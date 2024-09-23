<?php
namespace Landingi\Wordpress\Plugin\Framework\Model;

class Post
{
    private $title;
    private $content;
    private $type;

    public function __construct($title, $content, PostType $type)
    {
        $this->title = $title;
        $this->content = $content;
        $this->type = $type::POST_TYPE;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getType()
    {
        return $this->type;
    }

    public function create()
    {
        return wp_insert_post([
            'post_title' => $this->getTitle(),
            'post_content' => $this->getContent(),
            'post_type' => $this->getType(),
            'post_status' => 'publish'
        ]);
    }
}
