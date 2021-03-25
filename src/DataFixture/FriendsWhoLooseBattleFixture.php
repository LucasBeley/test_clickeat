<?php


namespace App\DataFixture;


use App\Document\Friend;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Graviton\MongoDB\Fixtures\FixtureInterface;

class FriendsWhoLooseBattleFixture extends Fixture implements FixtureInterface
{
	/**
	 * @param ObjectManager $manager
	 * @throws Exception
	 */
	public function load(ObjectManager $manager) {

		for($i = 0; $i < 600; $i++) {
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
			$friend->setEaten($i < 500 && !in_array($friend->getType(), ['GOD', 'UNICORN']));

			$manager->persist($friend);
		}
		$manager->flush();
	}
}