<?php

/**
 * Controller for the URL-specific count function
 */

namespace Proximate\Controller;

class CountUrl extends Count
{
    protected $url;

    protected function fetchCount()
    {
        $regex = '#^' . preg_quote($this->getUrl()) . '#';
        $body = [
            'urlPattern' => $regex,
        ];

        $data = $this->getCurl()->post('__admin/requests/count', $body);

        return isset($data['count']) ? $data['count'] : null;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    protected function getUrl()
    {
        return $this->url;
    }
}
