<?php
namespace Neos\RedirectHandler\Tests\Functional;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DateInterval;
use DateTime;
use Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository;
use Neos\RedirectHandler\Exception;
use Neos\RedirectHandler\RedirectInterface;
use Neos\RedirectHandler\Storage\RedirectStorageInterface;
use Neos\Flow\Tests\FunctionalTestCase;

/**
 * Functional tests for the RedirectService and dependant classes
 */
class RedirectTests extends FunctionalTestCase
{
    /**
     * @var boolean
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @var RedirectStorageInterface
     */
    protected $redirectStorage;

    /**
     * @var RedirectRepository
     */
    protected $redirectRepository;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->redirectStorage = $this->objectManager->get(RedirectStorageInterface::class);
        $this->redirectRepository = $this->objectManager->get(RedirectRepository::class);
    }

    /**
     * @test
     */
    public function addRedirectTrimsLeadingAndTrailingSlashesOfSourcePath()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('/some/source/path/', '/some/target/path/');

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame('some/source/path', $redirect->getSourceUriPath());
    }

    /**
     * @test
     */
    public function addRedirectTrimsLeadingSlashesOfTargetPath()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('/some/source/path/', '/some/target/path/');

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame('some/target/path/', $redirect->getTargetUriPath());
    }

    /**
     * @test
     */
    public function addRedirectSetsTheCorrectDefaultStatusCode()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path');

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame(301, $redirect->getStatusCode());
    }

    /**
     * @test
     */
    public function addRedirectRespectsTheGivenStatusCode()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123);

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame(123, $redirect->getStatusCode());
    }

    /**
     * @test
     */
    public function addRedirectRespectsTheGivenCreator()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123, [], 'Seb');

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame('Seb', $redirect->getCreator());
    }

    /**
     * @test
     */
    public function addRedirectRespectsTheGivenComment()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123, [], null, 'Important');

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame('Important', $redirect->getComment());
    }

    /**
     * @test
     */
    public function addRedirectWithoutTypeUseDefaultType()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123);

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame(RedirectInterface::REDIRECT_TYPE_GENERATED, $redirect->getType());
    }

    /**
     * @test
     */
    public function addRedirectRespectsTheGivenType()
    {
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123, [], null, null, RedirectInterface::REDIRECT_TYPE_MANUAL);

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame(RedirectInterface::REDIRECT_TYPE_MANUAL, $redirect->getType());
    }

    /**
     * @test
     */
    public function addRedirectRespectsTheGivenStartAndEndDate()
    {
        $start = new DateTime();
        $end = (new DateTime())->add(new DateInterval('P1D'));
        $this->assertEquals(0, $this->redirectRepository->countAll());
        $this->redirectStorage->addRedirect('some/source/path', 'some/target/path', 123, [], null, null, RedirectInterface::REDIRECT_TYPE_MANUAL, $start, $end);

        $this->persistenceManager->persistAll();
        /** @var RedirectInterface $redirect */
        $redirect = $this->redirectRepository->findAll()->current();

        $this->assertSame($start, $redirect->getStartDateTime());
        $this->assertSame($end, $redirect->getEndDateTime());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function addRedirectThrowsExceptionIfARedirectExistsForTheGivenSourceUriPath()
    {
        $this->redirectStorage->addRedirect('a', 'b');
        $this->redirectStorage->addRedirect('c', 'd');
        $this->persistenceManager->persistAll();

        $this->redirectStorage->addRedirect('c', 'e');
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function addRedirectThrowsExceptionIfARedirectExistsForTheGivenTargetUriPath()
    {
        $this->redirectStorage->addRedirect('a', 'b');
        $this->redirectStorage->addRedirect('c', 'd');
        $this->persistenceManager->persistAll();

        $this->redirectStorage->addRedirect('b', 'c');
    }

    /**
     * @test
     */
    public function addRedirectDoesNotThrowAnExceptionIfARedirectReversesAnExistingRedirect()
    {
        $this->redirectStorage->addRedirect('a', 'b');
        $this->redirectStorage->addRedirect('c', 'd');
        $this->persistenceManager->persistAll();

        $this->redirectStorage->addRedirect('d', 'c');
        $this->persistenceManager->persistAll();

        $expectedRedirects = ['a' => 'b', 'd' => 'c'];

        $resultingRedirects = [];
        foreach ($this->redirectRepository->findAll() as $redirect) {
            $resultingRedirects[$redirect->getSourceUriPath()] = $redirect->getTargetUriPath();
        }
        $this->assertSame($expectedRedirects, $resultingRedirects);
    }

    /**
     * Data provider for addRedirectTests()
     */
    public function addRedirectDataProvider()
    {
        return [
            // avoid redundant redirects (c -> d gets updated to c -> e)
            [
                'existingRedirects' => [
                    'a' => 'b',
                    'c' => 'd',
                ],
                'newRedirects' => [
                    'd' => 'e',
                ],
                'expectedRedirects' => [
                    'a' => 'b',
                    'c' => 'e',
                    'd' => 'e',
                ],
            ],
            // avoid redundant redirects, recursively (c -> d gets updated to c -> e)
            [
                'existingRedirects' => [
                    'a' => 'b',
                    'c' => 'b',
                ],
                'newRedirects' => [
                    'b' => 'd',
                ],
                'expectedRedirects' => [
                    'a' => 'd',
                    'b' => 'd',
                    'c' => 'd',
                ],
            ],
            // avoid circular redirects (c -> d is replaced by d -> c)
            [
                'existingRedirects' => [
                    'a' => 'b',
                    'c' => 'd',
                ],
                'newRedirects' => [
                    'd' => 'c',
                ],
                'expectedRedirects' => [
                    'a' => 'b',
                    'd' => 'c',
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider addRedirectDataProvider
     *
     * @param array $existingRedirects
     * @param array $newRedirects
     * @param array $expectedRedirects
     */
    public function addRedirectTests(array $existingRedirects, array $newRedirects, array $expectedRedirects)
    {
        foreach ($existingRedirects as $sourceUriPath => $targetUriPath) {
            $this->redirectStorage->addRedirect($sourceUriPath, $targetUriPath);
        }
        $this->persistenceManager->persistAll();

        foreach ($newRedirects as $sourceUriPath => $targetUriPath) {
            $this->redirectStorage->addRedirect($sourceUriPath, $targetUriPath);
        }
        $this->persistenceManager->persistAll();

        $resultingRedirects = [];
        foreach ($this->redirectRepository->findAll() as $redirect) {
            $resultingRedirects[$redirect->getSourceUriPath()] = $redirect->getTargetUriPath();
        }
        $this->assertSame($expectedRedirects, $resultingRedirects);
    }
}
