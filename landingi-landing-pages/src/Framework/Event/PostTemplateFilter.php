<?php
namespace Landingi\Wordpress\Plugin\Framework\Event;

use Landingi\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;

class PostTemplateFilter extends AbstractEvent implements PluginPartInterface
{
    const FILTER_TAG = 'single_template';

    public function filter()
    {
        $object = get_queried_object();

        if (array_key_exists($object->post_type, $this->containerCollection->get('framework.post.type.collection')->getPostTypes())) {
            return $this->containerCollection->get('framework.kernel')->getConfig('landingi_singlepost_path');
        } else {
            return $this->filterArguments['singleTemplate'];
        }
    }

    public function initialize()
    {
        add_filter(self::FILTER_TAG, function ($singleTemplate) {
            $this->filterArguments['singleTemplate'] = $singleTemplate;
            return $this->filter();
        }, 99);
    }
}
