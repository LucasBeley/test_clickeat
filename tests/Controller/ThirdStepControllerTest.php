<?php

namespace App\Tests\Controller;

use App\DataFixture\FriendsFixture;
use App\DataFixture\FriendsWhoLooseBattleFixture;
use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Tests\TestCase\ControllerTestCase;

class ThirdStepControllerTest extends ControllerTestCase
{
	/**
	 * Test the launch of the battle that Poppy should win
	 */
	public function testLaunchTheBattlePoppyWin()
	{
		$this->loadFixtures([FriendsWhoLooseBattleFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/launch_the_battle');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		$this->assertArrayHasKey('battleLaunched', $responseContent);
		$this->assertArrayHasKey('scores', $responseContent);
		$this->assertArrayHasKey('poppy', $responseContent['scores']);
		$this->assertArrayHasKey('friends', $responseContent['scores']);
		$this->assertArrayHasKey('poppyWon', $responseContent);

		//All humans and noobs are eaten, all unicorns ands gods stay non eaten
		$humanAndNoob = $repo->findBy([Friend::FIELD_TYPE => ['$in' => ['NOOB', 'HOOMAN']]]);
		$eaten = $repo->findBy(['eaten' => true]);
		$unicornAndGod = $repo->findBy([Friend::FIELD_TYPE => ['$in' => ['GOD', 'UNICORN']]]);
		$nonEaten = $repo->findBy(['eaten' => ['$in' => [null, false]]]);
		$this->assertCount(count($humanAndNoob), $eaten);
		$this->assertCount(count($unicornAndGod), $nonEaten);

		//Size of collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	/**
	 * Test the call of the monster with no fixture loaded
	 */
	public function testCallTheMonsterWithEmptyDB()
	{
		$repo = $this->documentManager->getRepository(Friend::class);

		//Execute request
		self::$client->request('GET', '/launch_the_battle');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be an error
		$this->assertArrayHasKey('errors', $responseContent);

		//Map types of error in an array to simplify the check
		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		$this->assertContains(EmptyDBException::class, $errorTypes);

		$this->assertCount(0, $repo->findAll());
	}

	/**
	 * Test the launch of the battle that Poppy should loose
	 */
	public function testLaunchTheBattlePoppyLoose()
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/launch_the_battle');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		$this->assertArrayHasKey('battleLaunched', $responseContent);
		$this->assertArrayHasKey('scores', $responseContent);
		$this->assertArrayHasKey('poppy', $responseContent['scores']);
		$this->assertArrayHasKey('friends', $responseContent['scores']);
		$this->assertArrayHasKey('poppyLoose', $responseContent);

		//Everyone is non eaten
		$nonEaten = $repo->findBy(['eaten' => ['$in' => [null, false]]]);
		$this->assertCount($originalSizeDb, $nonEaten);

		//Size of collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	/**
	 * Test the deus ex machina
	 */
	public function testDeusExMachina()
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/deus_ex_machina');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		$this->assertArrayHasKey('deusExMachina', $responseContent);

		//Everyone is non eaten
		$nonEaten = $repo->findBy(['eaten' => ['$in' => [null, false]]]);
		$this->assertCount($originalSizeDb, $nonEaten);

		//Size of collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}
}
