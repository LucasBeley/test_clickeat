<?php


namespace App\DataFixture;


use App\Document\Friend;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Graviton\MongoDB\Fixtures\FixtureInterface;

class FriendsFixture extends Fixture implements FixtureInterface
{
	/**
	 * The majority of humans and noob are eaten
	 * Also, each type oh friend is represented in a predetermined way
	 *
	 * @param ObjectManager $manager
	 * @throws Exception
	 */
	public function load(ObjectManager $manager) {

		for($i = 0; $i < 400; $i++) {
			$friend = new Friend();
			$friend->setName("Friend ".$i);
			$friend->setType(Friend::TYPES[random_int(0, count(Friend::TYPES) - 1)]);
			$friend->setFriendshipValue(random_int(Friend::MIN_FRIENDSHIP_VALUE, Friend::MAX_FRIENDSHIP_VALUE));
			$nbTags = random_int(0, 6);
			$tags = [];
			for($j = 0; $j < $nbTags; $j++) {
				$tags[] = "Tag ".random_int(0, 10);
			}
			$friend->setTags($tags);
			$friend->setEaten($i > 300 && !in_array($friend->getType(), ['GOD', 'UNICORN']));

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
		$unicornFriend->setTags(null);
		$manager->persist($unicornFriend);

		$eatenFriend = new Friend();
		$eatenFriend->setName("EatenFriend");
		$eatenFriend->setType("NOOB");
		$eatenFriend->setFriendshipValue(78);
		$eatenFriend->setTags(null);
		$eatenFriend->setEaten(true);
		$manager->persist($eatenFriend);

		$manager->flush();
	}
}