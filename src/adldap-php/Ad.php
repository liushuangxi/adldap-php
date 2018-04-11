<?php

namespace Liushuangxi\AdLdap;

use LdapTools\Configuration;
use LdapTools\LdapManager;
use LdapTools\Object\LdapObjectType;

/**
 * Class Ad
 *
 * @package Liushuangxi\AdLdap
 */
class Ad
{
    /**
     * @var LdapManager|null
     */
    public $ldap = null;

    /**
     * @var null
     */
    protected $logger = null;

    /**
     * 数据类型
     *
     * @var LdapObjectType
     */
    protected $objectType = '';

    /**
     * 查询单个方法
     *
     * @var string
     */
    protected $funcFindOne = '';

    /**
     * 查询单个主键
     *
     * @var string
     */
    protected $keyFindOne = '';

    /**
     * Ad constructor.
     * http://www.phpldaptools.com/reference/Main-Configuration/
     *
     * @param array $config
     * config_file
     * logger
     *
     * @throws \Exception
     */
    public function __construct($config = [])
    {
        if (!isset($config['config_file'])
            || !file_exists($config['config_file'])
            || !is_readable($config['config_file'])
        ) {
            throw new \Exception('config_file配置文件不存在或无法读取');
        }

        if (!isset($config['logger'])
            || !method_exists($config['logger'], 'logInfo')
            || !method_exists($config['logger'], 'logError')
        ) {
            throw new \Exception('logger日志类不存在，或logInfo/logError方法不存在');
        }

        $tmp = (new Configuration())->load($config['config_file']);

        $this->ldap = new LdapManager($tmp);
        $this->logger = $config['logger'];
    }

    /**
     * http://www.phpldaptools.com/tutorials/Creating-LDAP-Objects/
     *
     * @param string $container
     * @param array $attributes
     *
     * @return bool
     */
    public function create($container = '', $attributes = [])
    {
        try {
            $this->ldap
                ->createLdapObject()
                ->create($this->objectType)
                ->in($container)
                ->with($attributes)
                ->execute();

            return true;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return false;
        }
    }

    /**
     * http://www.phpldaptools.com/tutorials/Modifying-LDAP-Objects/
     *
     * @param string $name
     * @param array $attributes
     * @param string $funcFindOne
     *
     * @return bool
     */
    public function change($name = '', $attributes = [], $funcFindOne = '')
    {
        try {
            if (empty($funcFindOne)) {
                $func = $this->funcFindOne;
            } else {
                $func = $funcFindOne;
            }

            $object = $this->ldap
                ->getRepository($this->objectType)
                ->$func($name);

            foreach ($attributes as $key => $value) {
                $object->set($key, $value);
            }

            $this->ldap->persist($object);

            return true;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return false;
        }
    }

    /**
     * http://www.phpldaptools.com/tutorials/Using-the-LDAP-Manager/#moving-ldap-objects
     *
     * @param string $name
     * @param string $newOu
     *
     * @return bool
     */
    public function changeOu($name = '', $newOu = '')
    {
        try {
            $func = $this->funcFindOne;

            $object = $this->ldap
                ->getRepository($this->objectType)
                ->$func($name);

            $this->ldap->move($object, $newOu);

            return true;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return false;
        }
    }

    /**
     * http://www.phpldaptools.com/tutorials/Using-the-LDAP-Manager/#getting-a-repository-object-for-a-ldap-type
     *
     * @param string $name
     * @param bool $allAttributes
     *
     * @return array
     */
    public function get($name = '', $allAttributes = false)
    {
        try {
            if ($allAttributes) {
                $object = $this->all(
                    '*',
                    [
                        $this->keyFindOne => $name
                    ]
                );

                $object = isset($object[0]) ? $object[0] : [];
            } else {
                $func = $this->funcFindOne;

                $object = $this->ldap
                    ->getRepository($this->objectType)
                    ->$func($name)
                    ->toArray();
            }

            return $object;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return [];
        }
    }

    /**
     * http://www.phpldaptools.com/tutorials/Building-LDAP-Queries/
     *
     * @param array $attributes
     * @param array $whereStatements
     *
     * @return array
     */
    public function all($attributes = [], $whereStatements = [])
    {
        try {
            $results = $this->ldap
                ->buildLdapQuery()
                ->from($this->objectType)
                ->select($attributes)
                ->where($whereStatements)
                ->getLdapQuery()
                ->getResult();

            $data = [];
            foreach ($results as $result) {
                $data[] = $result->toArray();
            }

            return $data;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return [];
        }
    }

    /**
     * http://www.phpldaptools.com/tutorials/Using-the-LDAP-Manager/#deleting-ldap-objects
     *
     * @param string $name
     * @param string $funcFindOne
     *
     * @return bool
     */
    public function delete($name = '', $funcFindOne = '')
    {
        try {
            if (empty($funcFindOne)) {
                $func = $this->funcFindOne;
            } else {
                $func = $funcFindOne;
            }

            $object = $this->ldap
                ->getRepository($this->objectType)
                ->$func($name);

            $this->ldap->delete($object);

            return true;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());

            return false;
        }
    }

    /**
     * @return string
     */
    public function getBaseDn()
    {
        return $this->ldap->getConnection()->getConfig()->getBaseDn();
    }

    /**
     * @param $message
     */
    protected function logError($message)
    {
        $this->logger->logError($message);
    }

    /**
     * @param $message
     */
    protected function logInfo($message)
    {
        $this->logger->logInfo($message);
    }
}
