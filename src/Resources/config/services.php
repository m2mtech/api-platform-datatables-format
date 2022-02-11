<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use M2MTech\ApiPlatformDatatablesFormat\EventSubscriber\DatatablesParameterSubscriber;
use M2MTech\ApiPlatformDatatablesFormat\Serializer\DatatablesCollectionNormalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('api_platform.datatables.encode', ApiPlatform\Core\Serializer\JsonEncoder::class)
        ->args([
            DatatablesCollectionNormalizer::FORMAT,
        ])
        ->tag('serializer.encoder')

        ->set('m2mtech.datatables.collection.normalizer', DatatablesCollectionNormalizer::class)
        ->args([
            service(ResourceClassResolverInterface::class),
            param('api_platform.collection.pagination.page_parameter_name'),
            service(ResourceMetadataFactoryInterface::class),
        ])
        ->tag('serializer.normalizer')

        ->set('m2mtech.datatables.parameter.subscriber', DatatablesParameterSubscriber::class)
        ->args([
            param('api_platform.formats'),
            param('api_platform.collection.pagination.page_parameter_name'),
            param('api_platform.collection.pagination.items_per_page_parameter_name'),
            param('api_platform.collection.order_parameter_name'),
        ])
        ->tag('kernel.event_subscriber');
};
