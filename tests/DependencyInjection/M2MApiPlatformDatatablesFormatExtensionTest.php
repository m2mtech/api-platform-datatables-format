<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat\Tests\DependencyInjection;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Serializer\JsonEncoder;
use M2MTech\ApiPlatformDatatablesFormat\DependencyInjection\M2MApiPlatformDatatablesFormatExtension;
use M2MTech\ApiPlatformDatatablesFormat\EventSubscriber\DatatablesParameterSubscriber;
use M2MTech\ApiPlatformDatatablesFormat\Serializer\DatatablesCollectionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class M2MApiPlatformDatatablesFormatExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new M2MApiPlatformDatatablesFormatExtension();
        $container = new ContainerBuilder();
        $extension->load([], $container);

        $definition = $container->getDefinition('api_platform.datatables.encode');
        $this->assertTrue($definition->hasTag('serializer.encoder'));
        $encoder = $container->get('api_platform.datatables.encode');
        $this->assertInstanceOf(JsonEncoder::class, $encoder);
        /* @phpstan-ignore-next-line */
        $this->assertTrue($encoder->supportsEncoding(DatatablesCollectionNormalizer::FORMAT));

        $definition = $container->getDefinition('m2mtech.datatables.collection.normalizer');
        $this->assertTrue($definition->hasTag('serializer.normalizer'));
        $container->setParameter('api_platform.collection.pagination.page_parameter_name', 'page');
        $definition->setArgument(0, $this->createMock(ResourceClassResolverInterface::class));
        $definition->setArgument(2, $this->createMock(ResourceMetadataFactoryInterface::class));
        $normalizer = $container->get('m2mtech.datatables.collection.normalizer');
        $this->assertInstanceOf(DatatablesCollectionNormalizer::class, $normalizer);

        $definition = $container->getDefinition('m2mtech.datatables.parameter.subscriber');
        $this->assertTrue($definition->hasTag('kernel.event_subscriber'));
        $container->setParameter('api_platform.formats', []);
        $container->setParameter('api_platform.collection.pagination.items_per_page_parameter_name', 10);
        $container->setParameter('api_platform.collection.order_parameter_name', 'order');
        $subscriber = $container->get('m2mtech.datatables.parameter.subscriber');
        $this->assertInstanceOf(DatatablesParameterSubscriber::class, $subscriber);
    }

    public function testGetAlias(): void
    {
        $extension = new M2MApiPlatformDatatablesFormatExtension();
        $this->assertSame('m2m_api_platform_datatables_format', $extension->getAlias());
    }
}
