<?php

namespace LockerManager\Tests;

use LockerManager\Application\LockerManager;
use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\FLockerStore;
use LockerManager\Infrastructure\RedisLockerStore;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class LockerManagerTest extends TestCase
{
    /**
     * @var array
     */
    private $lockerManagers;

    protected function setUp()
    {
        $redisLockerStore = new RedisLockerStore(new Client());
        $fLockerStore = new FLockerStore('var/lock/');

        $this->lockerManagers = [
            'RedisLockerStore' => new LockerManager($redisLockerStore),
            'FLockerStore' => new LockerManager($fLockerStore),
        ];
    }

    /**
     * @test
     */
    public function it_should_return_false_if_a_not_existing_lock_exists()
    {
        /** @var LockerManager $lockerManager */
        foreach ($this->lockerManagers as $lockerManager){
            $this->assertFalse($lockerManager->exists('a not existing key'));
        }
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\ExistingKeyException
     */
    public function it_should_throw_ExistingKeyException_if_try_to_acquire_an_existing_key_with_RedisLockerStore()
    {
        $lock = new Lock(
            'Existing Lock',
            'simple payload'
        );

        $this->lockerManagers['RedisLockerStore']->acquire($lock);
        $this->lockerManagers['RedisLockerStore']->acquire($lock);
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\ExistingKeyException
     */
    public function it_should_throw_ExistingKeyException_if_try_to_acquire_an_existing_key_with_FLockerStore()
    {
        $lock = new Lock(
            'Existing Lock',
            'simple payload'
        );

        $this->lockerManagers['FLockerStore']->acquire($lock);
        $this->lockerManagers['FLockerStore']->acquire($lock);
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_delete_an_not_existing_key_with_RedisLockerStore()
    {
        $this->lockerManagers['RedisLockerStore']->delete('not-existing-key');
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_delete_an_not_existing_key_with_FLockerStore()
    {
        $this->lockerManagers['FLockerStore']->delete('not-existing-key');
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_get_an_not_existing_key_with_RedisLockerStore()
    {
        $this->lockerManagers['RedisLockerStore']->get('not-existing-key');
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_get_an_not_existing_key_with_FLockerStore()
    {
        $this->lockerManagers['FLockerStore']->get('not-existing-key');
    }

    /**
     * @test
     */
    public function it_should_write_and_update_and_get_and_delete_a_lock()
    {
        /** @var LockerManager $lockerManager */
        foreach ($this->lockerManagers as $lockerManager){
            $payload = [
                'name' => 'John Doe',
                'email' => 'john.doe@gmail.com',
                'age' => 33,
            ];

            $lock = new Lock(
                'Sample Lock',
                $payload
            );

            $lockerManager->acquire($lock);

            $this->assertEquals($lock, $lockerManager->get('sample-lock'));

            $newPayload = [
                'name' => 'Maria Dante',
                'email' => 'maria.dante@gmail.com',
                'age' => 31,
            ];

            $lockerManager->update(
                'sample-lock',
                $newPayload
            );

            $this->assertEquals($lock->id(), $lockerManager->get('sample-lock')->id());
            $this->assertEquals($lock->createdAt()->getTimestamp(), $lockerManager->get('sample-lock')->createdAt()->getTimestamp());
            $this->assertEquals($newPayload, $lockerManager->get('sample-lock')->payload());

            $lockerManager->delete('sample-lock');
        }
    }

    /**
     * @test
     */
    public function it_should_return_the_correct_lock_count()
    {
        /** @var LockerManager $lockerManager */
        foreach ($this->lockerManagers as $lockerManager){
            $lock1 = new Lock(
                'Sample Lock 1',
                [
                    'name' => 'John Doe',
                    'email' => 'john.doe@gmail.com',
                    'age' => 33,
                ]
            );

            $lock2 = new Lock(
                'Sample Lock 2',
                [
                    'name' => 'Maria Dante',
                    'email' => 'maria.dante@gmail.com',
                    'age' => 31,
                ]
            );

            $lockerManager->delete('existing-lock');

            $lockerManager->acquire($lock1);
            $lockerManager->acquire($lock2);

            $this->assertCount(2, $lockerManager->getAll());

            $lockerManager->clear();

            $this->assertCount(0, $lockerManager->getAll());
        }
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_update_an_not_existing_key_with_RedisLockerStore()
    {
        $this->lockerManagers['RedisLockerStore']->update('not-existing-key', 'payload');
    }

    /**
     * @test
     * @expectedException \LockerManager\Infrastructure\Exception\NotExistingKeyException
     */
    public function it_should_throw_NotExistingKeyException_if_try_to_update_an_not_existing_key_with_FLockerStore()
    {
        $this->lockerManagers['FLockerStore']->update('not-existing-key', 'payload');
    }
}
