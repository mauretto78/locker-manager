<?php
/**
 * This file is part of the LockerManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
