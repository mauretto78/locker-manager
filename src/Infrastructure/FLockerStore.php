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

        if (!is_dir($lockPath)) {
            mkdir($lockPath, 0755, true);
        }

        if (!is_writable($lockPath)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $lockPath));
        }

        $this->lockPath = $lockPath;
    }

    /**
     * @param Lock $lock
     * @throws ExistingKeyException
     */
    public function acquire(Lock $lock)
    {
        $key = $lock->key();
        $fileName = $this->getLockPath($key);

        if(file_exists($fileName)){
            throw new ExistingKeyException(sprintf('The key "%s" already exists.', $key));
        }

        $this->save($lock, $fileName);
    }

    /**
     * @param Lock $lock
     * @param $fileName
     * @throws LockingKeyException
     */
    private function save(Lock $lock, $fileName)
    {
        $file = @fopen($fileName,'w');

        if (flock($file,LOCK_EX)) {
            fwrite($file, serialize($lock));
            flock($file,LOCK_UN);
        } else {
            throw new LockingKeyException(sprintf('Error locking file "%s".', $lock->key()));
        }

        fclose($file);
    }

    /**
     * clear all locks
     */
    public function clear()
    {
        $files = scandir($this->lockPath);

        foreach($files as $file){
            if(is_file($this->lockPath.$file)) {
                unlink($this->lockPath.$file);
            }
        }
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

        unlink($this->getLockPath($key));
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        if(!@fopen($this->getLockPath($key),'r')){
            return false;
        }

        return true;
    }

    /**
     * @param $key
     * @return string
     */
    private function getLockPath($key)
    {
        return $this->lockPath.$key.'.lock';
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

        return unserialize(file_get_contents($this->getLockPath($key)));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $files = scandir($this->lockPath);
        $locks = [];

        unset($files[0]);
        unset($files[1]);

        foreach ($files as $lock){
            $locks[] = str_replace('.lock', '', $lock);
        }

        return array_values($locks);
    }

    /**
     * @param $key
     * @param $payload
     * @throws LockingKeyException
     * @throws NotExistingKeyException
     */
    public function update($key, $payload)
    {
        $fileName = $this->getLockPath($key);

        if(!file_exists($fileName)){
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        /** @var Lock $lock */
        $lock = $this->get($key);
        $lock->update($payload);

        $this->save($lock, $fileName);
    }
}
