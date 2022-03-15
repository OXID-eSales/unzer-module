# Unzer Payment for OXID

Unzer Payment integration for OXID eShop 6.3 and above.

## Documentation

* official Unzer Payment checkout for OXID [Documentation](https://docs.oxid-esales.com/modules/unzer/de/latest/).

## Compatibility

* b-6.3.x module branch is compatible with OXID eShop compilations 6.3 and 6.4

## Install

```bash

# Add Repository source
$ composer config repositories.oscunzer composer https://unzer-module.packages.oxid-esales.com/
# Install desired version of oxid-solution-catalysts/unzer module
$ composer require oxid-solution-catalysts/unzer ^1.0.0
# Run install
$ composer install
# Activate the module
$ ./vendor/bin/oe-console oe:module:install-configuration source/modules/osc/unzer
$ ./vendor/bin/oe-console oe:module:apply-configuration
```

**NOTE:** The location of the oe-console script depends on whether your root package
is the oxideshop_ce (```./bin/oe-console```) or if the shop was installed from
an OXID eShop edition metapackage (```./vendor/bin/oe-console```).

After requiring the module, you need to activate it, either via OXID eShop admin or CLI.

```bash
$ ./vendor/bin/oe-console oe:module:activate osc-unzer
```

## Limitations

* Float amount values are not supported.

## Running tests

Warning: Running tests will reset the shop.

#### Requirements:
* Ensure test_config.yml is configured:
  * ```
    partial_module_paths: osc/unzer
    ```
  * ```
    activate_all_modules: true
    run_tests_for_shop: false
    run_tests_for_modules: true
    ```
* For codeception tests to be running, selenium server should be available, several options to solve this:
  * Use OXID official [vagrant box environment](https://github.com/OXID-eSales/oxvm_eshop).
  * Use OXID official [docker sdk configuration](https://github.com/OXID-eSales/docker-eshop-sdk).
  * Use other preconfigured containers, example: ``image: 'selenium/standalone-chrome-debug:3.141.59'``

#### Run

Running phpunit tests:
```
vendor/bin/runtests
```

Running phpunit tests with coverage reports (report is generated in ``.../unzer/Tests/reports/`` directory):
```
XDEBUG_MODE=coverage vendor/bin/runtests-coverage
```

Running codeception tests default way (Host: selenium, browser: chrome):
```
vendor/bin/runtests-codeception
```

Running codeception tests example with specific host/browser/testgroup:
```
SELENIUM_SERVER_HOST=seleniumchrome BROWSER_NAME=chrome vendor/bin/runtests-codeception --group=examplegroup
```