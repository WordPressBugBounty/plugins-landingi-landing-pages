<?php
namespace Landingi\Wordpress\Plugin\Framework\Http;

class Response
{
    private $version = '1.1';
    private $statusCode = 200;
    private $headers = [];
    private $content = '';

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getHeaders()
    {
        return $this->getStandardHeaders();
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name][] = $value;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = [
            $value,
        ];
    }

    public function sendAllHttpHeaders()
    {
        foreach ($this->getHeaders() as $header) {
            header($header, false);
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function redirect($url)
    {
        $this->setHeader('Location', $url);
        $this->setStatusCode(301);
    }

    private function getRequestLineHeaders()
    {
        $headers = [];
        $requestLine = sprintf(
            'HTTP/%s %s',
            $this->version,
            $this->statusCode
        );
        $headers[] = trim($requestLine);
        return $headers;
    }

    private function getStandardHeaders()
    {
        $headers = [];
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        return $headers;
    }

    public function dispatch()
    {
        $this->sendAllHttpHeaders();

        echo $this->getContent();
        return $this->getContent();
    }
}
