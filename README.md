# Darsyn's Fly Bundle

A bundle that provides simple integration of the PHP League's Flysystem using service definitions.

**Note:** this bundle was conceived out of a desire to define multiple adapters and plugins through Symfony's *service
container* instead of a bulky config section (defining filesystems by enabling bundles without editing the
application configuration). I highly recommend using the
[OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle) as it is [official recongnised as the Symfony
bridging package by Flysystem](http://flysystem.thephpleague.com/integrations/).

This bundle prefers to use the [MountManager](http://flysystem.thephpleague.com/mount-manager) rather than individual
filesystems. As such, cache providers and plugins are applied to the entire mount system, rather than the filesystems
themselves.

## License

This project is licensed under [MIT](http://j.mp/mit-license).

## Dependencies

This bundle relies on the following components:

- Symfony's [HTTP Kernel](https://github.com/symfony/httpkernel) component (for registering the bundle itself with the
  [Symfony framework](http://symfony.com)).
- Symfony's [Dependency Injection](https://github.com/symfony/dependencyinjection) component (for registering
  Flysystem's MountManager  as services).
- Symfony's [Config](https://github.com/symfony/config) component (for loading service definition configuration files).
- [The PHP League](http://thephpleague.com/)'s [Flysystem](http://flysystem.thephpleague.com/) library, which this
  entire bundle is based upon.

If you wish to run tests, this bundle also requires some development dependencies:

- [Sebastian Bergmann](https://sebastian-bergmann.de/)'s [PHPUnit](http://phpunit.de) for unit testing.
- [SquizLabs](https://www.squizlabs.com/)' [PHP CodeSniffer](http://pear.php.net/package/PHP_CodeSniffer) for reporting
  PSR-2 coding standards.
- [Pádraic Brady](http://blog.astrumfutura.com/)'s [Humbug](https://github.com/padraic/humbug) for mutation testing.

### Adapter Dependencies

This bundle does not include dependencies for each filesystem type, you must require those in your `composer.json`
yourself;

- To access an [Amazon S3](http://aws.amazon.com/s3) filesystem, require `league/flysystem-aws-s3-v2` adapter.
- To access an [Azure](http://azure.microsoft.com/en-gb/services/storage/) filesystem, require `league/flysystem-azure`
  adapter.
- To access a [Copy.com](https://www.copy.com) filesystem, require `league/flysystem-copy` adapter.
- To access a [Dropbox](https://www.dropbox.com) filesystem, require `league/flysystem-dropbox` adapter.
- To access a [GridFS](http://docs.mongodb.org/manual/core/gridfs) filesystem, require the `league/flysystem-gridfs`
  adapter.
- To access a [Rackspace](https://developer.rackspace.com/) filesystem, require the `league/flysystem-rackspace`
  adapter.
- To access an SFTP filesystem, require the `league/flysystem-sftp` adapter.
- To access a WebDav filesystem, require the `league/flysystem-webdav` adapter.
- To access a Zip archive filesystem, require the `league/flysystem-ziparchive` adapter.

The [Cache adapter](https://github.com/thephpleague/flysystem-cached-adapter) is already included, however the eventable
filesystem is not yet implemented in this bundle.

## Installation

Include this bundle as a dependency of your project:

```bash
$ php composer.phar require "darsyn/fly-bundle:~0.1"
```

Next, activate the bundle in your application kernel:

```php
<?php

class AppBundle
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Darsyn\Bundle\FlyBundle\DarsynFlyBundle,
        ];
        return $bundles;
    }
}
```

## Setup

Now that the bundle has been enabled, it will search for any services that you define that are tagged with either
`flysystem.adapter` or `flysystem.plugin` every time the configuration is compiled (when the cache is cleared via
`app/console cache:clear` or when changes are detected in debug mode).

### Defining Adapters

To define Flysystem adapters, tag your service definitions with `slysystem.adapter` and add a scheme attribute
(restricted to a letter followed by any number of letters, numbers, hyphens, period or plus signs; see section 3.1 of
RFC 3986).

The class defined by the tagged service definition **must** implement `League\Flysystem\AdapterInterface`.

The following example shows how to implement adapters for Amazon's S3 and Dropbox:

```yaml
parameters:
    # Put these parameter definitions in your "parameters.yml.dist" file.
    amazon.key: ~
    amazon.secret: ~
    amazon.bucket: ~
    dropbox.access_token: ~
    dropbox.client_identifier: ~

services:

    # The adapters rely on these service definitions:
    aws_credentials:
        class:      Aws\Common\Credentials\Credentials
        arguments:  [ %amazon.key%, %amazon.secret% ]
    s3_client:
        class:      Aws\S3\S3Client
        factory:    [ Aws\S3\S3Client, factory ]
        arguments:  [ { credentials: @aws_credentials } ]
    dropbox_client:
        class:      Dropbox\Client
        arguments:  [ %dropbox.access_token%, %dropbox.client_identifier% ]

    # These are the tagged adapter services:
    my_amazon_flyadapter:
        class:      League\Flysystem\AwsS3v2\AwsS3Adapter
        arguments:  [ @s3_client, %amazon.bucket% ]
        tags:
            - { name: flysystem.adapter, protocol: amazon }
    my_dropbox_adapter:
        class:      League\Flysystem\Dropbox\DropboxAdapter
        arguments:  [ @dropbox_client ]
        tags:
            - { name: flysystem.adapter, protocol: dropbox }
```

### Predefined Adapter Services

The bundle comes with one adapter preset for you; a local filesystem with the scheme `project`. It assumes your project
root directory is one above the kernel root directory, and uses that for this adapter.

The scheme for this adapter can be changed in the application configuration (or disabled completely if set to null):

```yaml
darsyn_fly:
    project_adapter: project
```

### Usage

Flysystem can be used in your application by fetching the mount manager service `flysystem` and using it as described in
[Flysystem's documentation](http://flysystem.thephpleague.com/mount-manager).

```php
<?php

class YourController extends Controller
{
    public function indexAction()
    {
        $flysystem = $this->container->get('flysystem');
        $flysystem->move(
            'dropbox://Employment/MyCompany/WorkFromHome/MeetingMinutes.doc',
            'amazon://FinancialDepartment/Meetings/2015-05-01/MayDayMinutes.doc'
        );
        $flysystem->copy(
            'amazon://Website/Styles/CSS/Latest.css',
            'project://web/css/latest.css'
        );
    }
}
```

The `flysystem` service is an alias for the `darsyn_fly` service. You can change this service name (if there is a
conflict where another bundle provides a service of this name) in the application configuration (or disable it
completely by setting it to null).

The `darsyn_fly` service will always be available.

```yaml
darsyn_fly:
    alias: flysystem
```

## Caching

Cache can be enabled globally for all adapters in the configuration via the `darsyn_fly.cache` setting, but also per
adapter in its service configuration by adding the `cache` attribute to an adapter tag.

The class defined in the service definition **must** implement `League\Flysystem\Cached\CachedInterface`.

```yaml
services:

    global_cache_provider:
        class: League\Flysystem\Cached\Storage\Memory
    
    adapter_cache_provider:
        class: League\Flysystem\Cached\Storage\Predis

    dropbox_client:
        class:      Dropbox\Client
        arguments:  [ %dropbox.access_token%, %dropbox.client_identifier% ]

    my_dropbox_adapter:
        class:      League\Flysystem\Dropbox\DropboxAdapter
        arguments:  [ @dropbox_client ]
        tags:
            - { name: flysystem.adapter, scheme: dropbox, cache: adapter_cache_provider }

darsyn_fly:
    cache: global_cache_provider
```

## Flysystem Plugins

Defining plugins to be applied to the mount manager is the same as defining adapters, but tagged with `flysystem.plugin` instead (and no attributes are required).

The class defined in the service definition **must** implement `League\Flysystem\PluginInterface`.

```yaml
services:

    my_example_plugin:
        class: AppBundle\FlyPlugin\Example
        tags:
            - { name: flysystem.plugin }
```

## Authors and Contributing

Current authors include:

- [Zander Baldwin](https://zanderbaldwin.com) <[hello@zanderbaldwin.com](mailto:hello@zanderbaldwin.com)> (on
[GitHub](https://github.com/zanderbaldwin "Zander Baldwin on GitHub")).

All contributions are welcome, don't forget to add your name here in the pull request!
