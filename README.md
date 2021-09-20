Description
===========

This bundle brings a MQTT client class for use in your Symfony 5+ apps. It's built with [React](https://reactphp.org). This bundle is mainly based on a project by [alexmorbo](https://github.com/alexmorbo/react-mqtt), itself based on an earlier library by [oliverlorenz](https://github.com/oliverlorenz/phpMqttClient). Many thanks to them.

This project is licensed under the GNU Lesser General Public Licence (LGPL), version 3 or later, of the Free Software Foundation. See [the license text](LICENSE.md) for details. 

**NOT SUITABLE for general purpose YET! Consider yourself warned.**

TODO:
- finish the QoS workflows
- finish the tests
- add a Travis configuration
- ...

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require meteo-concept/react-mqtt-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require meteo-concept/react-mqtt-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    MeteoConcept\ReactMqttBundle\MeteoConceptReactMqttBundle::class => ['all' => true],
];
```