<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\EmptyDBException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecondStepController extends AbstractController
{
	/**
	 * Call the monster that will eat a friend
	 *
	 * @Route(name="callTheMonster", path="/call_the_monster")
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws MongoDBException
	 * @throws Exception
	 */
	public function callTheMonster(DocumentManager $dm): Response
	{
		$friendRepository = $dm->getRepository(Friend::class);
		$errors = [];
		if (count($friendRepository->findAll()) === 0) {
			$this->addException(new EmptyDBException(), $errors);
		}

		if (empty($errors)) {
			$builder = $dm->createAggregationBuilder(Friend::class);
			$builder->hydrate(Friend::class);
			$builder->sample(1);
			$eaten = $builder->getAggregation()->getIterator()->current();
			$dm->remove($eaten);
			$dm->flush();

			return $this->json($eaten);
		}

		return $this->json($errors);
	}

	private function addException(Exception $exception, &$errors)
	{
		$errors['errors'][] = [
			"exception" => get_class($exception),
			"message" => $exception->getMessage()
		];
	}
}