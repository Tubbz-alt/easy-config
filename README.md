# Common Config
Simple PHP library to read from yaml files.

Usage
------------

Place the following code in the constructor of your class or the superclass:

```php
use Common\Config;

$config = Config::getInstance();
$config->setUseCache(false); //Default is true, so no need to specify if you want to use APC
$config->loadConfig(
    array(
        __DIR__ . '/../config/environment.yml',
        __DIR__ . '/../config/properties.yml'
    )
);
```

Somewhere else in your code you can load a config value, by calling $config->fetch() and the headings of the value:

```
$config = Config::getInstance();
$var = $config->fetch('heading', 'subheading');               //Fetches value under [heading][subheading]
$var = $config->fetch('heading', 'subheading', 'subheading'); //Fetches value under [heading][subheading][subheading]
$var = $config->fetch();                                      //Fetches the whole config
```

You can specify as many keys as you want when fetching config values.
