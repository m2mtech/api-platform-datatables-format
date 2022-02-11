<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiPlatformDatatablesFormatBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        $this->extension = $this->createContainerExtension();

        return parent::getContainerExtension();
    }
}
