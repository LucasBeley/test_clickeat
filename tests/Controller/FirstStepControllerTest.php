<?php


namespace App\Tests\Controller;


use App\DataFixture\FriendsFixture;
use App\Document\Friend;
use App\Exception\FriendshipOutOfBoundsException;
use App\Exception\MissingParametersException;
use App\Exception\InvalidTypeOfFriendException;
use App\Exception\WrongTypeForParameterException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Graviton\MongoDB\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class FirstStepControllerTest extends WebTestCase
{
	use FixturesTrait;

	const DEFAULT_DOC_MANAGER_SERVICE = 'doctrine_mongodb.odm.default_document_manager';

	private static ?KernelBrowser $client = null;

	/**
	 * @var DocumentManager
	 */
	private $documentManager;

	protected function setUp(): void
	{
		self::$client = static::createClient();

		$this->documentManager = self::$client->getContainer()
			->get(self::DEFAULT_DOC_MANAGER_SERVICE);
		$this->mongoDbPurge($this->documentManager);
	}

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
		$this->assertEquals($friend['name'], $responseContent->getName());
		$this->assertEquals($friend['type'], $responseContent->getType());
		$this->assertEquals($friend['friendshipValue'], $responseContent->getFriendshipValue());
		if (array_key_exists('tags', $friend)) {
			$this->assertEquals($friend['tags'], $responseContent->getTags());
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

		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		//Check errors returned for name
		if (!array_key_exists('name', $friend) || $friend['name'] === "") {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_string($friend['name'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		}

		//Check errors returned for type
		if (!array_key_exists('type', $friend) || $friend['type'] === "") {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_string($friend['type'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if (!in_array($friend['type'], Friend::TYPES)) {
			$this->assertContains(InvalidTypeOfFriendException::class, $errorTypes);
		}

		//Check errors returned for friendshipValue
		if (!array_key_exists('friendshipValue', $friend)) {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if (!is_numeric($friend['friendshipValue'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if ($friend['friendshipValue'] < 0 || $friend['friendshipValue'] > 100) {
			$this->assertContains(FriendshipOutOfBoundsException::class, $errorTypes);
		}

		//Check errors returned for tags
		if (array_key_exists('tags', $friend) && !is_array($friend['tags'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		}

		//Check nothing went in DB
		$this->assertCount($originalSizeDb, $repo->findAll());
	}

	public function provideFriendsWithEveryPropertyGood(): array
	{
		$friendWithAllGood = [
			'name' => "FriendWithAllGood",
			'type' => "HOOMAN",
			'friendshipValue' => 50,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithNoTags = [
			'name' => "FriendWithNoTags",
			'type' => "GOD",
			'friendshipValue' => 14
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
			'name' => "",
			'type' => "",
			'friendshipValue' => 67,
			'tags' => [],
		];

		$friendWithoutName = [
			'type' => "GOD",
			'friendshipValue' => 4,
			'tags' => ["Tag 1", "Tag 3"],
		];

		$friendWithBlankName = [
			'name' => "",
			'type' => "GOD",
			'friendshipValue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongNameType = [
			'name' => [],
			'type' => "GOD",
			'friendshipValue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithoutType = [
			'name' => "FriendWithoutType",
			'friendshipValue' => 99,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithBlankType = [
			'name' => "FriendWithBlankType",
			'type' => "",
			'friendshipValue' => 94,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongType = [
			'name' => "FriendWithWrongType",
			'type' => "WRONGTYPE",
			'friendshipValue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithWrongTypeType = [
			'name' => "FriendWithWrongTypeType",
			'type' => [],
			'friendshipValue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithoutFriendship = [
			'name' => "FriendWithoutFriendship",
			'type' => "UNICORN",
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongFriendshipType = [
			'name' => "FriendWithWrongFriendshipType",
			'type' => "UNICORN",
			'friendshipValue' => "test",
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithTooHighFriendship = [
			'name' => "FriendWithTooHighFriendship",
			'type' => "NOOB",
			'friendshipValue' => 150,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithTooLowFriendship = [
			'name' => "FriendWithTooLowFriendship",
			'type' => "NOOB",
			'friendshipValue' => -1,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongTagsType = [
			'name' => "FriendWithWrongTagsType",
			'type' => "NOOB",
			'friendshipValue' => 35,
			'tags' => 687,
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
		$expectedResults = $friendRepository->findBy($criteria);

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
			if (array_key_exists('name', $criteria)) {
				$this->assertEquals($criteria['name'], $friendAsserted->getName());
			}
			if (array_key_exists('type', $criteria)) {
				$this->assertEquals($criteria['type'], $friendAsserted->getType());
			}
			if (array_key_exists('friendshipValue', $criteria)) {
				$this->assertEquals($criteria['friendshipValue'], $friendAsserted->getFriendshipValue());
			}
			if (array_key_exists('tags', $criteria)) {
				foreach ($criteria['tags'] as $tag) {
					$this->assertContains($tag, $friendAsserted->getType());
				}
			}
		}
	}

	public function provideCriteriaForListFriends()
	{
		$friendWithAllGood = [
			'name' => "FriendWithAllGood",
			'type' => "HOOMAN",
			'friendshipValue' => 50,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithNoTags = [
			'name' => "FriendWithNoTags",
			'type' => "GOD",
			'friendshipValue' => 14
		];

		$friendWithAllNull = [];

		$friendWithAllBlank = [
			'name' => "",
			'type' => "",
			'friendshipValue' => 67,
			'tags' => [],
		];

		$friendWithoutName = [
			'type' => "GOD",
			'friendshipValue' => 4,
			'tags' => ["Tag 1", "Tag 3"],
		];

		$friendWithBlankName = [
			'name' => "",
			'type' => "GOD",
			'friendshipValue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongNameType = [
			'name' => [],
			'type' => "GOD",
			'friendshipValue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithoutType = [
			'name' => "FriendWithoutType",
			'friendshipValue' => 99,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithBlankType = [
			'name' => "FriendWithBlankType",
			'type' => "",
			'friendshipValue' => 94,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongType = [
			'name' => "FriendWithWrongType",
			'type' => "WRONGTYPE",
			'friendshipValue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithWrongTypeType = [
			'name' => "FriendWithWrongTypeType",
			'type' => [],
			'friendshipValue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithoutFriendship = [
			'name' => "FriendWithoutFriendship",
			'type' => "UNICORN",
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongFriendshipType = [
			'name' => "FriendWithWrongFriendshipType",
			'type' => "UNICORN",
			'friendshipValue' => "test",
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithTooHighFriendship = [
			'name' => "FriendWithTooHighFriendship",
			'type' => "NOOB",
			'friendshipValue' => 150,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithTooLowFriendship = [
			'name' => "FriendWithTooLowFriendship",
			'type' => "NOOB",
			'friendshipValue' => -1,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongTagsType = [
			'name' => "FriendWithWrongTagsType",
			'type' => "NOOB",
			'friendshipValue' => 35,
			'tags' => 687,
		];

		return [
			[$friendWithAllGood],
			[$friendWithNoTags],
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
}