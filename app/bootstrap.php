<?php

require_once '../vendor/autoload.php';

\Webiny\Component\Security\Security::setConfig('./securityConfig.yaml');
\Webiny\Component\Mongo\Mongo::setConfig('./mongoConfig.yaml');
\Webiny\Component\Entity\Entity::setConfig('./entityConfig.yaml');

$security = \Webiny\Component\Security\Security::getInstance();
$loginConfig = \Webiny\Component\Config\Config::getInstance()->yaml('./loginConfig.yaml');

$login = new \Webiny\Login\Login($security, $loginConfig);

