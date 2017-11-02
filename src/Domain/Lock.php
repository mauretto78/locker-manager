<?php

namespace LockerManager\Domain;

use Cocur\Slugify\Slugify;
use Ramsey\Uuid\Uuid;

class Lock
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $key;

    /**
     * @var
     */
    private $payload;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $modified_at;

    /**
     * Lock constructor.
     * @param $key
     * @param $payload
     */
    public function __construct(
        $key,
        $payload
    )
    {
        $this->id = Uuid::uuid4();
        $this->key = $key;
        $this->payload = $payload;
        $this->created_at = new \DateTime();
        $this->modified_at = new \DateTime();
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function key()
    {
        return (new Slugify())->slugify($this->key);
    }

    /**
     * @return mixed
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return \DateTime
     */
    public function createdAt()
    {
        return $this->created_at;
    }

    /**
     * @return \DateTime
     */
    public function modifiedAt()
    {
        return $this->modified_at;
    }

    public function update($payload)
    {
        $this->payload = $payload;
        $this->modified_at = new \DateTime();
    }
}
