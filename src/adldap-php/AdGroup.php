<?php

namespace Liushuangxi\AdLdap;

use LdapTools\Object\LdapObjectType;

/**
 * Class AdGroup
 * http://www.phpldaptools.com/reference/Default-Schema-Attributes/#ad-group-types
 *
 * @package Liushuangxi\AdLdap
 */
class AdGroup extends Ad
{
    /**
     * @var LdapObjectType
     */
    public $objectType = LdapObjectType::GROUP;

    /**
     * @var string
     */
    public $funcFindOne = 'findOneByName';

    /**
     * @var string
     */
    public $keyFindOne = 'name';
}