<?php

namespace Ycs77\LaravelWizard\Test\Unit\Cache;

use Illuminate\Contracts\Session\Session;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Ycs77\LaravelWizard\Cache\SessionStore;
use Ycs77\LaravelWizard\Wizard;

class SessionStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGetAllData()
    {
        // arrange
        $expected = ['step' => ['field' => 'data']];
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'data']]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->get();

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetStepData()
    {
        // arrange
        $expected = ['field' => 'data'];
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'data']]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->get('step');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldData()
    {
        // arrange
        $expected = 'data';
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'data']]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->get('step.field');

        // assert
        $this->assertEquals($expected, $actual);
    }

    public function testSetData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->set(['step' => ['field' => 'data']]);

        // assert
        $session->shouldHaveReceived('put')
            ->with('wizard:ycs77_test', ['step' => ['field' => 'data']])
            ->once();
    }

    public function testSetDataIncludeLastProcessed()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->set(['step' => ['field' => 'data']], 1);

        // assert
        $session->shouldHaveReceived('put')
            ->with('wizard:ycs77_test', [
                'step' => ['field' => 'data'],
                '_last_index' => 1,
            ])
            ->once();
    }

    public function testGetLastProcessedIndexData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['_last_index' => 0]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->getLastProcessedIndex();

        // assert
        $this->assertEquals(0, $actual);
    }

    public function testSetLastProcessedIndexData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'data'], '_last_index' => 0]);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->setLastProcessedIndex(1);

        // assert
        $session->shouldHaveReceived('put')
            ->with('wizard:ycs77_test', [
                'step' => ['field' => 'data'],
                '_last_index' => 1,
            ])
            ->once();
    }

    public function testPutData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn([]);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->put('step', ['field' => 'data']);

        // assert
        $session->shouldHaveReceived('put')
            ->with('wizard:ycs77_test', ['step' => ['field' => 'data']])
            ->once();
    }

    public function testOverwriteData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'old data']]);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->put('step', ['field' => 'data']);

        // assert
        $session->shouldHaveReceived('put')
            ->with('wizard:ycs77_test', ['step' => ['field' => 'data']])
            ->once();
    }

    public function testCheckHasData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn(['step' => ['field' => 'data']]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->has('step');

        // assert
        $this->assertTrue($actual);
    }

    public function testCheckNotHasData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::mock(Session::class);
        $session->expects()
            ->get('wizard:ycs77_test', [])
            ->andReturn([]);

        $cache = new SessionStore($wizard, $session);

        // act
        $actual = $cache->has('not-found');

        // assert
        $this->assertFalse($actual);
    }

    public function testClearData()
    {
        // arrange
        $wizard = $this->getWizard();

        /** @var Session|MockInterface */
        $session = m::spy(Session::class);

        $cache = new SessionStore($wizard, $session);

        // act
        $cache->clear();

        // assert
        $session->shouldHaveReceived('forget')
            ->with('wizard:ycs77_test')
            ->once();
    }

    /** @return Wizard|MockInterface */
    protected function getWizard()
    {
        /** @var MockInterface */
        $wizard = m::mock(Wizard::class);
        $wizard->allows(['name' => 'ycs77_test']);

        return $wizard;
    }
}
