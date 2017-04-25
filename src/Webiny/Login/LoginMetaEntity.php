<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Login;

use ArrayObject;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * Class LoginSessionEntity
 * @package Webiny\Login
 *
 * @property string      $username
 * @property ArrayObject $previousUsernames
 * @property ArrayObject $loginAttempts
 * @property ArrayObject $allowedDevices
 * @property ArrayObject $sessions
 * @property bool        $blocked
 * @property bool        $confirmed
 * @property string      $confirmationToken
 * @property int         $lastLogin
 * @property string      $forgotPasswordToken
 */
class LoginMetaEntity extends AbstractEntity
{
    protected static $entityCollection = 'LoginMeta';

    function __construct()
    {
        parent::__construct();
        $this->attr('username')->char()->onSet(function ($value) {
            $changed = $this->username != $value;
            if ($changed) {
                $this->previousUsernames[] = [
                    'username'   => $this->username,
                    'modified' => time()
                ];
            }
            return $value;
        });

        $this->attr('loginAttempts')->arr();
        $this->attr('allowedDevices')->arr();
        $this->attr('sessions')->arr();
        $this->attr('blocked')->boolean()->setDefaultValue(false);
        $this->attr('confirmed')->boolean()->setDefaultValue(false);
        $this->attr('confirmationToken')->char();
        $this->attr('lastLogin')->integer();
        $this->attr('forgotPasswordToken')->char();
        $this->attr('previousUsernames')->arr();
    }
}