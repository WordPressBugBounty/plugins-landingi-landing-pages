<?php
namespace Landingi\Wordpress\Plugin\Framework\Http;

class Request
{
    protected $getParameters;
    protected $postParameters;
    protected $server;
    protected $cookies;
    protected $input;

    public function __construct(
        $get,
        $post,
        $cookies,
        $server,
        $input = ''
    ) {
        $this->getParameters = $get;
        $this->postParameters = $post;
        $this->cookies = $cookies;
        $this->server = $server;
        $this->input = $input;
    }

    public function getParameter($key)
    {
        if (array_key_exists($key, $this->postParameters)) {
            return $this->postParameters[$key];
        }

        if (array_key_exists($key, $this->getParameters)) {
            return $this->getParameters[$key];
        }

        return null;
    }

    public function getParameters()
    {
        return array_merge($this->getGetParameters(), $this->getPostParameters());
    }

    public function getGetParameter($key)
    {
        if (array_key_exists($key, $this->getParameters)) {
            return $this->getParameters[$key];
        }

        return null;
    }

    public function getGetParameters()
    {
        return $this->getParameters;
    }

    public function getPostParameter($key)
    {
        if (array_key_exists($key, $this->postParameters)) {
            return $this->postParameters[$key];
        }

        return null;
    }

    public function getPostParameters()
    {
        return $this->postParameters;
    }

    public function getCookie($key)
    {
        if (array_key_exists($key, $this->cookies)) {
            return $this->cookies[$key];
        }

        return null;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getServerVariable($key)
    {
        return $this->server[$key];
    }

    public function getUri()
    {
        return $this->getServerVariable('REQUEST_URI');
    }

    public function getUriPath()
    {
        return strtok($this->getServerVariable('REQUEST_URI'), '?');
    }

    public function getMethod()
    {
        return $this->getServerVariable('REQUEST_METHOD');
    }

    public function getHttpAccept()
    {
        return $this->getServerVariable('HTTP_ACCEPT');
    }

    public function getReferer()
    {
        return $this->getServerVariable('HTTP_REFERER');
    }

    public function getUserAgent()
    {
        return $this->getServerVariable('HTTP_USER_AGENT');
    }

    public function getIpAddress()
    {
        return $this->getServerVariable('REMOTE_ADDR');
    }

    public function isSSL()
    {
        return array_key_exists('HTTPS', $this->server) && $this->server['HTTPS'] !== 'off';
    }

    public function getQueryString()
    {
        return $this->getServerVariable('QUERY_STRING');
    }
}