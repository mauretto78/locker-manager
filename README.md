# Simple Locker Manager

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mauretto78/locker-manager/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mauretto78/locker-manager/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b0278e2b5b9b4feb8f9078326d3721fd)](https://www.codacy.com/app/mauretto78/locker-manager?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/locker-manager&amp;utm_campaign=Badge_Grade)
[![license](https://img.shields.io/github/license/mauretto78/simple-event-store-manager.svg)]()
[![Packagist](https://img.shields.io/packagist/v/mauretto78/simple-event-store-manager.svg)]()

This library is suitable for you if you need to simple lock system.

## Installation

```
composer require mauretto78/locker-manager
```

## Basic Usage

### Instantiate LockerManager

To instantiate the LockerManager you must inject an implementation of `LockerStoreInterface`:

```php
use LockerManager\Application\LockerManager;
use LockerManager\Infrastructure\FLockerStore;
use LockerManager\Infrastructure\RedisLockerStore;
use Predis\Client;

// 1. Redis implementation uses PRedis Client
$redisLockerStore = new RedisLockerStore(new Client());
$lockerManager = new LockerManager($redisLockerStore);

// 2. Filesystem implementation
$fLockerStore = new FLockerStore('var/lock/');
$lockerManager = new LockerManager($fLockerStore);

```

### Acquire, get, delete and update a lock

This library uses [Slugify](https://github.com/cocur/slugify) to save lock keys. 

Once a key is saved, this will be unique. An `ExistingKeyException` will be thrown if you try to save a lock with the same key.

Please consider this example:

```php
// ..

// acquire
$lock = new Lock(
    'Sample Lock',
    [
        'name' => 'John Doe',
        'email' => 'john.doe@gmail.com',
        'age' => 33,
    ]
);

$lockerManager->acquire($lock);

// get a lock
$sampleLock = $lockerManager->get('Sample Lock');

// delete a lock
$lockerManager->delete('Sample Lock');

// update a lock
$lockerManager->update(
    'Sample Lock',
    [
        'name' => 'Maria Dante',
        'email' => 'maria.dante@gmail.com',
        'age' => 31,
    ]
);

```

### Get all locks

To get all saved locks as an array:

```php
// ..

$lockerManager->getAll();
```

### Clear all locks

To clear all locks:

```php
// ..

$lockerManager->clear();
```

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/locker-manager/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
