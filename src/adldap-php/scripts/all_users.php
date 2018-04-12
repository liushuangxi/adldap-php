<?php
require_once "../../../vendor/autoload.php";

use LdapTools\Object\LdapObjectType;
use Liushuangxi\AdLdap\AdUser;

class Logger
{
    public function logError($message)
    {
        echo "error:$message\n";
    }

    public function logInfo($message)
    {
        echo "info:$message\n";
    }
}

$config = [
    'config_file' => '../configs/ldaptools.yml',
    'logger' => new Logger()
];

$lqb = (new AdUser($config))->ldap->buildLdapQuery();

$prefix = 'z';
$results = $lqb->from(LdapObjectType::USER)
    ->where($lqb->filter()->startsWith('username', $prefix))
    ->andWhere(['enabled' => true])
    ->getLdapQuery()
    ->getResult();

$users = [];
foreach ($results as $result) {
    $users[] = $result->toArray();
}

print_r($users);