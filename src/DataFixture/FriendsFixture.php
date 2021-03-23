<?php


namespace App\DataFixture;


use App\Document\Friend;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Graviton\MongoDB\Fixtures\FixtureInterface;

class FriendsFixture extends Fixture implements FixtureInterface
{
	private array $types = ["UNICORN", "GOD", "HOOMAN", "NOOB"];

	/**
	 * @param ObjectManager $manager
	 * @throws Exception
	 */
	public function load(ObjectManager $manager) {

		for($i = 0; $i < 1000; $i++) {
			$friend = new Friend();
			$friend->setName("Friend ".$i);
			$friend->setType($this->types[random_int(0, count($this->types) - 1)]);
			$friend->setFriendshipValue(random_int(0, 100));
			$nbTags = random_int(0, 6);
			$tags = [];
			for($j = 0; $j < $nbTags; $j++) {
				$tags[] = "Tag ".random_int(0, 10);
			}
			$friend->setTags($tags);

			$manager->persist($friend);
		}

		$godFriend = new Friend();
		$godFriend->setName("GodFriend");
		$godFriend->setType("GOD");
		$godFriend->setFriendshipValue(56);
		$godFriend->setTags(["Tag 51", "Tag 10"]);
		$manager->persist($godFriend);

		$hoomanFriend = new Friend();
		$hoomanFriend->setName("HoomanFriend");
		$hoomanFriend->setType("HOOMAN");
		$hoomanFriend->setFriendshipValue(64);
		$hoomanFriend->setTags(["Tag 1", "Tag 2", "Tag 3"]);
		$manager->persist($hoomanFriend);

		$noobFriend = new Friend();
		$noobFriend->setName("NoobFriend");
		$noobFriend->setType("NOOB");
		$noobFriend->setFriendshipValue(100);
		$noobFriend->setTags(["Tag 1", "Tag 2"]);
		$manager->persist($noobFriend);

		$unicornFriend = new Friend();
		$unicornFriend->setName("UnicornFriend");
		$unicornFriend->setType("UNICORN");
		$unicornFriend->setFriendshipValue(0);
		$unicornFriend->setTags();
		$manager->persist($unicornFriend);

		$manager->flush();
	}
}