<?php

namespace LockerManager\Application;

use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\LockerStoreInterface;

class LockerManager
{
    /**
     * @var LockerStoreInterface
     */
    private $store;

    /**
     * LockerManager constructor.
     * @param LockerStoreInterface $store
     */
    public function __construct(LockerStoreInterface $store)
    {
        $this->store = $store;
    }

    public function acquire(Lock $lock)
    {
        return $this->store->acquire($lock);
    }

    public function delete($key)
    {
        return $this->store->delete($key);
    }

    public function exists($key)
    {
        return $this->store->exists($key);
    }

    public function get($key)
    {
        return $this->store->get($key);
    }

    public function update($key, $payload)
    {
        return $this->store->update($key, $payload);
    }
}
