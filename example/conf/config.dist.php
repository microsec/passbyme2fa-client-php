<?php
use PassByME\TwoFactor\Config;


/**
 * The most important configuration are the certificates but if you need more advances configuration settings
 * feel free to use any of the "Defaults" section.
 */
// Full path of the application certificate file. (You can download it in PEM format from the administrator interface).
Config::set('auth_cert', '');
// Application certificate file password.
Config::set('auth_pwd', '');
// Full path of the management certificate file. (You can download it in PEM format from the administrator interface).
Config::set('mng_cert', '');
// Management certificate file password.
Config::set('mng_pwd','');

/**
 * Defaults
 *
 * This is a list of all the possible configuration properties
 * More details about these properties check the PassByME/TwoFactor/Config.php file.
 */
Config::set('auth_url', 'https://auth-sp.passbyme.com/frontend');
Config::set('mng_url', 'https://api.passbyme.com/register');

Config::set('ca_cert', '');
Config::set('curl_timeout', 30);
Config::set('curl_maxredirs', 10);
Config::set('curl_connecttimeout', 120);
Config::set('curl_useragent', '');
Config::set('curl_debug', false);
Config::set('curl_proxytype', 'HTTP');
Config::set('curl_proxy', '');
Config::set('curl_proxyport', '');
Config::set('curl_proxyuserpwd', '');