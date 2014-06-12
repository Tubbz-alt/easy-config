# Common Config
Simple PHP library to read from yaml files.

Usage
------------

```php
use Common\Config;

$config = Config::getInstance();
$config->setUseCache(false); // by default it's true, so no need to specify
$config->loadConfig(
    array(
        __DIR__ . '/../config/environment.yml',
        __DIR__ . '/../config/properties.yml'
    )
);

// somewhere else
$config = Config::getInstance();
$config->fetch('some', 'keys');
$config->fetch('some', 'keys', 'more');
$config->fetch();
```
You can specify as many keys as you want when fetching config values.
