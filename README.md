# Web Scraping with PHP

[TOC]

## Summary

This repository contains the [Sociedad Estatal de Correos y Telégrafos](https://www.correos.es/) web scraper which retrieves the Spanish postcodes.

Application is built using **PHP + Guzzle + concurrent requests** to improve the performance.

Docker image and service implementation are based on [Dockerized PHP CLI](https://github.com/fonil/dockerized-php-cli), a lightweight skeleton for PHP CLI microservices.

> For further details about Requirements, Getting Started, Conventions and more, please visit [Dockerized PHP CLI README.md](https://github.com/fonil/dockerized-php-cli#readme)

## Analysis

Spanish postal codes were introduced on 1 July 1984, when the [Sociedad Estatal de Correos y Telégrafos](https://www.correos.es/) introduced automated mail sorting. **They consist of five numerical digits**, where the first two digits, ranging 01 to 52, correspond either to one of the 50 provinces of Spain or to one of the two autonomous cities on the African coast.

> You can find the list of provinces and their corresponding codes at the [Instituto Nacional de Estadística](https://www.ine.es/en/daco/daco42/codmun/cod_provincia_en.htm).

For example:

| Province  | Province ID | Range  | Possible Postcodes   |
| --------- | ----------- | ------ | -------------------- |
| Barcelona | 08          | 1..999 | 08**001**..08**999** |
| Málaga    | 29          | 1..999 | 29**001**..29**999** |
| Ceuta     | 51          | 1..999 | 51**001**..51**999** |

### Sociedad Estatal de Correos y Telégrafos

[Sociedad Estatal de Correos y Telégrafos](https://www.correos.es/) (a.k.a. Correos) is a state-owned company responsible for providing postal service in Spain.

This company provides [a web form from where you can find postcodes](https://www.correos.es/es/en/tools/codigos-postales/details) but, as we can see in the tab *Network* from the browser *developer tools* while performing a search, a request to retrieve postcodes suggestions is performed in background.

#### Valid Requests

```text
GET https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001
```

##### Response

HTTP 200 OK

```json
{
  "suggestions": [
    {
      "text": "08001, Barcelona, Barcelona, Cataluña, ESP",
      "longitude": 2.1686990270000592,
      "latitude": 41.380160001000036
    }
  ]
}
```

#### Unvalid Requests

```text
https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=52999
```

##### Response

HTTP 200 OK

```json
{
  "code": "404",
  "message": "Not Found",
  "moreInformation": {
    "description": "Not results found.",
    "link": "www.correos.es"
  }
}
```

### Implementation

This application will accept a province ID as input, and using the previous endpoint, will check all possible postcodes and store into a CSV file the information from valid ones.

Those CSV files, one per province, will be placed at `./output/province-XX.csv` for easy processing.

> There is a special *Makefile* command for [combining all CSV files](#Combining-CSV-Files) into single one for mass processing.

## Getting Started

Just clone the repository into your preferred path:

```bash
$ mkdir -p ~/path/to/my-new-project && cd ~/path/to/my-new-project
$ git clone git@github.com:alcidesrc/correos-web-scraping-with-php.git .
```

### Commands

#### Build the service

```bash
~/path/to/my-new-project$ make build

[+] Building 48.8s (15/15) FINISHED
 => [internal] load build definition from Dockerfile                                                                      0.0s
 => => transferring dockerfile: 876B                                                                                      0.0s
 => [internal] load .dockerignore                                                                                         0.0s
 => => transferring context: 2B                                                                                           0.0s
 => resolve image config for docker.io/docker/dockerfile:1                                                                2.1s
 => docker-image://docker.io/docker/dockerfile:1@sha256:39b85bbfa7536a5feceb7372a0817649ecb2724562a38360f4d6a7782a409b14  1.5s
 ...
 => [stage-0 4/5] COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer                                   0.0s
 => [stage-0 5/5] WORKDIR /code                                                                                           0.0s
 => exporting to image                                                                                                    0.1s
 => => exporting layers                                                                                                   0.1s
 => => writing image sha256:02c98c48769c83fde7c726aeebc22d743c580b31e7e073ccfeaa9711f7276664                              0.0s
 => => naming to docker.io/library/correos-web-scraping-with-php-app                                                      0.0s

 ✅  Task done!
```

#### Start the service

```bash
~/path/to/my-new-project$ make up

[+] Running 2/2
 ⠿ Network correos-web-scraping-with-php_default  Created                                                                 0.1s
 ⠿ Container correos-web-scraping-with-php-app-1  Started                                                                 0.6s

 ✅  Task done!
```

#### Install the dependencies

```bash
~/path/to/my-new-project$ make composer-install

[13.9MiB/0.09s] Installing dependencies from lock file (including require-dev)
[14.2MiB/0.09s] Verifying lock file contents can be installed on current platform.
[16.1MiB/0.12s] Nothing to install, update or remove
[16.0MiB/0.13s] Generating optimized autoload files
[17.4MiB/1.74s] 70 packages you are using are looking for funding.
[17.4MiB/1.74s] Use the `composer fund` command to find out more!
[17.4MiB/1.75s] Memory usage: 17.43MiB (peak: 22.31MiB), time: 1.75s

 ✅  Task done!
```

#### Executing the application

##### Sequential Mode

```bash
~/path/to/my-new-project$ make run province=52 min=1 max=15

- Province [ 52 ] - Postal Codes [ 1..15 ] - Sequential Requests...
- Elapsed time: 00:00:54.1193
- Consumed memory: 1.19 MB
- CSV generated at: /code/config/../output/province-52.csv

 ✅  Task done!
```

##### Concurrent Mode

```bash
~/path/to/my-new-project$ make run province=52 min=1 max=15

- Province [ 52 ] - Postal Codes [ 1..15 ] - Concurrent Requests [ 10 ]...
- Elapsed time: 00:00:17.7500
- Consumed memory: 1.22 MB
- CSV generated at: /code/config/../output/province-52.csv

 ✅  Task done!
```

#### Combining CSV files

```bash
~/path/to/my-new-project$ make combine-csv-files

 📦  File generated at [ ./all-postal-codes.csv ]

 ✅  Task done!
```

#### Executing the test suite

```bash
~/path/to/my-new-project$ make phpunit

PHPUnit 9.6.8 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.19 with PCOV 1.0.11
Configuration: /app/phpunit.xml
Random Seed:   1686250378

....................                                              20 / 20 (100%)

Time: 00:25.158, Memory: 14.00 MB

OK (20 tests, 22 assertions)

Generating code coverage report in HTML format ... done [00:00.035]


Code Coverage Report:
  2023-06-08 18:53:23

 Summary:
  Classes: 100.00% (6/6)
  Methods: 100.00% (11/11)
  Lines:   100.00% (65/65)

App\Cli\Scraper
  Methods: 100.00% ( 3/ 3)   Lines: 100.00% ( 35/ 35)
App\FileSystem\CsvFile
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  7/  7)
App\Helpers\Range
  Methods: 100.00% ( 4/ 4)   Lines: 100.00% ( 11/ 11)
App\Helpers\ReadableSize
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  4/  4)
App\Helpers\ReadableTime
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  7/  7)
App\Http\UserAgents
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)

Generating code coverage report in PHPUnit XML format ... done [00:00.031]

 ✅  Task done!
```

#### Stop the service

```bash
~/path/to/my-new-project$ make down

[+] Running 2/2
 ⠿ Container correos-web-scraping-with-php-app-1  Removed                                                                 0.2s
 ⠿ Network correos-web-scraping-with-php_default  Removed                                                                 0.5s

 ✅  Task done!
```

## Security Vulnerabilities

PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY

## Supported Versions

Only the latest major version receives security fixes.

## License

The MIT License (MIT). Please see [LICENSE](./LICENSE) file for more information.
