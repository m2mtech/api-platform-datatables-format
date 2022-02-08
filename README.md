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
