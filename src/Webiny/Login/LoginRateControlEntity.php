<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Login;

/**
 * Class LoginRateControlEntity
 * @package Webiny\Login
 */
class LoginRateControlEntity extends \Webiny\Component\Entity\EntityAbstract
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