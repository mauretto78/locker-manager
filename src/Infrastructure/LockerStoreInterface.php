<?php

namespace LockerManager\Infrastructure;

use LockerManager\Domain\Lock;

interface LockerStoreInterface
{
    public function acquire(Lock $lock);

    public function delete($key);

    public function exists($key);

    public function get($key);

    public function update($key, $payload);
}
