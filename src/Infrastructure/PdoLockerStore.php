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

use Cocur\Slugify\Slugify;
use LockerManager\Domain\Lock;
use LockerManager\Infrastructure\Exception\ExistingKeyException;
use LockerManager\Infrastructure\Exception\InvalidArgumentException;
use LockerManager\Infrastructure\Exception\LockingKeyException;
use LockerManager\Infrastructure\Exception\NotExistingKeyException;

class PdoLockerStore implements LockerStoreInterface
{
    const LOCKERSTORE_TABLE_NAME = 'lockerstore';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * PdoLockerStore constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->createSchema();
    }

    /**
     * createSchema
     */
    private function createSchema()
    {
        $query = "CREATE TABLE IF NOT EXISTS `".self::LOCKERSTORE_TABLE_NAME."` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `key` varchar(255) NOT NULL UNIQUE,
          `body` text DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->pdo->exec($query);
    }

    /**
     * @param Lock $lock
     */
    public function acquire(Lock $lock)
    {
        $sql = 'INSERT INTO `'.self::LOCKERSTORE_TABLE_NAME.'` (
                    `key`,
                    `body`
                  ) VALUES (
                    :key,
                    :body
            )';

        $data = [
            'key' => $lock->key(),
            'body' => serialize($lock)
        ];

        $this->executeQueryInATransaction($sql, $data);
    }

    /**
     * @param $sql
     * @param array|null $data
     */
    private function executeQueryInATransaction($sql, array $data = null)
    {
        try {
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare($sql);

            if ($data) {
                foreach ($data as $key => &$value){
                    $stmt->bindParam(':'.$key, $value);
                }
            }

            $stmt->execute();

            $this->pdo->commit();
        } catch(\PDOException $e){
            $this->pdo->rollBack();

            throw $e;
        }
    }

    /**
     * clear all locks
     */
    public function clear()
    {
        $sql = 'DELETE FROM `'.self::LOCKERSTORE_TABLE_NAME.'`';

        $this->executeQueryInATransaction($sql);
    }

    /**
     * @param $key
     * @throws NotExistingKeyException
     */
    public function delete($key)
    {
        if (!$this->exists($key)) {
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        $key = (new Slugify())->slugify($key);
        $sql = 'DELETE FROM `'.self::LOCKERSTORE_TABLE_NAME.'` WHERE `key` = :key';

        $data = [
            'key' => $key
        ];

        $this->executeQueryInATransaction($sql, $data);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        $key = (new Slugify())->slugify($key);
        $sql = 'SELECT id FROM `'.self::LOCKERSTORE_TABLE_NAME.'` WHERE `key` = :key';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        return ($stmt->rowCount() > 0) ? true : false;
    }

    /**
     * @param $key
     * @return mixed
     * @throws NotExistingKeyException
     */
    public function get($key)
    {
        if (!$this->exists($key)) {
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        $key = (new Slugify())->slugify($key);
        $query = 'SELECT
                  `key`,
                  `body`
                FROM `'.self::LOCKERSTORE_TABLE_NAME.'` 
                WHERE `key` = :key';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        $row = $stmt->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return unserialize($row[0]['body']);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $query = 'SELECT
                  `key`,
                  `body`
                FROM `'.self::LOCKERSTORE_TABLE_NAME.'`';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $key
     * @param $payload
     *
     * @throws NotExistingKeyException
     */
    public function update($key, $payload)
    {
        if (!$this->exists($key)) {
            throw new NotExistingKeyException(sprintf('The key "%s" does not exists.', $key));
        }

        /** @var Lock $lock */
        $lock = $this->get($key);
        $lock->update($payload);

        $sql = "UPDATE `".self::LOCKERSTORE_TABLE_NAME."` 
                SET `body` = :lock 
                WHERE `key` = :key";

        $data = [
            'key' => $lock->key(),
            'lock' => serialize($lock)
        ];

        $this->executeQueryInATransaction($sql, $data);
    }
}
