<?php

namespace LockerManager\Infrastructure;

use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\Exception\ExistingKeyException;
use LockerManager\Infrastructure\Exception\NotExistingKeyException;
use Predis\Client;

class RedisLockerStore implements LockerStoreInterface
{
    const LOCK_LIST_NAME = 'lock-list';

    /**
     * @var Client
     */
    private $redis;

    /**
     * RedisLockerStore constructor.
     * @param Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param Lock $lock
     * @throws ExistingKeyException
     */
    public function acquire(Lock $lock)
    {
        $key = $lock->key();

        if($this->redis->hget(self::LOCK_LIST_NAME, $key)){
            throw new ExistingKeyException(sprintf('The key "%s" already exists.', $key));
        }

        $this->saveLock(
            $key,
            $lock
        );
    }

    /**
     * @param $key
     * @param Lock $lock
     */
    private function saveLock($key, Lock $lock)
    {
        $this->redis->hset(
            self::LOCK_LIST_NAME,
            $key,
            serialize($lock)
        );
    }

    /**
     * clear all locks
     */
    public function clear()
    {
        $this->redis->del([self::LOCK_LIST_NAME]);
    }

    /**
     * @param $key
     * @throws NotExistingKeyException
     */
    public function delete($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        $this->redis->hdel(self::LOCK_LIST_NAME, $key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return ($this->redis->hget(self::LOCK_LIST_NAME, $key)) ? true : false;
    }

    /**
     * @param $key
     * @return mixed
     * @throws NotExistingKeyException
     */
    public function get($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        return unserialize($this->redis->hget(self::LOCK_LIST_NAME, $key));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->redis->hgetall(self::LOCK_LIST_NAME);
    }

    /**
     * @param $key
     * @param $payload
     * @throws NotExistingKeyException
     */
    public function update($key, $payload)
    {
        if(!$this->redis->hget(self::LOCK_LIST_NAME, $key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        /** @var Lock $lock */
        $lock = $this->get($key);
        $lock->update($payload);

        $this->saveLock(
            $key,
            $lock
        );
    }


}
