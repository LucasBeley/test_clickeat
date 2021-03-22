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

	private static ?KernelBrowser $client = null;

	/**
	 * @var DocumentManager
	 */
	private $documentManager;

	protected function setUp(): void
	{
		self::$client = static::createClient();

		$this->documentManager = self::$client->getContainer()
			->get('doctrine_mongodb.odm.default_document_manager');
		$this->mongoDbPurge($this->documentManager);
	}

	/**
	 * Test the creation of a friend that should be OK
	 * @dataProvider provideFriendsWithEveryPropertyGood
	 * @param array $friend
	 */
	public function testCreateFriend(array $friend) {
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
		} catch (Exception $exception ) {
			$this->fail("Unserialization of json to Friend document failed.");
		}

		//Object properties are the ones given in the request
		$this->assertNotNull($responseContent->getId());
		$this->assertEquals($friend['name'], $responseContent->getName());
		$this->assertEquals($friend['type'], $responseContent->getType());
		$this->assertEquals($friend['friendshipvalue'], $responseContent->getFriendshipValue());
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
	public function testCreateFriendWithPropertyIssues(array $friend) {
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
		} else if(!is_string($friend['name'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		}

		//Check errors returned for type
		if (!array_key_exists('type', $friend) || $friend['type'] === "") {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if(!is_string($friend['type'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if (!in_array($friend['type'], Friend::TYPES)) {
			$this->assertContains(InvalidTypeOfFriendException::class, $errorTypes);
		}

		//Check errors returned for friendshipvalue
		if (!array_key_exists('friendshipvalue', $friend)) {
			$this->assertContains(MissingParametersException::class, $errorTypes);
		} else if(!is_numeric($friend['friendshipvalue'])) {
			$this->assertContains(WrongTypeForParameterException::class, $errorTypes);
		} else if ($friend['friendshipvalue'] < 0 || $friend['friendshipvalue'] > 100) {
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
			'friendshipvalue' => 50,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithNoTags = [
			'name' => "FriendWithNoTags",
			'type' => "GOD",
			'friendshipvalue' => 14
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
			'friendshipvalue' => 67,
			'tags' => [],
		];

		$friendWithoutName = [
			'type' => "GOD",
			'friendshipvalue' => 4,
			'tags' => ["Tag 1", "Tag 3"],
		];

		$friendWithBlankName = [
			'name' => "",
			'type' => "GOD",
			'friendshipvalue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongNameType = [
			'name' => [],
			'type' => "GOD",
			'friendshipvalue' => 43,
			'tags' => ["Tag 1"],
		];

		$friendWithoutType = [
			'name' => "FriendWithoutType",
			'friendshipvalue' => 99,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithBlankType = [
			'name' => "FriendWithBlankType",
			'type' => "",
			'friendshipvalue' => 94,
			'tags' => ["Tag 1"],
		];

		$friendWithWrongType = [
			'name' => "FriendWithWrongType",
			'type' => "WRONGTYPE",
			'friendshipvalue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithWrongTypeType = [
			'name' => "FriendWithWrongTypeType",
			'type' => [],
			'friendshipvalue' => 45,
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
			'friendshipvalue' => "test",
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithTooHighFriendship = [
			'name' => "FriendWithTooHighFriendship",
			'type' => "NOOB",
			'friendshipvalue' => 150,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithTooLowFriendship = [
			'name' => "FriendWithTooLowFriendship",
			'type' => "NOOB",
			'friendshipvalue' => -1,
			'tags' => ["Tag 1", "Tag 2", "Tag 3"],
		];

		$friendWithWrongTagsType = [
			'name' => "FriendWithWrongTagsType",
			'type' => "NOOB",
			'friendshipvalue' => 35,
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
	public function testListFriends()
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$this->loadFixtures([FriendsFixture::class], false, 'doctrine_mongodb.odm.default_document_manager');

		//Execute request
		self::$client->request('GET', '/list_friends');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object is an array of Friend documents
		try {
			$responseContent = json_decode(self::$client->getResponse()->getContent());
			$this->assertIsArray($responseContent);
			for ($i = 0; $i < count($responseContent); $i++) {
				/** @var Friend $friend */
				$friend = $serializer->deserialize(self::$client->getResponse()->getContent(), Friend::class, 'json');
				$this->assertInstanceOf(Friend::class, $friend);
			}
		} catch (Exception $exception ) {
			$this->fail("Unserialization of json to Friend document failed.");
		}
	}
}