<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient;

class InvalidTokenException extends \Exception
{
    public function __construct(?string $msg = null)
    {
        parent::__construct($msg ?? 'You need to provide a proper API token, and have at least one published Landing Page on your Landingi account.');
    }
}
