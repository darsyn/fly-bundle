Darsyn's Fly Bundle
====================

A bundle that provides simple integration of the PHP League's Flysystem using service definitions.

License
-------

This project is licensed under [MIT](http://j.mp/mit-license).

Dependencies
------------

This bundle relies on the following components:

- Symfony's [HTTP Kernel](https://github.com/symfony/httpkernel) component (for registering the bundle itself with the [Symfony framework](http://symfony.com)).
- Symfony's [Dependency Injection](https://github.com/symfony/dependencyinjection) component (for registering the bundle's Twig extensions as services).
- Symfony's [Config](https://github.com/symfony/config) component (for loading service definition configuration files).
- [The PHP League](http://thephpleague.com/)'s [Flysystem](http://flysystem.thephpleague.com/) library, which this entire bundle is based upon.

If you wish to run tests, this bundle also requires some development dependencies:

- [Sebastian Bergmann](https://sebastian-bergmann.de/)'s [PHPUnit](http://phpunit.de) for unit testing.
- [SquizLabs](https://www.squizlabs.com/)' [PHP CodeSniffer](http://pear.php.net/package/PHP_CodeSniffer) for reporting PSR-2 coding standards.
- [Pádraic Brady](http://blog.astrumfutura.com/)'s [Humbug](https://github.com/padraic/humbug) for mutation testing.

Installation
------------

Include this bundle as a dependency of your project:

```bash
$ php composer.phar require "darsyn/fly-bundle:~0.1"
```

Next, activate the bundle in your application kernel:

```php
<?php

class AppBundle
{
    // ...
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Darsyn\Bundle\FlyBundle\DarsynFlyBundle,
            // ...
        ];
        // ...
    }
    // ...
}
```

Usage
-----

Define your Flysystem adapters as services (the `Local` adapter is already defined), and tag them to be included in Flysystem's Mount Manager (specifying the protocol).

```yaml
parameters:
    # Put these parameter definitions in your "parameters.yml.dist" file.
    amazon.key: ~
    amazon.secret: ~
    amazon.bucket: ~
    dropbox.access_token: ~
    dropbox.client_identifier: ~

services:

    aws_credentials:
        class:      Aws\Common\Credentials\Credentials
        arguments:  [ %amazon.key%, %amazon.secret% ]
    s3_client:
        class:      Aws\S3\S3Client
        factory:    [ Aws\S3\S3Client, factory ]
        arguments:  [ { credentials: @amazon.credentials } ]
    my_amazon_flyadapter:
        class:      League\Flysystem\AwsS3v3\AwsS3Adapter
        arguments:  [ @s3_client, %amazon.bucket% ]
        tags:
            - { name: darsyn_fly.adapter, protocol: amazon }

    dropbox_client:
        class:      Dropbox\Client
        arguments:  [ %dropbox.access_token%, %dropbox.client_identifier% ]
    my_dropbox_adapter:
        class:      League\Flysystem\Dropbox\DropboxAdapter
        arguments:  [ @dropbox_client ]
        tags:
            - { name: darsyn_fly.adapter, protocol: dropbox }
```

You're all set! Start using the flysystem!

```php
<?php

class YourController extends Controller
{
    public function indexAction()
    {
        $flysystem = $this->container->get('darsyn_fly');
        $flysystem->move(
            'dropbox://Employment/MyCompany/WorkFromHome/MeetingMinutes.doc',
            'amazon://FinancialDepartment/Meetings/2015-05-01/MayDayMinutes.doc'
        );
    }
}
```

Authors and Contributing
------------------------

Current authors include:

- [Zander Baldwin](https://zanderbaldwin.com) <[hello@zanderbaldwin.com](mailto:hello@zanderbaldwin.com)> (on [GitHub](https://github.com/zanderbaldwin "Zander Baldwin on GitHub")).

All contributions are welcome, don't forget to add your name here in the pull request!
