<?php
/*
 * This file is part of the api-platform-datatables-format package.
 *
 * (c) 2022 m2m server software gmbh <tech@m2m.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace M2MTech\ApiPlatformDatatablesFormat\Tests\EventSubscriber;

use M2MTech\ApiPlatformDatatablesFormat\EventSubscriber\DatatablesParameterSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Stopwatch\Stopwatch;

class DatatablesParameterSubscriberTest extends TestCase
{
    /** @var Request */
    private $request;

    public function testGetSubscribedEvents(): void
    {
        $traceableEventDispatcher = new TraceableEventDispatcher(new EventDispatcher(), new Stopwatch());
        $traceableEventDispatcher->addSubscriber($this->getSubscriber());

        $traceableEventDispatcher->dispatch($this->getEvent(), KernelEvents::REQUEST);
        $this->assertSame(DatatablesParameterSubscriber::class.'::changeParameter', $traceableEventDispatcher->getCalledListeners()[0]['pretty']);
    }

    /**
     * @param array<string, string>         $query
     * @param array<string,string|string[]> $server
     * @param array<string, string>         $expectedQuery
     * @dataProvider changeParametersProvider
     */
    public function testChangeParameter(array $query, array $server, array $expectedQuery, string $expectedQueryString): void
    {
        $subscriber = $this->getSubscriber();
        $subscriber->changeParameter($this->getEvent($query, $server));
        $this->assertSame($expectedQuery, $this->request->query->all());
        $this->assertSame($expectedQueryString, urldecode((string) $this->request->getQueryString()));
    }

    public function changeParametersProvider(): iterable
    {
        yield [
            ['test' => 'full', 'draw' => 1, 'start' => 10, 'length' => 10, 'columns' => [['data' => 'name'], ['data' => 'email']], 'order' => [['column' => 1, 'dir' => 'asc']], 'search' => ['value' => 'me']],
            [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => ['application/vnd.datatables+json'],
            ],
            ['test' => 'full', 'draw' => 1, 'page' => 2, 'itemsPerPage' => 10, 'order' => ['email' => 'asc'], 'or' => ['name' => 'me', 'email' => 'me']],
            'draw=1&itemsPerPage=10&or[name]=me&or[email]=me&order[email]=asc&page=2&test=full',
        ];
        yield [
            ['test' => 'doNothing', 'draw' => 2],
            [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => ['application/vnd.datatables+json'],
            ],
            ['test' => 'doNothing', 'draw' => 2],
            'draw=2&test=doNothing',
        ];
        yield [
            ['test' => 'wrongRequestType', 'draw' => 3, 'start' => 0, 'length' => 10],
            [
                'HTTP_ACCEPT' => ['application/vnd.datatables+json'],
            ],
            ['test' => 'wrongRequestType', 'draw' => 3, 'start' => 0, 'length' => 10],
            'draw=3&length=10&start=0&test=wrongRequestType',
        ];
        yield [
            ['test' => 'wrongAccept', 'draw' => 3, 'start' => 0, 'length' => 10],
            [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
            ['test' => 'wrongAccept', 'draw' => 3, 'start' => 0, 'length' => 10],
            'draw=3&length=10&start=0&test=wrongAccept',
        ];
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $expectedQuery
     * @dataProvider paginationProvider
     */
    public function testChangePaginationParameter(array $query, array $expectedQuery, string $expectedQueryString): void
    {
        $this->testChangeParameter($query, [], $expectedQuery, $expectedQueryString);
    }

    public function paginationProvider(): iterable
    {
        yield [
            ['test' => 'startMissing', 'draw' => 1, 'length' => 10],
            ['test' => 'startMissing', 'draw' => 1, 'length' => 10],
            'draw=1&length=10&test=startMissing',
        ];
        yield [
            ['test' => 'lengthMissing', 'draw' => 2, 'start' => 0],
            ['test' => 'lengthMissing', 'draw' => 2, 'start' => 0],
            'draw=2&start=0&test=lengthMissing',
        ];
        yield [
            ['test' => 'valid', 'draw' => 3, 'start' => 0, 'length' => 10],
            ['test' => 'valid', 'draw' => 3, 'page' => 1, 'itemsPerPage' => 10],
            'draw=3&itemsPerPage=10&page=1&test=valid',
        ];
        yield [
            ['test' => 'valid', 'draw' => 4, 'start' => 9, 'length' => 10],
            ['test' => 'valid', 'draw' => 4, 'page' => 1, 'itemsPerPage' => 10],
            'draw=4&itemsPerPage=10&page=1&test=valid',
        ];
        yield [
            ['test' => 'valid', 'draw' => 5, 'start' => 10, 'length' => 10],
            ['test' => 'valid', 'draw' => 5, 'page' => 2, 'itemsPerPage' => 10],
            'draw=5&itemsPerPage=10&page=2&test=valid',
        ];
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $expectedQuery
     * @dataProvider sortingProvider
     */
    public function testChangeSortingParameter(array $query, array $expectedQuery, string $expectedQueryString): void
    {
        $this->testChangePaginationParameter($query, $expectedQuery, $expectedQueryString);
    }

    public function sortingProvider(): iterable
    {
        yield [
            ['test' => 'columnsMissing', 'draw' => 1, 'order' => [['column' => 0, 'dir' => 'asc'], ['column' => 1, 'dir' => 'desc']]],
            ['test' => 'columnsMissing', 'draw' => 1, 'order' => [['column' => 0, 'dir' => 'asc'], ['column' => 1, 'dir' => 'desc']]],
            'draw=1&order[0][column]=0&order[0][dir]=asc&order[1][column]=1&order[1][dir]=desc&test=columnsMissing',
        ];
        yield [
            ['test' => 'orderMissing', 'draw' => 2, 'columns' => [['data' => 'name'], ['data' => 'email']]],
            ['test' => 'orderMissing', 'draw' => 2],
            'draw=2&test=orderMissing',
        ];
        yield [
            ['test' => 'sortBoth', 'draw' => 3, 'columns' => [['data' => 'name'], ['data' => 'email']], 'order' => [['column' => 0, 'dir' => 'asc'], ['column' => 1, 'dir' => 'desc']]],
            ['test' => 'sortBoth', 'draw' => 3, 'order' => ['name' => 'asc', 'email' => 'desc']],
            'draw=3&order[name]=asc&order[email]=desc&test=sortBoth',
        ];
        yield [
            ['test' => 'sortSecond', 'draw' => 4, 'columns' => [['data' => 'name'], ['data' => 'email']], 'order' => [['column' => 1, 'dir' => 'asc']]],
            ['test' => 'sortSecond', 'draw' => 4, 'order' => ['email' => 'asc']],
            'draw=4&order[email]=asc&test=sortSecond',
        ];
        yield [
            ['test' => 'missingSecondColumn', 'draw' => 5, 'columns' => [['data' => 'name']], 'order' => [['column' => 1, 'dir' => 'asc']]],
            ['test' => 'missingSecondColumn', 'draw' => 5],
            'draw=5&test=missingSecondColumn',
        ];
        yield [
            ['test' => 'invalidFirstColumn', 'draw' => 6, 'columns' => [['wrongData' => 'name'], ['data' => 'email']], 'order' => [['column' => 1, 'dir' => 'asc']]],
            ['test' => 'invalidFirstColumn', 'draw' => 6, 'order' => ['email' => 'asc']],
            'draw=6&order[email]=asc&test=invalidFirstColumn',
        ];
        yield [
            ['test' => 'invalidSecondColumn', 'draw' => 7, 'columns' => [['data' => 'name'], ['wrongData' => 'email']], 'order' => [['column' => 1, 'dir' => 'asc']]],
            ['test' => 'invalidSecondColumn', 'draw' => 7],
            'draw=7&test=invalidSecondColumn',
        ];
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $expectedQuery
     * @dataProvider searchProvider
     */
    public function testChangeSearchParameter(array $query, array $expectedQuery, string $expectedQueryString): void
    {
        $this->testChangePaginationParameter($query, $expectedQuery, $expectedQueryString);
    }

    public function searchProvider(): iterable
    {
        yield [
            ['test' => 'columnsMissing', 'draw' => 1, 'search' => ['value' => 'me']],
            ['test' => 'columnsMissing', 'draw' => 1, 'search' => ['value' => 'me']],
            'draw=1&search[value]=me&test=columnsMissing',
        ];
        yield [
            ['test' => 'searchMissing', 'draw' => 2, 'columns' => [['data' => 'name'], ['data' => 'email']]],
            ['test' => 'searchMissing', 'draw' => 2],
            'draw=2&test=searchMissing',
        ];
        yield [
            ['test' => 'search', 'draw' => 3, 'columns' => [['data' => 'name'], ['data' => 'email']], 'search' => ['value' => 'me']],
            ['test' => 'search', 'draw' => 3, 'or' => ['name' => 'me', 'email' => 'me']],
            'draw=3&or[name]=me&or[email]=me&test=search',
        ];
        yield [
            ['test' => 'invalidSearch', 'draw' => 4, 'columns' => [['data' => 'name'], ['data' => 'email']], 'search' => ['invalidValue' => 'me']],
            ['test' => 'invalidSearch', 'draw' => 4],
            'draw=4&test=invalidSearch',
        ];
    }

    /**
     * @param array<string,string>          $query
     * @param array<string,string|string[]> $server
     */
    private function getEvent(array $query = [], array $server = []): RequestEvent
    {
        if (!$server) {
            $server = [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => ['application/vnd.datatables+json'],
            ];
        }
        $server['QUERY_STRING'] = http_build_query($query);
        $this->request = new Request($query, [], [], [], [], $server);
        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequest')
            ->willReturn($this->request);

        return $event;
    }

    private function getSubscriber(): DatatablesParameterSubscriber
    {
        return new DatatablesParameterSubscriber(
            ['datatables' => ['application/vnd.datatables+json']],
            'page',
            'itemsPerPage',
            'order'
        );
    }
}
