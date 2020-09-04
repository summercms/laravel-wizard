<?php

namespace Ycs77\LaravelWizard\Test\Unit\Cache;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Mockery as m;
use Mockery\MockInterface;
use Ycs77\LaravelWizard\Cache\DatabaseStore;
use Ycs77\LaravelWizard\Test\TestCase;
use Ycs77\LaravelWizard\Wizard;

class DatabaseStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testGetAllData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder();
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetAllDataFromIpAddress()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];

        $this->mockGuardAndUserIdIs(null);
        $wizard = $this->getWizard();

        /** @var Builder|MockInterface */
        $builder = m::mock(Builder::class);
        $builder->expects()
            ->where('wizard', 'ycs77_test')
            ->andReturn($builder);
        $builder->expects()
            ->where('ip_address', '123.456.789.000')
            ->andReturn($builder);
        $builder->allows(['first' => (object) [
            'wizard' => 'ycs77_test',
            'payload' => '{"step":{"field":"data"}}',
            'ip_address' => '123.456.789.000',
        ]]);

        /** @var MockInterface */
        $request = $this->app->instance('request', m::mock($this->app['request']));
        $request->allows(['ip' => '123.456.789.000']);

        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        // arrange
        $expected = ['field' => 'data'];

        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder();
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        // arrange
        $expected = 'data';

        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder();
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();

        /** @var Builder|MockInterface */
        $builder = m::spy(Builder::class);

        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->set(['step' => ['field' => 'data']]);

        // assert
        $builder->shouldHaveReceived('updateOrInsert')
            ->with(['user_id' => 777], [
                'wizard' => 'ycs77_test',
                'payload' => '{"step":{"field":"data"}}',
            ])
            ->once();
    }

    public function testSetDataIncludeLastProcessed()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();

        /** @var Builder|MockInterface */
        $builder = m::spy(Builder::class);

        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $builder->shouldHaveReceived('updateOrInsert')
            ->with(['user_id' => 777], [
                'wizard' => 'ycs77_test',
                'payload' => '{"step":{"field":"data"},"_last_index":1}',
            ])
            ->once();
    }

    public function testGetLastProcessedIndexData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder();
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetLastProcessedIndexData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder('{"step":{"field":"data"},"_last_index":0}', true);
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->setLastProcessedIndex(1);

        // assert
        $builder->shouldHaveReceived('updateOrInsert')
            ->with(['user_id' => 777], [
                'wizard' => 'ycs77_test',
                'payload' => '{"step":{"field":"data"},"_last_index":1}',
            ])
            ->once();
    }

    public function testPutData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder('{}', true);
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->put('step', ['field' => 'data']);

        // assert
        $builder->shouldHaveReceived('updateOrInsert')
            ->with(['user_id' => 777], [
                'wizard' => 'ycs77_test',
                'payload' => '{"step":{"field":"data"}}',
            ])
            ->once();
    }

    public function testOverwriteData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder('{"step":{"field":"old data"}}', true);
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->put('step', ['field' => 'data']);

        // assert
        $builder->shouldHaveReceived('updateOrInsert')
            ->with(['user_id' => 777], [
                'wizard' => 'ycs77_test',
                'payload' => '{"step":{"field":"data"}}',
            ])
            ->once();
    }

    public function testCheckHasData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder();
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $actual = $cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testClearData()
    {
        // arrange
        $this->mockGuardAndUserIdIs();
        $wizard = $this->getWizard();
        $builder = $this->getGetterBuilder(null, true);
        $connection = $this->getConnectionAndReturn($builder);
        $cache = new DatabaseStore($wizard, $connection, 'wizards', $this->app);

        // act
        $cache->clear();

        // assert
        $builder->shouldHaveReceived('delete')->once();
    }

    /** @return Wizard|MockInterface */
    protected function getWizard()
    {
        /** @var MockInterface */
        $wizard = m::mock(Wizard::class);
        $wizard->allows(['name' => 'ycs77_test']);

        return $wizard;
    }

    /** @return Builder|MockInterface */
    protected function getGetterBuilder(string $payload = null, bool $isSpy = false)
    {
        /** @var MockInterface */
        $builder = $isSpy ? m::spy(Builder::class) : m::mock(Builder::class);
        $builder->expects()
            ->where('wizard', 'ycs77_test')
            ->andReturn($builder);
        $builder->expects()
            ->where('user_id', 777)
            ->andReturn($builder);
        $builder->allows(['first' => (object) [
            'wizard' => 'ycs77_test',
            'payload' => $payload ?? '{"step":{"field":"data"}}',
            'user_id' => 777,
        ]]);

        return $builder;
    }

    /** @return ConnectionInterface|MockInterface */
    protected function getConnectionAndReturn($builder)
    {
        /** @var MockInterface */
        $connection = m::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')
            ->with('wizards')
            ->andReturn($builder);

        return $connection;
    }

    /** @return Guard|MockInterface */
    protected function mockGuardAndUserIdIs(?int $id = 777)
    {
        /** @var MockInterface */
        $guard = $this->mock(Guard::class);
        $guard->allows(['id' => $id]);

        return $guard;
    }
}
