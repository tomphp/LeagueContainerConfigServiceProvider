# Config Service Provider

[![Build Status](https://api.travis-ci.org/tomphp/config-service-provider.svg)](https://api.travis-ci.org/tomphp/config-service-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tomphp/config-service-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tomphp/config-service-provider/?branch=master)

This package contains a simple service provider for the League Of Extraordinary
Packages' [Container](https://github.com/thephpleague/container) package.

The purpose of this service provider is to take an array and add each item in
the array to the container as a value. These values can then easily be used as
dependencies of other services.

## Installation

Installation can be done easily using composer:

```
$ composer require tomphp/config-service-provider
```

## Example Usage

```php
<?php

use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use TomPHP\ConfigServiceProvider\ConfigServiceProvider;

class DatabaseConnectionProvider extends AbstractServiceProvider
{
    protected $provides = [
        'database_connection',
    ];
    
    public function register()
    {
        $this->container->share('database_connection', function () {
            return new DatabaseConnection(
                $this->container->get('config.db.name'),
                $this->container->get('config.db.username'),
                $this->container->get('config.db.password')
            );
        });
    }
}

$appConfig = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ]
];

$container = new Container();

$container->addServiceProvider(ConfigServiceProvider::fromConfig($appConfig));
$container->addServiceProvider(new DatabaseConnectionProvider());

$db = $container->get('database_connection');
```

* Each item in the config array is added as a separate entry into the
  container.
* Each item name is has a prefix added to it. The prefix defaults to `config`.
* If an item contains an sub-array, each of that array's are added separately
  with a name made up of the first array key, followed by a separator (defaults
  to `.`) followed by the key from the second array.

### Reading Files From Disk

Instead of providing the config as an array, you can also provide a list of 
file filesystem pattern matches to the `fromFiles` constructor.

```php
$container->addServiceProvider(ConfigServiceProvider::fromFiles([
    'config_dir/*.global.php',
    'json_dir/*.json',
    'config_dir/*.local.php',
]));
```

#### Merging

Patterns will be matched in the order the appear in the array. As files are
read their config will be merged in, overwriting any matching keys.

#### Supported Formats

Current `.php` and `.json` files are supported. PHP config files **must**
return a PHP array.

### Accessing A Whole Sub-Array

Whole sub-arrays are also made available for cases where you want them instead
of individual values. Altering the previous example, this is also possible
instead:

```php
class DatabaseConnectionProvider extends AbstractServiceProvider
{
    protected $provides = [
        'database_connection',
    ];
    
    public function register()
    {
        $this->container->share('database_connection', function () {
            /* @var array $config */
            $config = $this->container->get('config.db');
        
            return new DatabaseConnection(
                $config['name'],
                $config['username'],
                $config['password']
            );
        });
    }
}
```

### Configuring Inflectors

It is also possible to set up
[Inflectors](http://container.thephpleague.com/inflectors/) by adding an
`inflectors` key to the config.

```php
$appConfig = [
    'inflectors' => [
        LoggerAwareInterface::class => [
            'setLogger' => ['Some\Logger']
        ]
    ]
];
```

### Extra Settings

You can provide an array of extra settings as a second parameter to
`TomPHP\ConfigServiceProvider\ConfigServiceProvider::fromConfig()`.

Current valid keys are:

| Name        | Effect                                        |
|-------------|-----------------------------------------------|
| `prefix`    | Changes `config` prefix given to config keys. |
| `separator` | Changes `.` separator in config keys.         |

Example:

```php
$provider = ConfigServiceProvider::fromConfig($appConfig, [
    'prefix'    => 'settings',
    'separator' => '/'
]);
```
