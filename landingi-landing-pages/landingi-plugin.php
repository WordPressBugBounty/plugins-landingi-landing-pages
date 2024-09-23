<?php
/*
Plugin Name: Landingi Landing pages
Plugin URI: https://landingi.com/
Description: Landingi is a Web app to speed up and simplify the process of building, publishing, optimizing and managing landing pages on a large scale for lead generation process. We are integrated with leading marketing tools so that the marketer can take full advantage of his existing marketing stack and deliver more high quality leads.
Version: 3.1.4
Author: Landingi
License: GPLv2
Text Domain: landingi-plugin
*/

use Landingi\Wordpress\Plugin\LandingiPlugin\LandingiWordpressPlugin;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/LandingiPlugin/PageTemplater.php';

$landingiPluginApp = LandingiWordpressPlugin::getInstance();
$landingiPluginApp->addConfig('landingi_plugin_path', __FILE__);
$landingiPluginApp->addConfig('landingi_singlepost_path', __DIR__ . '/single-post.php');
$landingiPluginApp->addConfig('landingi_api_url', 'https://api.landingi.com/');
$landingiPluginApp->addConfig('landingi_export_url', 'https://www.landingiexport.com');
$landingiPluginApp->addConfig('landingi_tests_domain', 'dotests.com');
$landingiPluginApp->addConfig(
    'plugin_images_path',
    sprintf('%s/plugins/%s/resources/images/', content_url(), pathinfo(__DIR__, PATHINFO_FILENAME))
);
$landingiPluginApp->initialize();
