# Laravel Health-Check

![build](https://github.com/countless-integers/laravel-health-check/actions/workflows/php.yml/badge.svg)

## Installation

PHP 7.4+ is required.

    $ composer require countless-integers/laravel-health-check
    $ php artisan vendor:publish
    
## Known issues

* even though all checker classes are optional, all the libraries that they depend on are not
* no aliases for check, instead check class names are used
    
## Configuration 

Package publishes its config to your project's and it can be found at `config/health-check.php`.

### Configuration keys

#### Checkers

List of checker classes that should run on service check. By default, all available checkers are included in the exported configuration. You can disable the ones you don`t want by removing their key from this array.

Some of the checkers can or need to be configured. List of available configuration options:

Checker class       | Configuration key           | Supported Values | Default value
--------------------| ----------------------------| ---------------- | -------------
`CacheChecker`      | -                           | - | -
`DbChecker`         | `query`                     | raw SQL query | `SHOW TABLES`
`DiskSpaceChecker`  | `min_free_space` (required) | Value with a SI prefix (KB, MB, GB, TB), e.g. `1GB` | -
`DiskSpaceChecker`  | `drive_path`                | mount path | `.`
`LogFileChecker`    | `log_path`                  | writable log path | `/var/log/laravel.log`
`StorageChecker`    | `drives` (required)         | drive key configures in `filesystems` configuration | -
`DynamodbChecker`   | -                           | | -
`SQSChecker`        | `queue_url` (required)      | queue url, used to identify the queue | -
`PingChecker`       | `domains` (required, []string) | list of urls to ping | -
`PingChecker`       | `timeout` (int)             | timeout value for each check | `5` (sec)

#### Other options

Configuration key | Supported Values | Default value
------------------| ---------------- | -------------
`access_token`    | `null`|(string)  | null 

## Contribution guidelines

PR-s need to:

* include a description explaining the problem and solution
* pass static analysis (uses psalm, ran on CI)
* pass test (uses codeception, ran on CI)

### Running tests

You can use composer scripts:

* to run all tests:

        $ composer test
        
* to run only unit tests:

        $ composer unit-test
        
* to run only unit tests with test coverage:

        $ composer unit-test-coverage
        
* to view the coverage report (mac only):

        $ composer coverage-report
        
* to run static analysis:

        $ composer static-analysis
        
* to run all:

        $ composer qa
