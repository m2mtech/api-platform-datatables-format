# API Platform Datatables Format

[![Author](https://img.shields.io/badge/author-@m2mtech-blue.svg?style=flat-square)](http://www.m2m.at)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

---

This bundle provides the [Datatables format](https://datatables.net) to the [API Platform](https://api-platform.com).

## Installation

```bash
composer require m2mtech/api-platform-datatables-format
```

If you are not using Flex enable the bundle:

```php
// config/bundles.php

return [
    // ...
    M2MTech\ApiPlatformDatatablesFormat\M2MApiPlatformDatatablesFormatBundle::class => ['all' => true],
];
```

## Usage

Enable the datatables format:

```yaml
# config/packages/api_platform.yaml
api_platform:
    formats:
        datatables: [ 'application/vnd.datatables+json' ]
```

### Pagination

The package rewrites the query parameters `start` and `length` from [datatables](https://datatables.net/manual/server-side) to `page` and `itemsPerPage` or whatever you have set as `page_parameter_name` and `items_per_page_parameter_name` for the [API Platform](https://api-platform.com/docs/core/pagination). 

e.g.:
```diff
- /api/offers?draw=1&start=0&length=10
+ /api/offers?draw=1&page=1&itemsPerPage=10
```

Pagination is enabled by default in the [API Platform](https://api-platform.com/docs/core/pagination). 


### Sorting 

The package rewrites the query parameters `columns` and `order` from [datatables](https://datatables.net/manual/server-side) to `order` or whatever you have set as `order_parameter_name` for the [API Platform](https://api-platform.com/docs/core/pagination).

e.g.:
```diff
- /api/offers?draw=2&columns[0][data]=name&columns[1][data]=price&order[0][column]=1&order[0][dir]=desc
+ /api/offers?draw=2&order[email]=desc
```

You need to enable sorting for the [API Platform](https://api-platform.com/docs/core/pagination).

e.g. in your entity definition:
```php
#[ApiFilter(OrderFilter::class, properties: ['name', 'price'])]
```

### Search

The package rewrites the query parameters `columns` and `search` from [datatables](https://datatables.net/manual/server-side) to `or` for the [Filter logic for API Platform](https://github.com/metaclass-nl/filter-bundle).

e.g.:
````diff
- /api/offers?draw=3&columns[0][data]=name&columns[1][data]=description&search[value]=shirt 
+ /api/offers?draw=2&or[name]=shirt&or[desciption]=shirt
````

You need to install [Filter logic for API Platform](https://github.com/metaclass-nl/filter-bundle), an equivalent bundle or your own filter for this functionality, e.g.:

```bash
composer require metaclass-nl/filter-bundle "dev-master"
```

You need also to enable the search filter for the [API Platform](https://api-platform.com/docs/core/pagination).

e.g. in your entity definition:
```php
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'description' => 'partial'])]
#[ApiFilter(FilterLogic::class)]
```


### Output

Including the data, the output contains `recordsTotal` and `recordsFiltered` (which are always the same) as well as the `draw` parameter from the query. 


### 

## Testing

This package has been developed for php 7.4 with compatibility tested for php 7.2 to 8.1.

```bash
composer test
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
