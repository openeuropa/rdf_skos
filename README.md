# RDF SKOS

This module provides a dedicated entity type for SKOS modeling. The module requires access to a [triplestore database](https://en.wikipedia.org/wiki/Triplestore),
such as [Virtuoso 7](https://github.com/openlink/virtuoso-opensource).

## Upgrade from 0.11.0 to 1.0.0-alpha1

On `1.0.0-alpha1`, the `rdf_entity` module dependency has been removed and instead the `sparql_entity_storage` module has been
introduced (see the [rdf_entity module's Readme](https://github.com/ec-europa/rdf_entity#updating-from-10-alpha16-to-alpha17) for more information).

As suggested by the `rdf_entity` module itself, the following steps can be taken in order to update `rdf_skos` in production:

The update process needs to be split in three deployments, which will likely result into separate site releases.

**First deployment**

1. **Before you update `rdf_skos` to `1.0.0-alpha1`**, require an empty version of the `drupal/sparql_entity_storage` module.

The official module repository has a branch which contains such empty version. Drupal.org doesn't allow to reference
this branch directly, so to overcome this you need to add a custom package repository entry that points to the module
repository itself. Place this entry before the Drupal composer packages entry, like shown here:

   ```
    "repositories": [
        {
            "type": "git",
            "url": "https://git.drupalcode.org/project/sparql_entity_storage.git"
        }
        {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        }
    ],
   ```

   Now you can require the module with composer:

   ```
   $ composer require drupal/sparql_entity_storage:dev-empty-module
   ```
4. Deploy to production.
5. Enable the module (this can be part of the deployment procedure above, depending on your setup).

At this point your site's `composer.json` should look like this:

```
{
    ...
    "openeuropa/rdf_skos": "~0.11.0",
    "drupal/sparql_entity_storage": "dev-empty-module",
    ...
}
```

**Second deployment**

1. Remove the empty `drupal/sparql_entity_storage` module requirement from your `composer.json`, together with the
custom package repository entry.
2. Require `drupal/rdf_entity` with the new `1.0-alpha21` version and `openeuropa/rdf_skos` with the new `1.0.0-alpha1` version.
3. Update the Sparql database connection in your settings with the new namespace, so that it looks like this:

```
$databases['sparql_default']['sparql'] = [
  'prefix' => '',
  'host' => 'sparql',
  'port' => '8890',
  'namespace' => 'Drupal\\sparql_entity_storage\\Driver\\Database\\sparql',
  'driver' => 'sparql',
];
```

Note that you might have to change this in your runner.yml.dist file and not directly in settings.php.

4. Deploy to production.
5. Uninstall the `drupal/rdf_entity` module (this can be part of the deployment procedure above, depending on your setup).

At this point your site's `composer.json` should look like this:

```
{
    ...
    "openeuropa/rdf_skos": "~1.0.0-alpha1",
    "drupal/rdf_entity": "~1.0-alpha21",
    ...
}
```

**Third deployment**

1. Remove the `drupal/rdf_entity` dependency.
2. Deploy to production.

At this point your site's `composer.json` should look like this:

```
{
    ...
    "openeuropa/rdf_skos": "~1.0.0-alpha1",
    ...
}
```

After these steps your site should have the latest version `drupal/rdf_skos` module using `drupal/sparql_entity_storage`
and the `drupal/rdf_entity` module should no longer be in your codebase.

## Technical details and constraints

The module allows loading SKOS concept schemes and concepts as entities in Drupal. The entities will be
loaded from the graph IRIs specified in the related configuration.

Since all the graphs are passed to the methods for loading entities, this enforces the limitation of having unique IRIs
(IDs in Drupalese) for the SKOS entities present in all the graphs.

## Development setup

### Initial setup

You can build the test site by running the following steps.

* Install Virtuoso. The easiest way to do this is by using the OpenEuropa [Triple store](https://github.com/openeuropa/triple-store-dev) development Docker container which also pre-imports the main Europa vocabularies.
* Install all the composer dependencies:

```
$ composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values, like your database credentials.

* Setup test site by running:

```
$ ./vendor/bin/run drupal:site-setup
```

This will symlink the module in the proper directory within the test site and
perform token substitution in test configuration files such as `behat.yml.dist`.

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
```

* Install test site by running:

```
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

### Using Docker Compose

Alternatively you can build a test site using Docker and Docker-compose with the provided configuration.

Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker-compose](https://docs.docker.com/compose/)

You can make any alterations you need for your local Docker setup. However, the defaults should be enough to set the project up.

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec web composer install
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

To run the grumphp test:

```
$ docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit test:

```
$ docker-compose exec web ./vendor/bin/phpunit
```

To run the behat test:

```
$ docker-compose exec web ./vendor/bin/behat
```

### Working with content

The project ships with the following Task Runner commands to work with content in the RDF store, they require Docker Compose
services to be up and running.

Purge all data:

```
$ docker-compose exec sparql ./vendor/bin/robo purge
```

Or, if you can run commands on your host machine:

```
$ ./vendor/bin/run sparql:purge
```

Import default data:

```
$ docker-compose exec sparql ./vendor/bin/robo import
```

Or, if you can run commands on your host machine:

```
$ ./vendor/bin/run sparql:import
```

Reset all data, i.e. run purge and import:

```
$ docker-compose exec sparql ./vendor/bin/robo purge
$ docker-compose exec sparql ./vendor/bin/robo import
```

Or, if you can run commands on your host machine:

```
$ ./vendor/bin/run sparql:reset
```
