<?php

namespace App\Services\Ad;

use LdapTools\Object\LdapObjectType;

/**
 * Class AdUser
 * http://www.phpldaptools.com/reference/Default-Schema-Attributes/#ad-user-types
 *
 * @package App\Libraries\Ad
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
    public $funcFindOne = 'findOneByUsername';

    /**
     * @var string
     */
    public $keyFindOne = 'username';

    /**
     * @var AdGroup
     */
    private $group = null;

    /**
     * @var AdMail
     */
    private $mail = null;

    /**
     * AdUser constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->group = new AdGroup();
        $this->mail = new AdMail();
    }

    /**
     * @param string $username
     * @param string $groupname
     * @param string $type
     *  add / delete
     *
     * @return bool
     */
    public function changeGroup($username = '', $groupname = '', $type = 'add')
    {
        $group = $this->group->get($groupname);
        if (empty($group)) {
            return false;
        }

        $user = $this->get($username, true);
        if (empty($user)) {
            return false;
        }

        if (isset($user['groups'])) {
            $group = $user['groups'];
        } else {
            $group = [];
        }

        switch ($type) {
            case 'add':
                $group[] = $groupname;
                $group = array_values(array_unique($group));
                break;
            case 'delete':
                if (isset($user['groups'])) {
                    $key = array_search($groupname, $group);
                    if ($key === false) {
                        return true;
                    }

                    array_splice($group, $key, 1);
                    $group = array_values($group);
                } else {
                    return true;
                }
                break;
            case 'default':
                return false;
        }

        return $this->change($username, [
            'groups' => $group
        ]);
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
            $this->log($e->getMessage());

            return false;
        }
    }
}
