<?php
/**
 * This file is part of the LockerManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @param Lock $lock
     * @return mixed
     */
    public function acquire(Lock $lock)
    {
        return $this->store->acquire($lock);
    }

    /**
     * @return mixed
     */
    public function clear()
    {
        return $this->store->clear();
    }

    /**
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->store->delete($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function exists($key)
    {
        return $this->store->exists($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->store->get($key);
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->store->getAll();
    }

    /**
     * @param $key
     * @param $payload
     * @return mixed
     */
    public function update($key, $payload)
    {
        return $this->store->update($key, $payload);
    }
}
