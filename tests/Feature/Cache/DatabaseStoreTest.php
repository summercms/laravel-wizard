<?php

namespace Ycs77\LaravelWizard\Test\Feature\Cache;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Ycs77\LaravelWizard\Cache\DatabaseStore;
use Ycs77\LaravelWizard\Contracts\Cache;
use Ycs77\LaravelWizard\Test\Stubs\StubWizard;
use Ycs77\LaravelWizard\Test\TestCase;

class DatabaseStoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var DatabaseStore */
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = $this->app['db']->connection(
            $this->app['config']['wizard.connection']
        );

        $table = $this->app['config']['wizard.table'];

        $this->cache = $this->app->makeWith(DatabaseStore::class, [
            'wizard' => $this->app->makeWith(StubWizard::class, [
                'cache' => m::mock(Cache::class),
            ]),
            'connection' => $connection,
            'table' => $table,
            'container' => $this->app,
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
        $this->authenticate();

        // arrange
        $expected = ['step' => ['field' => 'data']];
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
        DB::table('wizards')->insert([
            'wizard' => 'other-wizard',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllDataFromIpAddress()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'ip_address' => '127.0.0.1',
        ]);
        DB::table('wizards')->insert([
            'wizard' => 'other-wizard',
            'payload' => '{"step":{"field":"data"}}',
            'ip_address' => '127.0.0.1',
        ]);

        // act
        $actual = $this->cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        $this->authenticate();

        // arrange
        $expected = ['field' => 'data'];
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        $this->authenticate();

        // arrange
        $expected = 'data';
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetData()
    {
        $this->authenticate();

        // act
        $this->cache->set(['step' => ['field' => 'data']]);

        // assert
        $this->assertDatabaseHas('wizards', [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
    }

    public function testSetDataIncludeLastProcessed()
    {
        $this->authenticate();

        // act
        $this->cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $this->assertDatabaseHas('wizards', [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testGetLastProcessedIndexData()
    {
        $this->authenticate();

        // arrange
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"},"_last_index":0}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetLastProcessedIndexData()
    {
        $this->authenticate();

        // arrange
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"},"_last_index":0}',
            'user_id' => 1,
        ]);

        // act
        $this->cache->setLastProcessedIndex(1);

        // assert
        $this->assertDatabaseHas('wizards', [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);
    }

    public function testPutData()
    {
        $this->authenticate();

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertDatabaseHas('wizards', [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
    }

    public function testOverwriteData()
    {
        $this->authenticate();

        // arrange
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"old data"}}',
            'user_id' => 1,
        ]);

        // act
        $this->cache->put('step', ['field' => 'data']);

        // assert
        $this->assertDatabaseHas('wizards', [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);
    }

    public function testCheckHasData()
    {
        $this->authenticate();

        // arrange
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'user_id' => 1,
        ]);

        // act
        $actual = $this->cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        $this->authenticate();

        // arrange
        DB::table('wizards')->insert([
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"},"_last_index":1}',
            'user_id' => 1,
        ]);

        // act
        $this->cache->clear();

        // assert
        $this->assertDatabaseCount('wizards', 0);
    }
}
