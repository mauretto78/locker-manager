<?php

namespace LockerManager\Infrastructure;

use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\Exception\ExistingKeyException;
use LockerManager\Infrastructure\Exception\InvalidArgumentException;
use LockerManager\Infrastructure\Exception\LockingKeyException;
use LockerManager\Infrastructure\Exception\NotExistingKeyException;

class FLockerStore implements LockerStoreInterface
{
    /**
     * @var null|string
     */
    private $lockPath;

    /**
     * FLockerStore constructor.
     * @param null $lockPath
     */
    public function __construct($lockPath = null)
    {
        if (null === $lockPath) {
            $lockPath = sys_get_temp_dir();
        }

        if (!is_dir($lockPath) || !is_writable($lockPath)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $lockPath));
        }

        $this->lockPath = $lockPath;
    }

    public function acquire(Lock $lock)
    {
        $key = $lock->key();
        $fileName = $this->getLockPath($key);

        if(file_exists($fileName)){
            throw new ExistingKeyException(sprintf('The key "%s" already exists.', $key));
        }

        $file = @fopen($fileName,'x');
        fwrite($file, serialize($lock));
        fclose($file);
    }

    public function delete($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        unlink($this->getLockPath($key));
    }

    public function exists($key)
    {
        if(!@fopen($this->getLockPath($key),'r')){
            return false;
        }

        return true;
    }

    private function getLockPath($key)
    {
        return $this->lockPath.$key.'.lock';
    }

    public function get($key)
    {
        if(!$this->exists($key)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        return unserialize(file_get_contents($this->getLockPath($key)));
    }

    public function update($key, $payload)
    {
        $fileName = $this->getLockPath($key);

        if(!file_exists($fileName)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        /** @var Lock $lock */
        $lock = $this->get($key);
        $lock->update($payload);

        $file = @fopen($fileName,'w+');

        if (flock($file,LOCK_EX)) {
            fwrite($file, serialize($lock));
            flock($file,LOCK_UN);
        } else {
            throw new LockingKeyException(sprintf('Error locking file "%s".', $key));
        }

        fclose($file);
    }
}
