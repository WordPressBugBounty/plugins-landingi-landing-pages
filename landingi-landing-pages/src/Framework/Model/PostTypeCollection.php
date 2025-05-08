<?php
namespace Landingi\Wordpress\Plugin\Framework\Model;

class PostTypeCollection
{
    /**
     * @var PostType[]
     */
    protected array $postTypes = [];

    public function addPostType(PostType $postType): void
    {
        $this->postTypes[$postType::POST_TYPE] = $postType;
    }

    public function getPostTypes(): array
    {
        return $this->postTypes;
    }

    public function getPostType($slug): PostType
    {
        return $this->postTypes[$slug];
    }
}
