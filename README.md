# Simple Locker Manager

This library is suitable for you if you need to simple lock system.

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



## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/locker-manager/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
