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
          `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
          `key` varchar(255) DEFAULT NULL,
          `payload` varchar(255) DEFAULT NULL,
          `created_at` datetime(6),
          `modified_at` datetime(6),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->pdo->exec($query);
    }

    /**
     * @param Lock $lock
     */
    public function acquire(Lock $lock)
    {
        $uuid = (string) $lock->id();
        $key = $lock->key();
        $payload = serialize($lock->payload());
        $createdAt = $lock->createdAt()->format('Y-m-d H:i:s.u');
        $modifiedAt = $lock->modifiedAt()->format('Y-m-d H:i:s.u');

        $sql = 'INSERT INTO `'.self::LOCKERSTORE_TABLE_NAME.'` (
                    `uuid`,
                    `key`,
                    `payload`,
                    `created_at`,
                    `modified_at`
                  ) VALUES (
                    :uuid,
                    :key,
                    :payload,
                    :created_at,
                    :modified_at
            )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':payload', serialize($payload));
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->bindParam(':modified_at', $modifiedAt);
        $stmt->execute();
    }

    /**
     * clear all locks
     */
    public function clear()
    {
        $sql = 'DELETE FROM `'.self::LOCKERSTORE_TABLE_NAME.'`';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
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
                  `id`,
                  `uuid`,
                  `key`,
                  `payload`,
                  `created_at`,
                  `modified_at`,
                FROM `'.self::LOCKERSTORE_TABLE_NAME.'` 
                WHERE `key` = :key';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':key', $key);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $row[0];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $query = 'SELECT
                  `id`,
                  `uuid`,
                  `key`,
                  `payload`,
                  `created_at`,
                  `modified_at`,
                FROM `'.self::LOCKERSTORE_TABLE_NAME.'` 
                ORDER BY `created_at` ASC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($key, $payload)
    {
        //UPDATE MyGuests SET lastname='Doe' WHERE id=2
    }
}
