<?php
use PassByME\TwoFactor\Config;

require_once __DIR__ . '/../PassByME/Autoloader.php';

Config::set('mng_cert', '');
Config::set('mng_pwd', '');
Config::set('mng_url', 'https://api.passbyme.com/register');

Config::set('curl_proxy', '');
Config::set('curl_proxyport', '');
Config::set('curl_proxyuserpwd', '');

