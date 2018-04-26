# adldap-php

## 安装
<pre>
composer require liushuangxi/adldap-php -vvv
</pre>

## 配置
<pre>
use Liushuangxi\AdLdap\AdUser;
use Liushuangxi\AdLdap\AdGroup;

class Logger
{
    public function logError($message)
    {}

    public function logInfo($message)
    {}
}

$config = [
    'config_file' => './configs/ldaptools.yml',
    'logger' => new Logger()
];
</pre>

## Ad
* create($container = '', $attributes = [])
* change($name = '', $attributes = [], $funcFindOne = '')
* changeOu($name = '', $newOu = '')
* get($name = '', $allAttributes = false)
* all($attributes = [], $whereStatements = [])
* delete($name = '', $funcFindOne = '')
* getBaseDn()
* logError($message = '')
* logInfo($message = '')

## AdUser
* createUser($ou = '', $params = [])
* changeGroups($username = '', $groupNames = [], $type = 'add')
* changePassword($username = '', $password = '')
* verifyPassword($username = '', $password = '')
* getUsernameFromDns($dns = [])

## AdGroup
* createGroup($ou = '', $params = [])
* changeMembers($groupName = '', $usernames = [], $type = 'add')
* getMembers($groupName = '', $page = 1, $pageSize = 500, $showDn = true)
* getGroupByNameOrAccountName($name)
