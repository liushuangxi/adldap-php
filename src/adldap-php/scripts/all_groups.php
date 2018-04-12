<?php
require_once "../../../vendor/autoload.php";

use Liushuangxi\AdLdap\AdGroup;

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

$groups = (new AdGroup($config))->all();

print_r($groups);