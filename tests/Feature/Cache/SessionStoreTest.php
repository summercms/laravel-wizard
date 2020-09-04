<?php

namespace Ycs77\LaravelWizard\Test\Feature\Cache;

use Mockery as m;
use Ycs77\LaravelWizard\Cache\SessionStore;
use Ycs77\LaravelWizard\Contracts\Cache;
use Ycs77\LaravelWizard\Test\Stubs\StubWizard;
use Ycs77\LaravelWizard\Test\TestCase;

class SessionStoreTest extends TestCase
{
    /** @var SessionStore */
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->app->makeWith(SessionStore::class, [
            'wizard' => $this->app->makeWith(StubWizard::class, [
                'cache' => m::mock(Cache::class),
            ]),
            'session' => $this->app['session.store'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->cache = null;
        m::close();
        parent::tearDown();
    }

    public function testGetAllData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        $this->session([
            'wizard:ycs77_test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        // arrange
        $expected = ['field' => 'data'];
        $this->session([
            'wizard:ycs77_test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        // arrange
        $expected = 'data';
        $this->session([
            'wizard:ycs77_test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        // act
        $this->cache->set(['step' => ['field' => 'data']]);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('wizard:ycs77_test'));
    }

    public function testSetDataIncludeLastProcessed()
    {
        // arrange
        $expected = [
            'step' => ['field' => 'data'],
            '_last_index' => 1,
        ];

        // act
        $this->cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('wizard:ycs77_test'));
    }

    public function testGetLastProcessedIndexData()
    {
        // arrange
        $this->session([
            'wizard:ycs77_test' => [
                '_last_index' => 0,
            ],
        ]);

        // act
        $actual = $this->cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetLastProcessedIndexData()
    {
        // arrange
        $expected = [
            'step' => ['field' => 'data'],
            '_last_index' => 1,
        ];

        $this->session([
            'wizard:ycs77_test' => [
                'step' => ['field' => 'data'],
                '_last_index' => 0,
            ],
        ]);

        // act
        $this->cache->setLastProcessedIndex(1);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('wizard:ycs77_test'));
    }

    public function testPutData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('wizard:ycs77_test'));
    }

    public function testOverwriteData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        $this->app['session']->put('wizard:ycs77_test', [
            'step' => ['field' => 'old data'],
        ]);

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertEquals($expected, $this->app['session']->get('wizard:ycs77_test'));
    }

    public function testCheckHasData()
    {
        // arrange
        $this->session([
            'wizard:ycs77_test' => [
                'step' => ['field' => 'data'],
            ],
        ]);

        // act
        $actual = $this->cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        // arrange
        $this->app['session']->put('wizard:ycs77_test', [
            'step' => ['field' => 'data'],
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertNull($this->app['session']->get('wizard:ycs77_test'));
    }
}
