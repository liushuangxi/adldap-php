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
     * @param string $ou
     * @param array $params
     *  name
     *  emailAddress
     *
     *  displayName
     *  description
     *
     * @return bool
     */
    public function createGroup($ou = '', $params = [])
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
     * @param string $groupName
     * @param array $usernames
     * @param string $type
     *  add /delete
     *
     * @return bool
     */
    public function changeMembers($groupName = '', $usernames = [], $type = 'add')
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

        switch ($type) {
            case 'add':
                $members = array_values(array_unique(array_merge($members, $usernames)));
                break;
            case 'delete':
                $members = array_values(array_unique(array_diff($members, $usernames)));
                break;
            default:
                return false;
        }

        //检查用户
        foreach ($members as $key => $member) {
            if (empty($this->user()->get($member))) {
                unset($members[$key]);
            }
        }
        $members = array_values($members);

        $result = $this->change(
            $groupName, [
            'members' => $members
        ]);

        return $result;
    }

    /**
     * https://msdn.microsoft.com/en-us/library/Aa367017
     *
     * @param string $groupName
     * @param int $page
     * @param int $pageSize
     * @param bool $showDn
     *
     * @return array
     */
    public function getMembers($groupName = '', $page = 1, $pageSize = 500, $showDn = true)
    {
        $limit = $pageSize > 1000 ? 1000 : $pageSize;

        $start = ($page - 1) * $pageSize;
        $start = $start < 0 ? 0 : $start;

        $group = $this->get($groupName, true);
        if (empty($group)) {
            return [];
        }

        $members = [];
        if (isset($group['members']) && !empty($group['members'])) {
            if ($showDn) {
                $members = $group['members'];
            } else {
                $members = AdUser::getUsernameFromDns($group['members']);
            }

            sort($members);

            if ($page) {
                $members = array_slice($members, $start, $pageSize);
            }
        } else {
            while ($start >= 0) {
                $attr = "member;range=" . $start . "-" . ($start + $limit - 1);

                $group = $this->all(
                    $attr,
                    [
                        'accountName' => $groupName
                    ]
                );

                if (isset($group[0][$attr]) && count($group[0][$attr]) == $limit) {
                    $start += $limit;

                    if ($showDn) {
                        $members = array_merge($members, $group[0][$attr]);
                    } else {
                        $members = array_merge($members, AdUser::getUsernameFromDns($group[0][$attr]));
                    }
                } else {
                    $start = -1;
                }

                if ($page) {
                    $start = -1;
                }
            }

            sort($members);
        }

        return array_values(array_unique($members));
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function getGroupByNameOrAccountName($name)
    {
        $group = $this->all(
            '*',
            [
                'name' => $name
            ]
        );

        if (isset($group[0]) && !empty($group[0])) {
            return $group[0];
        }

        $group = $this->all(
            '*',
            [
                'accountName' => $name
            ]
        );

        if (isset($group[0]) && !empty($group[0])) {
            return $group[0];
        }

        return [];
    }
}
