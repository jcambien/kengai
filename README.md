# Kengai

![Kengai](https://raw.github.com/jcambien/kengai/master/kengai.png)

Kengai is a simple but powerful configuration tool for your PHP applications.

This is a work in progress, do NOT use it in production environment.
Also, please note that documentation is not done but in progress.

Anybody is welcome to help and contribute to this project.


## About this project

The idea is simple :
- Manage configurations from any formats with a tree structure (YAML, JSON, INI, etc.) in a simple and optimized way.
- All data are stored in a common tree ordered by namespaces.
- The possibility to use any cache system (like APC).
- Event system for advanced use cases, powered by the Symfony 2 event dispatcher component. 


## Basics

Here is a example of a basic usage:

```php
<?php

use Kengai\Manager as Kengai;
use Kengai\SourceReader\YAML;
use Kengai\SourceReader\JSON;

// Create a Kengai manager instance
$kengai = new Kengai();
 
// Import your configuration files
$kengai->add(new YAML('myconfig.yml', 'myconfig'));
 
// Example of JSON support
$kengai->add(new JSON('composer.json', 'composer'));
 
// Fetch data
$kengai->fetch();
```

As you can see in this example, all sources are registered with the `add()` method before `fetch()` call.
When all sources are registered, you can use `fetch()` to proceed the importing process.
After this, you can not use `add()` anymore!
In the case of a cache manager is registered, the importing process will first verify the cache freshness from your cache manager, then just restore it or reload all sources if needed.

Here's is a configuration reading example :

```php
$bar = $kengai->get('foo.bar');

var_dump($var);
```


## Cache support

You can use any cache system by using CacheManagerInterface :

```php
<?php

use Kengai\Manager as Kengai;
use Kengai\SourceReader\YAML;
use Kengai\CacheManager\APC;

// Create a Kengai manager instance with APC support
$kengai = new Kengai(new APC());
 
...

// Fetch data
$kengai->fetch();
```
