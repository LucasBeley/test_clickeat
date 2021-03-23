<?php

namespace App\Tests\Controller;

use App\DataFixture\FriendsFixture;
use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Exception\FriendshipOutOfBoundsException;
use App\Exception\GodDoesNotAcceptException;
use App\Exception\MissingParametersException;
use App\Exception\WrongTypeForParameterException;
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
	 * @dataProvider provideCallTheMonsterOK
	 * @param array $criteria
	 */
	public function testCallTheMonsterOK(array $criteria)
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$sacrifiedFriend = null;
		if (!empty($criteria)) {
			$sacrifiedFriend = $repo->findOneBy($criteria);
		}

		//Execute request
		self::$client->request(
			'GET',
			'/call_the_monster',
			$sacrifiedFriend ? ['id' => $sacrifiedFriend->getId()] : []
		);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object can be unserialized in a Friend document
		try {
			/** @var Friend $responseContent */
			$responseContent = $serializer->deserialize(self::$client->getResponse()->getContent(), Friend::class, 'json');
			$this->assertInstanceOf(Friend::class, $responseContent);
			$this->assertTrue($responseContent->getEaten());
		} catch (Exception $exception) {
			$this->fail("Unserialization of json to Friend document failed.");
		}
		$this->assertNotContains($responseContent->getType(), ['GOD', 'UNICORN']);

		if ($sacrifiedFriend !== null) {
			//Check that the good one has been eaten
			$this->assertEquals($sacrifiedFriend->getId(), $responseContent->getId());
		}

		$this->assertCount($originalSizeDb, $repo->findAll());
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

		//Map types of error in an array to simplify the check
		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		$this->assertContains(EmptyDBException::class, $errorTypes);

		$this->assertCount(0, $repo->findAll());
	}


	/**
	 * Test the call of the monster with a Unicorn
	 *
	 * @dataProvider provideCallTheMonsterUnicorn
	 * @param array $criteria
	 */
	public function testCallTheMonsterUnicorn(array $criteria)
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$sacrifiedFriend = $repo->findOneBy($criteria);

		//Execute request
		self::$client->request(
			'GET',
			'/call_the_monster',
			['id' => $sacrifiedFriend->getId()]
		);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be a specific return
		$this->assertArrayHasKey('unicornPower', $responseContent, array_keys($responseContent)[0]);

		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	/**
	 * Test the call of the monster that should be KO
	 *
	 * @dataProvider provideCallTheMonsterKO
	 * @param array $criteria
	 */
	public function testCallTheMonsterKO(array $criteria)
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$sacrifiedFriend = null;
		if (!empty($criteria)) {
			$sacrifiedFriend = $repo->findOneBy($criteria);
		}

		//Execute request
		self::$client->request(
			'GET',
			'/call_the_monster',
			$sacrifiedFriend ? ['id' => $sacrifiedFriend->getId()] : []
		);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be an error
		$this->assertArrayHasKey('errors', $responseContent);

		//Map types of error in an array to simplify the check
		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		if (array_key_exists(Friend::FIELD_TYPE, $criteria) && $criteria[Friend::FIELD_TYPE] === "GOD") {
			$this->assertContains(GodDoesNotAcceptException::class, $errorTypes);
		}

		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	public function provideCallTheMonsterOK(): array
	{
		$hoomanType = [
			Friend::FIELD_TYPE => "HOOMAN",
		];
		$noobType = [
			Friend::FIELD_TYPE => "NOOB",
		];

		return [
			[$hoomanType],
			[$noobType],
		];
	}

	public function provideCallTheMonsterUnicorn(): array
	{
		$unicornType = [
			Friend::FIELD_TYPE => "UNICORN",
		];

		return [
			[$unicornType]
		];
	}

	public function provideCallTheMonsterKO(): array
	{
		$godType = [
			Friend::FIELD_TYPE => "GOD",
		];

		return [
			[$godType]
		];
	}

	/**
	 * Test the listing of eaten friends
	 */
	public function testListEaten()
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$nbEaten = count($repo->findBy([Friend::FIELD_EATEN => true]));

		//Execute request
		self::$client->request('GET', '/list_eaten');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		$responseContent = json_decode(self::$client->getResponse()->getContent());
		$this->assertIsArray($responseContent);
		$this->assertCount($nbEaten, $responseContent);
		for ($i = 0; $i < count($responseContent); $i++) {
			try {
				/** @var Friend $friend */
				$friend = $serializer->deserialize(json_encode($responseContent[$i]), Friend::class, 'json');
				$this->assertInstanceOf(Friend::class, $friend);
				$this->assertTrue($friend->getEaten());
				$this->assertNotContains($friend->getType(), ['GOD', 'UNICORN']);
			} catch (Exception $exception) {
				$this->fail("Unserialization of json to Friend document failed.");
			}
		}

		//Size of the collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	/**
	 * Test the listing of eaten friends with empty DB
	 */
	public function testListEatenEmptyDB()
	{
		$repo = $this->documentManager->getRepository(Friend::class);

		//Execute request
		self::$client->request('GET', '/list_eaten');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);
		$this->assertArrayHasKey("emptyStomach", $responseContent);

		//Size of the collection should not change and should be 0
		$this->assertCount(0, $repo->findAll());
	}

	/**
	 * Test changing a friendship value
	 */
	public function testChangeFriendshipValue()
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$chosen = $repo->findOneBy([Friend::FIELD_TYPE => 'HOOMAN']);
		$chosenFriendshipValue = 57;
		$urlParams[Friend::FIELD_ID] = $chosen->getId();
		$urlParams[Friend::FIELD_FRIENDSHIP_VALUE] = $chosenFriendshipValue;

		//Execute request
		self::$client->request(
			'GET',
			'/change_friendship_value',
			$urlParams
		);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object can be unserialized in a Friend document
		try {
			/** @var Friend $responseContent */
			$responseContent = $serializer->deserialize(self::$client->getResponse()->getContent(), Friend::class, 'json');
			$this->assertInstanceOf(Friend::class, $responseContent);
			$this->assertEquals($chosenFriendshipValue, $responseContent->getFriendshipValue());
		} catch (Exception $exception) {
			$this->fail("Unserialization of json to Friend document failed.");
		}
		$this->assertNotEquals('GOD', $responseContent->getType());

		//Size of the collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	/**
	 * Test changing a friendship value that should be KO
	 *
	 * @dataProvider provideChangeFriendshipValueKO
	 * @param $friendshipValue
	 * @param array $criteria
	 */
	public function testChangeFriendshipValueKO($friendshipValue, array $criteria)
	{
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());
		$chosen = null;
		if (!array_key_exists(Friend::FIELD_ID, $criteria)) {
			$chosen = $repo->findOneBy($criteria);
		}
		$chosenFriendshipValue = $friendshipValue;
		$urlParams[Friend::FIELD_ID] = $chosen ? $chosen->getId() : null;
		$urlParams[Friend::FIELD_FRIENDSHIP_VALUE] = $chosenFriendshipValue;

		//Execute request
		self::$client->request(
			'GET',
			'/change_friendship_value',
			$urlParams
		);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be an error
		$this->assertArrayHasKey('errors', $responseContent);

		//Map types of error in an array to simplify the check
		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		//Check errors returned for friendshipValue
		if (array_key_exists(Friend::FIELD_ID, $criteria) && $criteria[Friend::FIELD_ID] === null) {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		}

		//Check errors returned for friendshipValue
		if ($friendshipValue === null) {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_numeric($friendshipValue)) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if ($friendshipValue < 0 || $friendshipValue > 100) {
			$this->assertContains(FriendshipOutOfBoundsException::class, $errorTypes);
		}

		//Check errors for GOD
		if (array_key_exists(Friend::FIELD_TYPE, $criteria) && $criteria[Friend::FIELD_TYPE] === "GOD") {
			$this->assertContains(GodDoesNotAcceptException::class, $errorTypes);
		}

		//Size of the collection should not change
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	public function provideChangeFriendshipValueKO(): array
	{
		$allNull = [
			null,
			[Friend::FIELD_ID => null]
		];
		$nullId = [
			78,
			[Friend::FIELD_ID => null]
		];
		$nullFriendshipValue = [
			null,
			[Friend::FIELD_TYPE => 'HOOMAN']
		];
		$nonNumericalFriendshipValue = [
			"friendshipValue",
			[Friend::FIELD_TYPE => 'HOOMAN']
		];
		$tooHighFriendshipValue = [
			150,
			[Friend::FIELD_TYPE => 'HOOMAN']
		];
		$tooLowFriendshipValue = [
			-1,
			[Friend::FIELD_TYPE => 'HOOMAN']
		];

		return [
			$allNull,
			$nullId,
			$nullFriendshipValue,
			$nonNumericalFriendshipValue,
			$tooHighFriendshipValue,
			$tooLowFriendshipValue
		];
	}
}
