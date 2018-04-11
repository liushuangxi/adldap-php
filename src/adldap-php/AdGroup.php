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
    protected $objectType = LdapObjectType::GROUP;

    /**
     * @var string
     */
    protected $funcFindOne = 'findOneByName';

    /**
     * @var string
     */
    protected $keyFindOne = 'name';

    /**
     * @param $ou
     * @param $params
     *  name
     *  emailAddress
     *
     *  displayName
     *  description
     *
     * @return bool
     */
    public function createGroup($ou, $params)
    {
        if (empty($ou)
            || !isset($params['name'])
            || !isset($params['emailAddress'])
            || !filter_var($params['emailAddress'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->logError(json_encode($params));

            return false;
        }

        //创建邮件组
        $params['exchangeAlias'] = substr($params['emailAddress'], 0, strpos($params['emailAddress'], '@'));
        $params['proxyAddresses'] = [
            "SMTP:" . $params['emailAddress']
        ];

        $result = $this->create($ou, $params);

        //修改邮件组
        $group = [
            'scopeGlobal' => 0,
            'scopeUniversal' => 1,
            'exchangeInternalOnly' => 1,
        ];

        foreach ($group as $key => $value) {
            if (isset($params[$key])) {
                $group[$key] = $params[$key];
            }
        }

        if ($result) {
            $result = $this->change($params['name'], $group);
        }

        return $result;
    }

    /**
     * @param $groupName
     * @param $usernames
     * @param string $type
     *  add /delete
     *
     * @return bool
     */
    public function changeMembers($groupName, $usernames, $type = 'add')
    {
        $group = $this->get($groupName, true);
        if (empty($group)) {
            return false;
        }

        if (isset($group['members'])) {
            $members = AdUser::getUsernameFromDns($group['members']);
        } else {
            $members = [];
        }

        if ($type == 'add') {
            //添加
            $members = array_values(array_unique(array_merge($members, $usernames)));
        } else if ($type == 'delete') {
            //删除
            $members = array_values(array_unique(array_diff($members, $usernames)));
        } else {
            return false;
        }

        $result = $this->change(
            $groupName, [
            'members' => $members
        ]);

        return $result;
    }
}
