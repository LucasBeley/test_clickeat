<?php


namespace App\DataFixture;


use App\Document\Friend;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Graviton\MongoDB\Fixtures\FixtureInterface;

class NotRandomFriendsFixture extends Fixture implements FixtureInterface
{
	/**
	 * Predetermine fixture to test value returns
	 *
	 * @param ObjectManager $manager
	 * @throws Exception
	 */
	public function load(ObjectManager $manager) {

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