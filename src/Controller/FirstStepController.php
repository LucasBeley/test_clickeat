<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\FriendshipTooHighException;
use App\Exception\FriendshipTooLowException;
use App\Exception\NoFriendshipValueForFriendException;
use App\Exception\NoNameForFriendException;
use App\Exception\NoTypeForFriendException;
use App\Exception\WrongTypeForFriendException;
use App\Exception\WrongTypeForParameterException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstStepController extends AbstractController
{
	/**
	 * Add a friend to Poppy, using url params 'name', 'type', 'friendshipvalue' and 'tags'
	 *
	 * @Route(name="createFriend", path="/create_friend")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws MongoDBException
	 */
	public function createFriend(Request $request, DocumentManager $dm): Response
	{
		$errors = $this->validateParameters(
			$request->get('name'),
			$request->get('type'),
			$request->get('friendshipvalue'),
			$request->get('tags')
		);

		if (empty($errors)) {
			$friend = new Friend();
			$friend->setName($request->get('name'));
			$friend->setType($request->get('type'));
			$friend->setFriendshipValue($request->get('friendshipvalue'));
			$friend->setTags($request->get('tags'));

			$dm->persist($friend);
			$dm->flush();

			return $this->json($friend);
		}

		return $this->json($errors);
	}

	private function validateParameters($name, $type, $friendshipvalue, $tags): array
	{
		$errors = [];

		if ($name === null || $name === "") {
			$this->addException(new NoNameForFriendException(), $errors);
		} else if (!is_string($name)) {
			$this->addException(new WrongTypeForParameterException('name', gettype($name), 'string'), $errors);
		}

		if ($type === null || $type === "") {
			$this->addException(new NoTypeForFriendException(), $errors);
		} else if (!is_string($type)) {
			$this->addException(new WrongTypeForParameterException('type', gettype($name), 'string'), $errors);
		} else if (!in_array($type, Friend::TYPES)) {
			$this->addException(new WrongTypeForFriendException(), $errors);
		}

		if ($friendshipvalue === null) {
			$this->addException(new NoFriendshipValueForFriendException(), $errors);
		} else if (!is_numeric($friendshipvalue)) {
			$this->addException(new WrongTypeForParameterException('friendshipvalue', gettype($friendshipvalue), 'integer'), $errors);
		} else if ($friendshipvalue < 0) {
			$this->addException(new FriendshipTooLowException(), $errors);
		} else if ($friendshipvalue > 100) {
			$this->addException(new FriendshipTooHighException(), $errors);
		}

		if ($tags !== null && !is_array($tags)) {
			$this->addException(new WrongTypeForParameterException('tags', gettype($tags), 'array'), $errors);
		}

		return $errors;
	}

	private function addException(Exception $exception, &$errors) {
		$errors['errors'][] = [
			"exception" => get_class($exception),
			"message" => $exception->getMessage()
		];
	}
}