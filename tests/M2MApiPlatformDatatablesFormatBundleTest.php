<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat\Tests;

use M2MTech\ApiPlatformDatatablesFormat\DependencyInjection\M2MApiPlatformDatatablesFormatExtension;
use M2MTech\ApiPlatformDatatablesFormat\M2MApiPlatformDatatablesFormatBundle;
use PHPUnit\Framework\TestCase;

class M2MApiPlatformDatatablesFormatBundleTest extends TestCase
{
    public function testGetContainerExtension(): void
    {
        $bundle = new M2MApiPlatformDatatablesFormatBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(M2MApiPlatformDatatablesFormatExtension::class, $extension);
        /* @phpstan-ignore-next-line */
        $this->assertSame('m2m_api_platform_datatables_format', $extension->getAlias());
    }
}
