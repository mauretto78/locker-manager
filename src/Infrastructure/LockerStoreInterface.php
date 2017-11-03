<?php

namespace LockerManager\Infrastructure;

use LockerManager\Domain\Lock;

interface LockerStoreInterface
{
    public function acquire(Lock $lock);

    public function clear();

    public function delete($key);

    public function exists($key);

    public function get($key);

    public function getAll();

    public function update($key, $payload);
}
