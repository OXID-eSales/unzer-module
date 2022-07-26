# Unzer Payment for OXID

Unzer Payment integration for OXID eShop 6.3 and above.

## Documentation

* Official German Unzer Payment checkout for OXID [documentation](https://docs.oxid-esales.com/modules/unzer/de/latest/).
* Official English Unzer Payment checkout for OXID [documentation](https://docs.oxid-esales.com/modules/unzer/en/latest/).

## Compatibility

* b-6.3.x module branch is compatible with OXID eShop compilations >=6.3

## Install for OXID

* see Official documentation

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