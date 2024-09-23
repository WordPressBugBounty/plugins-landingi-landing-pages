<?php
namespace Landingi\Wordpress\Plugin\LandingiPlugin\Service\ApiClient;

class LandingiApiErrorException extends \Exception
{
    public function __construct()
    {
        parent::__construct('We cannot establish a connection to the Landingi API.');
    }
}
