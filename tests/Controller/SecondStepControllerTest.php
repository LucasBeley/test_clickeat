<?php

namespace App\Tests\Controller;

use App\DataFixture\FriendsFixture;
use App\Document\Friend;
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
	 * @dataProvider provideLoadFixtures
	 * @param bool $loadFixtures
	 */
	public function testCallTheMonster(bool $loadFixtures)
	{
		$serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
		if ($loadFixtures) {
			$this->loadFixtures([FriendsFixture::class], false, self::DEFAULT_DOC_MANAGER_SERVICE);
		}
		$repo = $this->documentManager->getRepository(Friend::class);
		$originalSizeDb = count($repo->findAll());

		//Execute request
		self::$client->request('GET', '/call_the_monster');

		//HTTP response is OK
		$this->assertEquals(200, self::$client->getResponse()->getStatusCode());

		//Returned object can be unserialized in a Friend document
		if ($loadFixtures) {
			try {
				/** @var Friend $responseContent */
				$responseContent = $serializer->deserialize(self::$client->getResponse()->getContent(), Friend::class, 'json');
				$this->assertInstanceOf(Friend::class, $responseContent);
			} catch (Exception $exception) {
				$this->fail("Unserialization of json to Friend document failed.");
			}
		}

		$this->assertCount($originalSizeDb - $loadFixtures, $repo->findAll());
	}

	public function provideLoadFixtures(): array
	{
		return [
			true,
			false
		];
	}
}
