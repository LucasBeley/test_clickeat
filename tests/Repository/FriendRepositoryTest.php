<?php

namespace App\Tests\Repository;

use App\DataFixture\NotRandomFriendsFixture;
use App\Document\Friend;
use App\Repository\FriendRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\MongoDB\Fixtures\FixturesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FriendRepositoryTest extends KernelTestCase
{
	use FixturesTrait;

	const DEFAULT_DOC_MANAGER_SERVICE = 'doctrine_mongodb.odm.default_document_manager';

	/**
	 * @var DocumentManager
	 */
	protected $documentManager;

	protected function setUp(): void
	{
		$kernel = self::bootKernel();

		$this->documentManager = $kernel->getContainer()
			->get(self::DEFAULT_DOC_MANAGER_SERVICE);
		$this->mongoDbPurge($this->documentManager);
	}

	public function testGetSideLife()
	{
		$this->loadFixtures([NotRandomFriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		/** @var FriendRepository $repo */
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		$this->assertEquals(78, $repo->getSideLife([true]));
		$this->assertEquals(164, $repo->getSideLife([null,false]));

		//Size of collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}
}
