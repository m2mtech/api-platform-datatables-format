<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat\Tests\Serializer;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use M2MTech\ApiPlatformDatatablesFormat\Serializer\DatatablesCollectionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DatatablesCollectionNormalizerTest extends TestCase
{
    public function testNormalization(): void
    {
        $resourceClassResolver = $this->createMock(ResourceClassResolverInterface::class);
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->method('normalize')
            ->willReturnArgument(0);
        $collectionNormalizer = new DatatablesCollectionNormalizer($resourceClassResolver, 'page');
        $collectionNormalizer->setNormalizer($normalizer);

        $data = [[1], [2], [3]];
        $this->assertSame([
            'recordsTotal' => 3,
            'recordsFiltered' => 3,
            'data' => $data,
        ], $collectionNormalizer->normalize($data, 'datatables', ['resource_class' => 'class']));

        $data = [[1], [2], [3], ['x']];
        $this->assertSame([
            'draw' => 42,
            'recordsTotal' => 4,
            'recordsFiltered' => 4,
            'data' => $data,
        ], $collectionNormalizer->normalize($data, 'datatables', [
            'resource_class' => 'class',
            'uri' => 'draw=42',
        ]));
    }
}
