<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Login;

use Webiny\Component\Entity\AbstractEntity;

/**
 * Class LoginRateControlEntity
 * @package Webiny\Login
 *
 * @property string $ip
 * @property int    $timestamp
 * @property string $username
 */
class LoginRateControlEntity extends AbstractEntity
{
    protected static $entityCollection = 'LoginRateControl';

    function __construct()
    {
        parent::__construct();
        $this->attr('ip')->char();
        $this->attr('timestamp')->integer()->setDefaultValue(0);
        $this->attr('username')->char();
    }
}