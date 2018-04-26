<?php

namespace Liushuangxi\AdLdap;

use LdapTools\Object\LdapObjectType;

/**
 * Class AdUser
 * http://www.phpldaptools.com/reference/Default-Schema-Attributes/#ad-user-types
 *
 * @package Liushuangxi\AdLdap
 */
class AdUser extends Ad
{
    /**
     * @var LdapObjectType
     */
    public $objectType = LdapObjectType::USER;

    /**
     * @var string
     */
    protected $funcFindOne = 'findOneByUsername';

    /**
     * @var string
     */
    protected $keyFindOne = 'username';

    /**
     * @param string $ou
     * @param array $params
     *  username
     *  password
     *  emailAddress
     *  enabled
     *
     *  employeeId
     *  firstName
     *  lastName
     *  displayName
     *  description
     *
     * @return bool
     */
    public function createUser($ou = '', $params = [])
    {
        if (empty($ou)
            || !isset($params['username'])
            || !isset($params['password'])
            || !isset($params['emailAddress'])
            || !isset($params['enabled'])
            || !filter_var($params['emailAddress'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->logError(json_encode($params));

            return false;
        }

        //创建邮箱
        $mail = [
            'username' => $params['username'],
            'password' => $params['password'],
            'emailAddress' => $params['emailAddress'],
            'enabled' => $params['enabled']
        ];

        $result = $this->mail()->createMail($ou, $mail);

        //修改账号
        $params['proxyAddresses'] = [
            "SMTP:" . $params['emailAddress']
        ];

        if ($result) {
            $result = $this->change($params['username'], $params);
        }

        return $result;
    }

    /**
     * @param string $username
     * @param array $groupNames
     * @param string $type
     *  add / delete
     *
     * @return bool
     */
    public function changeGroups($username = '', $groupNames = [], $type = 'add')
    {
        $user = $this->get($username, true);
        if (empty($user)) {
            return false;
        }

        if (isset($user['groups'])) {
            $groups = $user['groups'];
        } else {
            $groups = [];
        }

        switch ($type) {
            case 'add':
                $groups = array_values(array_unique(array_merge($groups, $groupNames)));
                break;
            case 'delete':
                $groups = array_values(array_unique(array_diff($groups, $groupNames)));
                break;
            default:
                return false;
        }

        //检查邮件组
        foreach ($groups as $key => $groupName) {
            $group = $this->group()->getGroupByNameOrAccountName($groupName);

            if (empty($group)) {
                unset($groups[$key]);
            } else {
                $groups[$key] = $group['accountName'];
            }
        }
        $groups = array_values($groups);

        return $this->change(
            $username,
            [
                'groups' => $groups
            ]
        );
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function changePassword($username = '', $password = '')
    {
        return $this->change(
            $username,
            [
                'password' => $password
            ]
        );
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function verifyPassword($username = '', $password = '')
    {
        try {
            return $this->ldap->authenticate($username, $password);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return false;
        }
    }

    /**
     * @param array $dns
     *
     * @return array
     */
    public static function getUsernameFromDns($dns = [])
    {
        return array_filter(
            array_map(
                function ($dn) {
                    if (strpos($dn, 'CN=') !== 0) {
                        return '';
                    }

                    if (strpos($dn, ',') > 3) {
                        return substr($dn, 3, strpos($dn, ',') - 3);
                    } else {
                        return '';
                    }
                },
                $dns
            )
        );
    }
}
