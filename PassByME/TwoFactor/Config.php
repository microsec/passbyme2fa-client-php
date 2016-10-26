<?php
/**
 * This is the configuration class for PassBy[ME] 2Fa communication.
 * This class can be used as main config or you can set these properties in your own code.
 *
 * Example:
 * Config::set('aut_api_url', 'https://auth-sp.passbyme.com/frontend');
 * Config::set('curl_debug', true);
 * ...
 */
namespace PassByME\TwoFactor;

class Config
{
    private static $config = array(
        /**
         * Authentication API webservice url
         * @var string
         */
        'auth_url' => 'https://auth-sp.passbyme.com/frontend',
        /**
         * Application PEM file.
         * @var string
         */
        'auth_cert' => '',
        /**
         * Application PEM password.
         * @var string
         */
        'auth_pwd' => '',
        /**
         * Management API webservice url
         * @var string
         */
        'mng_url' => 'https://api.passbyme.com/register',
        /**
         * Organisation PEM file.
         * @var string
         */
        'mng_cert' => '',
        /**
         * Organisation PEM file password.
         * @var string
         */
        'mng_pwd' => '',
        /**
         * CA certificate path.
         * @var string
         */
        'ca_cert' => '',
        /**
         * The maximum number of seconds to allow cURL functions to execute.
         * @var integer
         */
        'curl_timeout' => 30,
        /**
         * The maximum amount of HTTP redirections to follow.
         * @var integer
         */
        'curl_maxredirs' => 10,
        /**
         * The number of seconds to wait while trying to connect.
         * @var integer
         */
        'curl_connecttimeout' => 120,
        /**
         * The contents of the "User-Agent:" header to be used in a HTTP request.
         * @var string
         */
        'curl_useragent' => '',
        /**
         * TRUE to output cURL verbose information. Writes output to log files.
         * @var boolean
         */
        'curl_debug' => false,
        /**
         * cURL Proxy type.
         * @var string
         */
        'curl_proxytype' => 'HTTP',
        /**
         * cURL Proxy URL
         * @var string
         */
        'curl_proxy' => '',
        /**
         * cURL Proxy Port
         * @var string
         */
        'curl_proxyport' => '',
        /**
         * cURL Proxy user/pwd
         * @var string
         */
        'curl_proxyuserpwd' => ''
    );

    public static function get($item)
    {
        return self::$config[$item];
    }

    public static function set($item, $value)
    {
        self::$config[$item] = $value;
        return true;
    }
}
