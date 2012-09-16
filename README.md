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

Here is a example of a basic use case:

```php
<?php

use Kengai\Manager as Kengai;
use Kengai\SourceReader\YAML;
 
// Create a Kengai manager instance
$kengai = new Kengai();
 
// Import your configuration files
$kengai->add(new YAML('myconfig.yml', 'myconfig'));
 
// Example of JSON support
$kengai->add(new JSON('composer.json', 'composer'));
 
// Fetch data
$kengai->fetch();