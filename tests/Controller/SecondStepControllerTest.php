<?php

namespace App\Tests\Controller;

use App\DataFixture\FriendsFixture;
use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Exception\GodDoesNotAcceptException;
use App\Tests\TestCase\ControllerTestCase;
use Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class SecondStepControllerTest extends ControllerTestCase
{
	/**
	 * Test the call of the monster that should be OK
	 *
	 * @dataProvider provideLoadFixturesOK
	 */
	public function testCallTheMonsterOK()
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/call_the_monster');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object can be unserialized in a Friend document
		try {
			/** @var Friend $responseContent */
			$responseContent = $serializer->deserialize(self::$client->getResponse()->getContent(), Friend::class, 'json');
			$this->assertInstanceOf(Friend::class, $responseContent);
		} catch (Exception $exception) {
			$this->fail("Unserialization of json to Friend document failed.");
		}
		$this->assertNotContains($responseContent->getType(), ['GOD', 'UNICORN']);

		$this->assertCount($originalSizeDb - 1, $repo->findAll());
	}

	/**
	 * Test the call of the monster with no fixture loaded
	 */
	public function testCallTheMonsterWithEmptyDB()
	{
		$repo = $this->documentManager->getRepository(Friend::class);

		//Execute request
		self::$client->request('GET', '/call_the_monster');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be an error
		$this->assertArrayHasKey('errors', $responseContent);

		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		$this->assertContains(EmptyDBException::class, $errorTypes);

		$this->assertCount(0, $repo->findAll());
	}

	/**
	 * Test the call of the monster that should be KO
	 *
	 * @dataProvider provideLoadFixturesKO
	 * @param array $criteria
	 */
	public function testCallTheMonsterKO(array $criteria)
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/call_the_monster');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be an error
		$this->assertArrayHasKey('errors', $responseContent);

		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		if (!array_key_exists(Friend::FIELD_TYPE, $criteria) && $criteria[Friend::FIELD_TYPE] === "GOD") {
			$this->assertContains(GodDoesNotAcceptException::class, $errorTypes);
		}

		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	public function provideLoadFixturesOK(): array
	{
		$noCriteria = [];
		$hoomanType = [
			Friend::FIELD_TYPE => "HOOMAN",
		];
		$noobType = [
			Friend::FIELD_TYPE => "NOOB",
		];
		$unicornType = [
			Friend::FIELD_TYPE => "UNICORN",
		];

		return [
			[$noCriteria],
			[$hoomanType],
			[$noobType],
			[$unicornType],
		];
	}

	public function provideLoadFixturesKO(): array
	{
		$godType = [
			Friend::FIELD_TYPE => "GOD",
		];
		$unicornType = [
			Friend::FIELD_TYPE => "UNICORN",
		];

		return [
			[$godType],
			[$unicornType],
		];
	}
}
