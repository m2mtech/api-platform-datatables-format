<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use M2MTech\ApiPlatformDatatablesFormat\Serializer\DatatablesCollectionNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @see https://datatables.net/manual/server-side
 */
class DatatablesParameterSubscriber implements EventSubscriberInterface
{
    /** @var array<string,string[]> */
    private $formats;

    /** @var string */
    private $pageParameterName;

    /** @var string */
    private $itemsPerPageParameterName;

    /** @var string */
    private $orderParameterName;

    /** @param array<string,string[]> $formats */
    public function __construct(
        array $formats,
        string $pageParameterName,
        string $itemsPerPageParameterName,
        string $orderParameterName
    ) {
        $this->formats = $formats;
        $this->orderParameterName = $orderParameterName;
        $this->itemsPerPageParameterName = $itemsPerPageParameterName;
        $this->pageParameterName = $pageParameterName;
    }

    public function changeParameter(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (
            !isset($this->formats[DatatablesCollectionNormalizer::FORMAT][0])
            || !$request->isXmlHttpRequest()
            || !$request->query->has('draw')
            || !($acceptedTypes = $request->getAcceptableContentTypes())
            || !in_array($this->formats[DatatablesCollectionNormalizer::FORMAT][0], $acceptedTypes, true)
        ) {
            return;
        }

        $this->changePaginationParameter($request);
        $this->changeSortingParameter($request);
        $this->changeSearchParameter($request);

        $this->removeParameter($request, 'columns');
    }

    private function changePaginationParameter(Request $request): void
    {
        if (!$request->query->has('start') || !$request->query->has('length')) {
            return;
        }

        $start = (int) $request->query->get('start');
        $length = (int) $request->query->get('length');
        $page = intdiv($start, $length) + 1;

        $this->removeParameter($request, 'start');
        $this->removeParameter($request, 'length');

        $this->addParameter($request, $this->pageParameterName, $page);
        $this->addParameter($request, $this->itemsPerPageParameterName, $length);
    }

    private function changeSortingParameter(Request $request): void
    {
        if (!$request->query->has('columns') || !$request->query->has('order')) {
            return;
        }

        $columns = $this->getColumns($request);

        $order = [];
        foreach ($request->query->all()['order'] as $sort) {
            if (!isset($columns[$sort['column']])) {
                continue;
            }

            $order[$columns[$sort['column']]] = (string) $sort['dir'];
        }

        $this->removeParameter($request, 'order');

        if (!$order) {
            return;
        }

        $this->addParameter($request, $this->orderParameterName, $order);
    }

    private function changeSearchParameter(Request $request): void
    {
        if (!$request->query->has('columns') || !$request->query->has('search')) {
            return;
        }

        $columns = $this->getColumns($request);
        $search = $request->query->all()['search'];
        if (!isset($search['value']) || !$search['value']) {
            $this->removeParameter($request, 'search');

            return;
        }

        $value = $search['value'];

        $this->removeParameter($request, 'search');

        $or = [];
        foreach ($columns as $column) {
            $or[$column] = $value;
        }

        $this->addParameter($request, 'or', $or);
    }

    /** @var array<int,string> */
    private $columns = [];

    /**
     * @return array<int,string>
     */
    private function getColumns(Request $request): array
    {
        if ($this->columns) {
            return $this->columns;
        }

        foreach ($request->query->all()['columns'] as $key => $column) {
            if (!isset($column['data'])) {
                continue;
            }

            $this->columns[(int) $key] = (string) $column['data'];
        }

        return $this->columns;
    }

    /**
     * @param int|string|string[] $value
     */
    private function addParameter(Request $request, string $name, $value): void
    {
        $queryString = $request->server->get('QUERY_STRING');
        if (!is_string($queryString)) {
            return;
        }

        if ($request->query->has($name)) {
            $queryString = preg_replace('/(^|&)'.$name.'[^=]*=[^&]*/', '', $queryString);
        }

        $queryString .= '&'.http_build_query([$name => $value]);

        $request->query->set($name, $value);
        $request->server->set('QUERY_STRING', $queryString);
    }

    private function removeParameter(Request $request, string $name): void
    {
        $queryString = $request->server->get('QUERY_STRING');
        if (!is_string($queryString)) {
            return;
        }

        if ($request->query->has($name)) {
            $request->query->remove($name);
        }

        $queryString = preg_replace('/(^|&)'.$name.'[^=]*=[^&]*/', '', $queryString);

        $request->server->set('QUERY_STRING', $queryString);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['changeParameter', EventPriorities::PRE_READ + 100]];
    }
}
