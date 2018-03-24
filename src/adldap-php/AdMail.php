<?php

namespace Liushuangxi\AdLdap;

use LdapTools\Object\LdapObjectType;

/**
 * Class AdMail
 * http://www.phpldaptools.com/reference/Default-Schema-Attributes/#exchange-mailbox-user-types
 *
 * @package Liushuangxi\AdLdap\AdMail
 */
class AdMail extends Ad
{
    /**
     * @var LdapObjectType
     */
    public $objectType = LdapObjectType::EXCHANGE_MAILBOX_USER;

    /**
     * @var string
     */
    public $funcFindOne = 'findOneByUsername';

    /**
     * @var string
     */
    public $keyFindOne = 'username';

    /**
     * @param string $dn
     * @param array $attributes
     *  username    required
     *  password    required
     *
     *  emailAddress
     *  enabled
     *
     * @return bool
     */
    public function createMail($dn = '', $attributes = [])
    {
        $data = $this->getServerAndDatabase();

        if (!isset($attributes['mailboxDatabase'])) {
            $attributes['mailboxDatabase'] = $data['database']['name'];
        }

        if (!isset($attributes['mailboxServer'])) {
            $attributes['mailboxServer'] = $data['server']['name'];
        }

        $attributes['showInAddressBooks'] = [];

        return $this->create($dn, $attributes);
    }

    /**
     * @return array
     */
    public function getServerAndDatabase()
    {
        $data = [
            'database' => null,
            'server' => null
        ];

        $databases = $this->ldap->buildLdapQuery()
            ->select('name')
            ->from(LdapObjectType::EXCHANGE_DATABASE)
            ->getLdapQuery()
            ->getArrayResult();
        shuffle($databases);

        $data['database'] = $databases[0];

        $servers = $this->ldap->buildLdapQuery()
            ->select('name')
            ->from(LdapObjectType::EXCHANGE_SERVER)
            ->getLdapQuery()
            ->getArrayResult();
        shuffle($servers);

        $data['server'] = $servers[0];

        return $data;
    }
}
