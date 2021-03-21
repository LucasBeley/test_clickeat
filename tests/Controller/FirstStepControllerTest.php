<?php


namespace App\Tests\Controller;


use App\DataFixture\FriendsFixture;
use App\Document\Friend;
use App\Exception\FriendshipTooHighException;
use App\Exception\FriendshipTooLowException;
use App\Exception\NoFriendshipValueForFriendException;
use App\Exception\NoNameForFriendException;
use App\Exception\NoTypeForFriendException;
use App\Exception\WrongTypeForFriendException;
use Exception;
use Graviton\MongoDB\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class FirstStepControllerTest extends WebTestCase
{
	use FixturesTrait;

	/**
	 * @dataProvider provideFriendsWithEveryPropertyGood
	 * @param array $friend
	 */
	public function testCreateFriend(array $friend) {
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		$client = static::createClient();

		if (!array_key_exists('name', $friend)) {
			$this->expectException(NoNameForFriendException::class);
		}
		if (!array_key_exists('type', $friend)) {
			$this->expectException(NoTypeForFriendException::class);
		}
		if (!array_key_exists('friendshipvalue', $friend)) {
			$this->expectException(NoFriendshipValueForFriendException::class);
		}

		$client->request('GET', '/create_friend', $friend);

		//HTTP response is OK
		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		//Returned object can be unserialized in a Friend document
		try {
			/** @var Friend $responseContent */
			$responseContent = $serializer->deserialize($client->getResponse()->getContent(), Friend::class, 'json');
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
	}

	/**
	 * @dataProvider provideFriendsWithPropertyIssues
	 * @param array $friend
	 */
	public function testCreateFriendWithPropertyIssues(array $friend) {
		$client = static::createClient();

		$client->request('GET', '/create_friend', $friend);

		//HTTP response is OK
		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$responseContent = json_decode($client->getResponse()->getContent(), true);

		//There should be at least an error
		$this->assertArrayHasKey('errors', $responseContent);

		$errorTypes = array_map(function ($element) {
			return $element['exception'];
		}, $responseContent['errors']);

		//Check error returned for name
		if (!array_key_exists('name', $friend)) {
			$this->assertContains(NoNameForFriendException::class, $errorTypes);
		}

		//Check error returned for type
		if (!array_key_exists('type', $friend)) {
			$this->assertContains(NoTypeForFriendException::class, $errorTypes);
		} else if (!in_array($friend['type'], Friend::TYPES)) {
			$this->assertContains(WrongTypeForFriendException::class, $errorTypes);
		}

		//Check error returned for friendshipvalue
		if (!array_key_exists('friendshipvalue', $friend)) {
			$this->assertContains(NoFriendshipValueForFriendException::class, $errorTypes);
		} else if ($friend['friendshipvalue'] < 0) {
			$this->assertContains(FriendshipTooLowException::class, $errorTypes);
		} else if ($friend['friendshipvalue'] > 100) {
			$this->assertContains(FriendshipTooHighException::class, $errorTypes);
		}
	}




	public function provideFriendsWithEveryPropertyGood(): array
	{
		//TODO Test wrong value type, wrong parameters name, blank param instead of null, test changing size of db

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

		$friendWithoutName = [
			'type' => "GOD",
			'friendshipvalue' => 4,
			'tags' => ["Tag 1", "Tag 3"],
		];

		$friendWithoutType = [
			'name' => "FriendWithoutType",
			'friendshipvalue' => 99,
			'tags' => ["Tag 1", "Tag 2"],
		];

		$friendWithWrongType = [
			'name' => "FriendWithWrongType",
			'type' => "WRONGTYPE",
			'friendshipvalue' => 45,
			'tags' => ["Tag 3"],
		];

		$friendWithoutFriendship = [
			'name' => "FriendWithoutFriendship",
			'type' => "UNICORN",
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

		return [
			[$friendWithAllNull],
			[$friendWithoutName],
			[$friendWithoutType],
			[$friendWithWrongType],
			[$friendWithoutFriendship],
			[$friendWithTooHighFriendship],
			[$friendWithTooLowFriendship]
		];
	}
}