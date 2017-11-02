<?php

namespace LockerManager\Infrastructure;

use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\Exception\ExistingKeyException;
use LockerManager\Infrastructure\Exception\NotExistingKeyException;
use Predis\Client;

class RedisLockerStore implements LockerStoreInterface
{
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

    public function acquire(Lock $lock)
    {
        $key = $lock->key();

        if($this->redis->exists($key) !== 0){
            throw new ExistingKeyException(sprintf('The key "%s" already exists.', $key));
        }

        $this->redis->set(
            $key,
            serialize($lock)
        );
    }

    public function delete($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        $this->redis->del($key);
    }

    public function exists($key)
    {
        if($this->redis->exists($key) === 0){
            return false;
        }

        return true;
    }

    public function get($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        return unserialize($this->redis->get($key));
    }

    public function update($key, $payload)
    {
        if($this->redis->exists($key) === 0){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        /** @var Lock $lock */
        $lock = $this->get($key);
        $lock->update($payload);

        $this->redis->set(
            $key,
            serialize($lock)
        );
    }
}
