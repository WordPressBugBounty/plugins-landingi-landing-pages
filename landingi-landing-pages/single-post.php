<?php
use Landingi\Wordpress\Plugin\LandingiPlugin\LandingiWordpressPlugin;

require_once __DIR__ . '/vendor/autoload.php';

$landingiPluginApp = LandingiWordpressPlugin::getInstance();
$landingiPluginApp->dispatchPost();
