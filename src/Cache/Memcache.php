<?php

namespace Solar\Cache;

use Solar\Object\AbstractSingletonFactory;

class Memcache extends AbstractSingletonFactory
{
    /**
     * @var string
     */
    protected string $listKey;

    /**
     * @var \Memcache
     */
    protected \Memcache $memcache;

    /**
     * @param array $servers
     */
    public function __construct(array $servers)
    {
        $this->memcache = new \Memcache();

        foreach ($servers as $server)
            $this->addServer($server['host'], $server['port']);

        $this->listKey = $this->generateListKey();
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function add(string $key, $var, int $expire = 0): bool
    {
        if ($this->memcache->add($key, $var, MEMCACHE_COMPRESSED, $expire))
        {
            $this->addListKey($key);

            return true;
        }

        return false;
    }

    /**
     * @param string $host
     * @param int $port
     * @return bool
     */
    public function addServer(string $host, int $port): bool
    {
        return $this->memcache->addServer($host, $port);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        if ($this->memcache->delete($key))
        {
            $this->deleteListKey($key);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        return $this->memcache->flush();
    }

    /**
     * @param string $key
     * @param callable|null $callback
     * @param int $expire
     * @return array|false|mixed|string
     */
    public function get(string $key, callable $callback = null, int $expire = 0)
    {
        if ($key === $this->listKey)
            return false;

        $var = $this->memcache->get($key);

        if ($var !== false || $callback === null)
            return $var;

        $var = $callback();

        $this->set($key, $var, $expire);

        return $var;
    }

    /**
     * @param array|null $parameters
     * @return Memcache
     * @throws \Exception
     */
    public static function getInstance(array $parameters = null): Memcache
    {
        return parent::getInstance($parameters);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function listKeys(int $limit = 0, int $offset = 0): array
    {
        $keyList = $this->getKeyList();

        $keys = array_keys($keyList);

        if ($limit)
            $keys = array_splice($keys, $offset, $limit);

        return $keys;
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $var, int $expire = 0): bool
    {
        if ($this->memcache->set($key, $var, MEMCACHE_COMPRESSED, $expire))
        {
            $this->addListKey($key);

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param int $expire
     * @return int
     */
    final protected function addListKey(string $key, int $expire = 0): int
    {
        $keyList = $this->getKeyList();

        $keyList[$key] = $expire ? $expire + time() : $expire;

        $this->setKeyList($keyList);

        return count($keyList);
    }

    /**
     * @param string $key
     * @return int
     */
    final protected function deleteListKey(string $key): int
    {
        $keyList = $this->getKeyList();

        unset($keyList[$key]);

        $this->setKeyList($keyList);

        return count($keyList);
    }

    /**
     * @return string
     */
    protected function generateListKey(): string
    {
        $parts = explode('\\', get_called_class());

        $parts[] = 'key_list';

        return strtolower(implode('_', $parts));
    }

    /**
     * @return array|false
     */
    final protected function getKeyList()
    {
        $keyList = $this->memcache->get($this->listKey);

        if (is_array($keyList))
            $this->cleanKeyList($keyList);
        else
            $keyList = [];

        return $keyList;
    }

    /**
     * @param array $keyList
     * @return bool
     */
    final protected function setKeyList(array $keyList): bool
    {
        return $this->memcache->set($this->listKey, $keyList);
    }

    /**
     * @param array $keyList
     * @return void
     */
    private function cleanKeyList(array &$keyList)
    {
        $count = count($keyList);

        $time = time();

        foreach ($keyList as $key => $expire)
            if ($expire && $expire < $time)
                unset($keyList[$key]);

        if ($count > count($keyList))
            $this->setKeyList($keyList);
    }
}