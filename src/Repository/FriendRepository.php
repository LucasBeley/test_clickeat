<?php


namespace App\Repository;


use App\Document\Friend;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Exception;

class FriendRepository extends DocumentRepository
{
	/**
	 * Get the life of one side of the battle, Poppy vs friends
	 *
	 * @param array $eaten
	 * @return int
	 * @throws Exception
	 */
	public function getSideLife(array $eaten): int
	{
		$builder = $this->getDocumentManager()->createAggregationBuilder(Friend::class);
		$builder
			->match()
				->field(Friend::FIELD_EATEN)
				->in($eaten)
				->field(Friend::FIELD_TYPE)
				->in(['HOOMAN', 'NOOB'])
			->group()
				->field('_id')
				->expression('id')
				->field('sum')
				->sum('$'.Friend::FIELD_FRIENDSHIP_VALUE)
			->project()
				->excludeFields(['_id'])
				->includeFields(['sum']);
		$result = $builder->getAggregation()->getIterator()->current();
		return $result ? $result['sum'] : 0;
	}

	/**
	 * Pick a random Friend
	 *
	 * @return Friend
	 * @throws Exception
	 */
	public function pickOne() : Friend
	{
		$builder = $this->getDocumentManager()->createAggregationBuilder(Friend::class);
		$builder->hydrate(Friend::class);
		$builder->sample(1);
		/** @var Friend $eaten */
		return $builder->getAggregation()->getIterator()->current();
	}
}