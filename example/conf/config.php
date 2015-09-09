<?php
//NOCOMMIT
use PassByME\TwoFactor\Config;

Config::set('curl_debug', true);

Config::set('curl_proxy', 'http://proxy.graphi.intra.microsec.hu');
Config::set('curl_proxyport', '3128');

/*Config::set('cert_file', 'c:/tmp/pfx/outcert.pem');
Config::set('cert_key', 'c:/tmp/pfx/outkey.key');*/

Config::set('cert_file', 'c:/tmp/pfx/1.3.6.1.4.1.21528.3.3.2.133.2.135.pem');
Config::set('cert_key', 'c:/tmp/pfx/private.key');
