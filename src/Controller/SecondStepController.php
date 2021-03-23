<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Exception\GodDoesNotAcceptException;
use App\Exception\WrongTypeForParameterException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecondStepController extends AbstractController
{
	/**
	 * Call the monster that will eat a friend
	 *
	 * @Route(name="callTheMonster", path="/call_the_monster")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws MongoDBException
	 * @throws Exception
	 */
	public function callTheMonster(Request $request, DocumentManager $dm): Response
	{
		$friendRepository = $dm->getRepository(Friend::class);
		$json = [];

		//Return error if no Friend in DB
		if (count($friendRepository->findAll()) === 0) {
			$this->addException(new EmptyDBException(), $json);
		}

		//Validate 'id' if given
		$id = $request->get('id');
		if ($id !== null && !is_string($id)) {
			$this->addException(new WrongTypeForParameterException('id', gettype($id), 'string'), $errors);
		}

		if (empty($json)) {
			if ($id) {
				$eaten = $friendRepository->find($id);
			} else {
				//Draw a random Friend
				$builder = $dm->createAggregationBuilder(Friend::class);
				$builder->hydrate(Friend::class);
				$builder->sample(1);
				/** @var Friend $eaten */
				$eaten = $builder->getAggregation()->getIterator()->current();
			}

			//Specific return for Gods and Unicorns
			switch ($eaten->getType()) {
				case "GOD":
					$this->addException(new GodDoesNotAcceptException(), $json);
					break;
				case "UNICORN":
					$json["unicornPower"] = "Unicorn's are eternal, they always survive the monster.";
					break;
				default:
					$eaten->setEaten(true);
					$dm->flush();
					$json = $eaten;
					break;
			}
		}
		return $this->json($json);
	}

	/**
	 * List all eaten friends
	 *
	 * @Route(name="listEaten", path="/list_eaten")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 */
	public function listEaten(Request $request, DocumentManager $dm): Response
	{
		$friendRepository = $dm->getRepository(Friend::class);
		$json = [];

		$allEaten = $friendRepository->findBy([Friend::FIELD_EATEN => true]);
		if (count($allEaten) === 0) {
			$json['emptyStomach'] = "No one has been eaten .... yet !";
		} else {
			$json = $allEaten;
		}

		return $this->json($json);
	}

	/**
	 * Change friendship value
	 *
	 * @Route(name="changeFriendshipValue", path="/change_friendship_value")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws MongoDBException
	 */
	public function changeFriendshipValue(Request $request, DocumentManager $dm): Response
	{
		$friendRepository = $dm->getRepository(Friend::class);
		$json = [];

		$id = $request->get(Friend::FIELD_ID);
		$friendshipValue = $request->get(Friend::FIELD_FRIENDSHIP_VALUE);

		$friend = $friendRepository->find($id);
		$friend->setFriendshipValue($friendshipValue);

		$dm->flush();

		$json = $friend;

		return $this->json($json);
	}

	private function addException(Exception $exception, &$errors)
	{
		$errors['errors'][] = [
			"exception" => get_class($exception),
			"message" => $exception->getMessage()
		];
	}
}