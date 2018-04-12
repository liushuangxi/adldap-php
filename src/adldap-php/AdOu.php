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
    protected $objectType = LdapObjectType::OU;

    /**
     * @var string
     */
    protected $funcFindOne = 'findOneByName';

    /**
     * @var string
     */
    protected $keyFindOne = 'name';
}