<?php

namespace Liushuangxi\AdLdap;

use LdapTools\Object\LdapObjectType;

/**
 * Class AdOu
 * http://www.phpldaptools.com/reference/Default-Schema-Attributes/#ad-ou-types
 *
 * @package Liushuangxi\AdLdap
 */
class AdOu extends Ad
{
    /**
     * @var LdapObjectType
     */
    public $objectType = LdapObjectType::OU;

    /**
     * @var string
     */
    public $funcFindOne = 'findOneByName';

    /**
     * @var string
     */
    public $keyFindOne = 'name';
}