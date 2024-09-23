<?php
namespace Landingi\Wordpress\Plugin\Framework\Model;

class PostTypeCollection
{
    protected $postTypes = [];

    public function addPostType(PostType $postType)
    {
        $this->postTypes[$postType::POST_TYPE] = $postType;
    }

    public function getPostTypes()
    {
        return $this->postTypes;
    }

    public function getPostType($slug)
    {
        return $this->postTypes[$slug];
    }
}
