<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\FriendshipOutOfBoundsException;
use App\Exception\MissingParametersException;
use App\Exception\InvalidTypeOfFriendException;
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
	 * Add a friend to Poppy, using url params Friend::FIELD_NAME, Friend::FIELD_TYPE, Friend::FIELD_FRIENDSHIP_VALUE and Friend::FIELD_TAGS
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
			$request->get(Friend::FIELD_NAME),
			$request->get(Friend::FIELD_TYPE),
			$request->get(Friend::FIELD_FRIENDSHIP_VALUE),
			$request->get(Friend::FIELD_TAGS)
		);

		if (empty($errors)) {
			$friend = new Friend();
			$friend->setName($request->get(Friend::FIELD_NAME));
			$friend->setType($request->get(Friend::FIELD_TYPE));
			$friend->setFriendshipValue($request->get(Friend::FIELD_FRIENDSHIP_VALUE));
			$friend->setTags($request->get(Friend::FIELD_TAGS));

			$dm->persist($friend);
			$dm->flush();

			return $this->json($friend);
		}

		return $this->json($errors);
	}

	/**
	 * List Poppy's friends
	 *
	 * @Route(name="listFriends", path="/list_friends")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 */
	public function listFriends(Request $request, DocumentManager $dm): Response
	{
		$friendRepository = $dm->getRepository(Friend::class);

		$name = $request->get(Friend::FIELD_NAME);
		$type = $request->get(Friend::FIELD_TYPE);
		$friendshipValue = $request->get(Friend::FIELD_FRIENDSHIP_VALUE);
		$tags = $request->get(Friend::FIELD_TAGS);

		$criteria = [];
		if ($name) {
			$criteria[Friend::FIELD_NAME] = $name;
		}
		if ($type) {
			$criteria[Friend::FIELD_TYPE] = $type;
		}
		if ($friendshipValue) {
			$criteria[Friend::FIELD_FRIENDSHIP_VALUE] = $friendshipValue;
		}
		if ($tags) {
			$criteria[Friend::FIELD_TAGS] = is_array($tags) ? ['$all' => $tags] : $tags;
		}

		$friends = $friendRepository->findBy($criteria);

		return $this->json($friends);
	}

	private function validateParameters($name, $type, $friendshipValue, $tags): array
	{
		$errors = [];

		//Valid param name
		if ($name === null || $name === "") {
			$this->addException(new MissingParametersException(Friend::FIELD_NAME), $errors);
		} else if (!is_string($name)) {
			$this->addException(new WrongTypeForParameterException(Friend::FIELD_NAME, gettype($name), 'string'), $errors);
		}

		//Valid param type
		if ($type === null || $type === "") {
			$this->addException(new MissingParametersException(Friend::FIELD_TYPE), $errors);
		} else if (!is_string($type)) {
			$this->addException(new WrongTypeForParameterException(Friend::FIELD_TYPE, gettype($name), 'string'), $errors);
		} else if (!in_array($type, Friend::TYPES)) {
			$this->addException(new InvalidTypeOfFriendException($type), $errors);
		}

		//Valid param friendshipValue
		if ($friendshipValue === null) {
			$this->addException(new MissingParametersException(Friend::FIELD_FRIENDSHIP_VALUE), $errors);
		} else if (!is_numeric($friendshipValue)) {
			$this->addException(new WrongTypeForParameterException(Friend::FIELD_FRIENDSHIP_VALUE, gettype($friendshipValue), 'integer'), $errors);
		} else if ($friendshipValue < Friend::MIN_FRIENDSHIP_VALUE || $friendshipValue > Friend::MAX_FRIENDSHIP_VALUE) {
			$this->addException(new FriendshipOutOfBoundsException(), $errors);
		}

		//Valid param tags
		if ($tags !== null && !is_array($tags)) {
			$this->addException(new WrongTypeForParameterException(Friend::FIELD_TAGS, gettype($tags), 'array'), $errors);
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