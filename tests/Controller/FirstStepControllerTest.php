<?php


namespace App\Tests\Controller;


use App\DataFixture\FriendsFixture;
use App\Document\Friend;
use App\Exception\FriendshipOutOfBoundsException;
use App\Exception\MissingParametersException;
use App\Exception\InvalidTypeOfFriendException;
use App\Exception\WrongTypeForParameterException;
use App\Tests\TestCase\ControllerTestCase;
use Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class FirstStepControllerTest extends ControllerTestCase
{
	/**
	 * Test the creation of a friend that should be OK
	 * @dataProvider provideFriendsWithEveryPropertyGood
	 * @param array $friend
	 */
	public function testCreateFriend(array $friend)
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/create_friend', $friend);

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

		//Object properties are the ones given in the request
		$this->assertNotNull($responseContent->getId());
		$this->assertEquals($friend[Friend::FIELD_NAME], $responseContent->getName());
		$this->assertEquals($friend[Friend::FIELD_TYPE], $responseContent->getType());
		$this->assertEquals($friend[Friend::FIELD_FRIENDSHIP_VALUE], $responseContent->getFriendshipValue());
		if (array_key_exists(Friend::FIELD_TAGS, $friend)) {
			$this->assertEquals($friend[Friend::FIELD_TAGS], $responseContent->getTags());
		} else {
			$this->assertNull($responseContent->getTags());
		}

		$this->assertCount($originalSizeDb + 1, $repo->findAll());
	}

	/**
	 * Test the creation of a friend that should be KO
	 *
	 * @dataProvider provideFriendsWithPropertyIssues
	 * @param array $friend
	 */
	public function testCreateFriendWithPropertyIssues(array $friend)
	{
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/create_friend', $friend);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		$responseContent = json_decode(self::$client->getResponse()->getContent(), true);

		//There should be at least an error
		$this->assertArrayHasKey('errors', $responseContent);

		//Map types of error in an array to simplify the check
		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		//Check errors returned for name
		if (!array_key_exists(Friend::FIELD_NAME, $friend) || $friend[Friend::FIELD_NAME] === "") {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_string($friend[Friend::FIELD_NAME])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		}

		//Check errors returned for type
		if (!array_key_exists(Friend::FIELD_TYPE, $friend) || $friend[Friend::FIELD_TYPE] === "") {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_string($friend[Friend::FIELD_TYPE])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if (!in_array($friend[Friend::FIELD_TYPE], Friend::TYPES)) {
			$this->assertContains(InvalidTypeOfFriendException::class, $errorTypes);
		}

		//Check errors returned for friendshipValue
		if (!array_key_exists(Friend::FIELD_FRIENDSHIP_VALUE, $friend)) {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_numeric($friend[Friend::FIELD_FRIENDSHIP_VALUE])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if ($friend[Friend::FIELD_FRIENDSHIP_VALUE] < 0 || $friend[Friend::FIELD_FRIENDSHIP_VALUE] > 100) {
			$this->assertContains(FriendshipOutOfBoundsException::class, $errorTypes);
		}

		//Check errors returned for tags
		if (array_key_exists(Friend::FIELD_TAGS, $friend) && !is_array($friend[Friend::FIELD_TAGS])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		}

		//Check nothing went in DB
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	public function provideFriendsWithEveryPropertyGood(): array
	{
		$friendWithAllGood = [
			Friend::FIELD_NAME => "FriendWithAllGood",
			Friend::FIELD_TYPE => "HOOMAN",
			Friend::FIELD_FRIENDSHIP_VALUE => 50,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithNoTags = [
			Friend::FIELD_NAME => "FriendWithNoTags",
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 14
		];

		return [
			[$friendWithAllGood],
			[$friendWithNoTags]
		];
	}

	public function provideFriendsWithPropertyIssues(): array
	{
		$friendWithAllNull = [];

		$friendWithAllBlank = [
			Friend::FIELD_NAME => "",
			Friend::FIELD_TYPE => "",
			Friend::FIELD_FRIENDSHIP_VALUE => 67,
			Friend::FIELD_TAGS => [],
		];

		$friendWithoutName = [
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 4,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 3"],
		];

		$friendWithBlankName = [
			Friend::FIELD_NAME => "",
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 43,
			Friend::FIELD_TAGS => ["Tag 1"],
		];

		$friendWithWrongNameType = [
			Friend::FIELD_NAME => [],
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 43,
			Friend::FIELD_TAGS => ["Tag 1"],
		];

		$friendWithoutType = [
			Friend::FIELD_NAME => "FriendWithoutType",
			Friend::FIELD_FRIENDSHIP_VALUE => 99,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2"],
		];

		$friendWithBlankType = [
			Friend::FIELD_NAME => "FriendWithBlankType",
			Friend::FIELD_TYPE => "",
			Friend::FIELD_FRIENDSHIP_VALUE => 94,
			Friend::FIELD_TAGS => ["Tag 1"],
		];

		$friendWithWrongType = [
			Friend::FIELD_NAME => "FriendWithWrongType",
			Friend::FIELD_TYPE => "WRONGTYPE",
			Friend::FIELD_FRIENDSHIP_VALUE => 45,
			Friend::FIELD_TAGS => ["Tag 3"],
		];

		$friendWithWrongTypeType = [
			Friend::FIELD_NAME => "FriendWithWrongTypeType",
			Friend::FIELD_TYPE => [],
			Friend::FIELD_FRIENDSHIP_VALUE => 45,
			Friend::FIELD_TAGS => ["Tag 3"],
		];

		$friendWithoutFriendship = [
			Friend::FIELD_NAME => "FriendWithoutFriendship",
			Friend::FIELD_TYPE => "UNICORN",
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongFriendshipType = [
			Friend::FIELD_NAME => "FriendWithWrongFriendshipType",
			Friend::FIELD_TYPE => "UNICORN",
			Friend::FIELD_FRIENDSHIP_VALUE => "test",
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithTooHighFriendship = [
			Friend::FIELD_NAME => "FriendWithTooHighFriendship",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => 150,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2"],
		];

		$friendWithTooLowFriendship = [
			Friend::FIELD_NAME => "FriendWithTooLowFriendship",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => -1,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongTagsType = [
			Friend::FIELD_NAME => "FriendWithWrongTagsType",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => 35,
			Friend::FIELD_TAGS => 687,
		];

		return [
			[$friendWithAllNull],
			[$friendWithAllBlank],
			[$friendWithoutName],
			[$friendWithBlankName],
			[$friendWithWrongNameType],
			[$friendWithoutType],
			[$friendWithBlankType],
			[$friendWithWrongType],
			[$friendWithWrongTypeType],
			[$friendWithoutFriendship],
			[$friendWithWrongFriendshipType],
			[$friendWithTooHighFriendship],
			[$friendWithTooLowFriendship],
			[$friendWithWrongTagsType],
		];
	}

	/**
	 * Test the listings of all friends
	 */
	public function testListAllFriends()
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$friendRepository = $this->documentManager->getRepository(Friend::class);
		$nbFriendInserted = count($friendRepository->findAll());

		//Execute request
		self::$client->request('GET', '/list_friends');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		$responseContent = json_decode(self::$client->getResponse()->getContent());
		$this->assertIsArray($responseContent);
		$this->assertCount($nbFriendInserted, $responseContent);
		for ($i = 0; $i < count($responseContent); $i++) {
			try {
				/** @var Friend $friend */
				$friend = $serializer->deserialize(json_encode($responseContent[$i]), Friend::class, 'json');
				$this->assertInstanceOf(Friend::class, $friend);
			} catch (Exception $exception) {
				$this->fail("Unserialization of json to Friend document failed.");
			}
		}
	}

	/**
	 * Test the listings of all friends with no friends inserted
	 */
	public function testListFriendWithNoFriendsInserted()
	{
		//Execute request
		self::$client->request('GET', '/list_friends');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		$responseContent = json_decode(self::$client->getResponse()->getContent());
		$this->assertIsArray($responseContent);
		$this->assertCount(0, $responseContent);
	}

	/**
	 * @dataProvider provideCriteriaForListFriends
	 * @param array $criteria
	 */
	public function testListFriendsWithCriteria(array $criteria)
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		$friendRepository = $this->documentManager->getRepository(Friend::class);
		$copyCriteria = $criteria;
		if (array_key_exists('tags', $copyCriteria) && is_array($copyCriteria['tags'])) {
			$copyCriteria['tags'] = ['$all' => $criteria['tags']];
		}
		$expectedResults = $friendRepository->findBy($copyCriteria);

		//Execute request
		self::$client->request('GET', '/list_friends', $criteria);

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		$responseContent = json_decode(self::$client->getResponse()->getContent());
		$this->assertIsArray($responseContent);
		$this->assertCount(count($expectedResults), $responseContent);
		$friends = [];
		for ($i = 0; $i < count($responseContent); $i++) {
			try {
				/** @var Friend $friend */
				$friend = $serializer->deserialize(json_encode($responseContent[$i]), Friend::class, 'json');
				$this->assertInstanceOf(Friend::class, $friend);
				$friends[] = $friend;
			} catch (Exception $exception) {
				$this->fail("Unserialization of json to Friend document failed.");
			}
		}

		/** @var Friend $friendAsserted */
		foreach ($friends as $friendAsserted) {
			if (array_key_exists(Friend::FIELD_NAME, $criteria)) {
				$this->assertEquals($criteria[Friend::FIELD_NAME], $friendAsserted->getName());
			}
			if (array_key_exists(Friend::FIELD_TYPE, $criteria)) {
				$this->assertEquals($criteria[Friend::FIELD_TYPE], $friendAsserted->getType());
			}
			if (array_key_exists(Friend::FIELD_FRIENDSHIP_VALUE, $criteria)) {
				$this->assertEquals($criteria[Friend::FIELD_FRIENDSHIP_VALUE], $friendAsserted->getFriendshipValue());
			}
			if (array_key_exists(Friend::FIELD_TAGS, $criteria)) {
				foreach ($criteria[Friend::FIELD_TAGS] as $tag) {
					$this->assertContains($tag, $friendAsserted->getTags());
				}
			}
		}
	}

	public function provideCriteriaForListFriends(): array
	{
		$friendWithAllGood = [
			Friend::FIELD_NAME => "FriendWithAllGood",
			Friend::FIELD_TYPE => "HOOMAN",
			Friend::FIELD_FRIENDSHIP_VALUE => 50,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithNoTags = [
			Friend::FIELD_NAME => "FriendWithNoTags",
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 14
		];

		$friendWithAllNull = [];


		$friendWithoutName = [
			Friend::FIELD_TYPE => "GOD",
			Friend::FIELD_FRIENDSHIP_VALUE => 4,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 3"],
		];

		$friendWithoutType = [
			Friend::FIELD_NAME => "FriendWithoutType",
			Friend::FIELD_FRIENDSHIP_VALUE => 99,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2"],
		];

		$friendWithWrongType = [
			Friend::FIELD_NAME => "FriendWithWrongType",
			Friend::FIELD_TYPE => "WRONGTYPE",
			Friend::FIELD_FRIENDSHIP_VALUE => 45,
			Friend::FIELD_TAGS => ["Tag 3"],
		];

		$friendWithoutFriendship = [
			Friend::FIELD_NAME => "FriendWithoutFriendship",
			Friend::FIELD_TYPE => "UNICORN",
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongFriendshipType = [
			Friend::FIELD_NAME => "FriendWithWrongFriendshipType",
			Friend::FIELD_TYPE => "UNICORN",
			Friend::FIELD_FRIENDSHIP_VALUE => "test",
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithTooHighFriendship = [
			Friend::FIELD_NAME => "FriendWithTooHighFriendship",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => 150,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2"],
		];

		$friendWithTooLowFriendship = [
			Friend::FIELD_NAME => "FriendWithTooLowFriendship",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => -1,
			Friend::FIELD_TAGS => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongTagsType = [
			Friend::FIELD_NAME => "FriendWithWrongTagsType",
			Friend::FIELD_TYPE => "NOOB",
			Friend::FIELD_FRIENDSHIP_VALUE => 35,
			Friend::FIELD_TAGS => 687,
		];

		return [
			[$friendWithAllGood],
			[$friendWithNoTags],
			[$friendWithAllNull],
			[$friendWithoutName],
			[$friendWithoutType],
			[$friendWithWrongType],
			[$friendWithoutFriendship],
			[$friendWithWrongFriendshipType],
			[$friendWithTooHighFriendship],
			[$friendWithTooLowFriendship],
			[$friendWithWrongTagsType],
		];
	}
}