<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Exception\FriendNotFoundException;
use App\Exception\FriendshipOutOfBoundsException;
use App\Exception\GodDoesNotAcceptException;
use App\Exception\MissingParametersException;
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
				$eaten = $friendRepository->pickOne();
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
	 * List all eaten friends, if no one has been eaten, specific return
	 *
	 * @Route(name="listEaten", path="/list_eaten")
	 * @param DocumentManager $dm
	 * @return Response
	 */
	public function listEaten(DocumentManager $dm): Response
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

		//Valid param id
		if ($id === null || $id === "") {
			$this->addException(new MissingParametersException(Friend::FIELD_ID), $json);
		}

		//Valid param friendshipValue
		if ($friendshipValue === null || $friendshipValue === "") {
			$this->addException(new MissingParametersException(Friend::FIELD_FRIENDSHIP_VALUE), $json);
		} else if (!is_numeric($friendshipValue)) {
			$this->addException(new WrongTypeForParameterException(Friend::FIELD_FRIENDSHIP_VALUE, gettype($friendshipValue), 'integer'), $json);
		} else if ($friendshipValue < Friend::MIN_FRIENDSHIP_VALUE || $friendshipValue > Friend::MAX_FRIENDSHIP_VALUE) {
			$this->addException(new FriendshipOutOfBoundsException(), $json);
		}

		//If there was no error to that point
		if(empty($json)) {
			$friend = $friendRepository->find($id);
			if ($friend === null) {
				$this->addException(new FriendNotFoundException(), $json);
			} else if ($friend->getType() === "GOD") {
				$this->addException(new GodDoesNotAcceptException(), $json);
			} else {
				$friend->setFriendshipValue($friendshipValue);
				$dm->flush();
				$json = $friend;
			}
		}

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