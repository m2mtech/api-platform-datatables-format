<?php

namespace M2MTech\ApiPlatformDatatablesFormat\Serializer;

use ApiPlatform\Core\Serializer\AbstractCollectionNormalizer;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class DatatablesCollectionNormalizer extends AbstractCollectionNormalizer
{
    public const FORMAT = 'datatables';

    /** @phpstan-ignore-next-line */
    protected function getPaginationData($object, array $context = []): array
    {
        $data = [];
        $matches = [];
        if (
            isset($context['uri'])
            && preg_match('/draw=(\d+)/', $context['uri'], $matches)
        ) {
            $data['draw'] = (int) $matches[1];
        }

        [, , , , , , $totalItems] = $this->getPaginationConfig($object, $context);

        if (null !== $totalItems) {
            $data['recordsTotal'] = $totalItems;
            $data['recordsFiltered'] = $totalItems;
        }

        return $data;
    }

    /** @phpstan-ignore-next-line */
    protected function getItemsData($object, string $format = null, array $context = []): array
    {
        $data = [];

        foreach ($object as $obj) {
            $item = $this->normalizer->normalize($obj, $format, $context);
            if (!is_array($item)) {
                throw new UnexpectedValueException('Expected item to be an array');
            }

            $data['data'][] = $item;
        }

        return $data;
    }
}
