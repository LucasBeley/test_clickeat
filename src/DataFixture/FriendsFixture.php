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
		$manager->flush();
	}
}