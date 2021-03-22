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
	 * Add a friend to Poppy, using url params 'name', 'type', 'friendshipValue' and 'tags'
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
			$request->get('friendshipValue'),
			$request->get('tags')
		);

		if (empty($errors)) {
			$friend = new Friend();
			$friend->setName($request->get('name'));
			$friend->setType($request->get('type'));
			$friend->setFriendshipValue($request->get('friendshipValue'));
			$friend->setTags($request->get('tags'));

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

		$name = $request->get('name');
		$type = $request->get('type');
		$friendshipValue = $request->get('friendshipValue');
		$tags = $request->get('tags');

		$criteria = [];
		if ($name) {
			$criteria['name'] = $name;
		}
		if ($type) {
			$criteria['type'] = $type;
		}
		if ($friendshipValue) {
			$criteria['friendshipValue'] = $friendshipValue;
		}
		if ($request->get('tags')) {
			$criteria['tags'] = is_array($tags) ? ['$all' => $tags] : $tags;
		}

		$friends = $friendRepository->findBy($criteria);

		return $this->json($friends);
	}

	private function validateParameters($name, $type, $friendshipValue, $tags): array
	{
		$errors = [];

		//Valid param name
		if ($name === null || $name === "") {
			$this->addException(new MissingParametersException('name'), $errors);
		} else if (!is_string($name)) {
			$this->addException(new WrongTypeForParameterException('name', gettype($name), 'string'), $errors);
		}

		//Valid param type
		if ($type === null || $type === "") {
			$this->addException(new MissingParametersException('type'), $errors);
		} else if (!is_string($type)) {
			$this->addException(new WrongTypeForParameterException('type', gettype($name), 'string'), $errors);
		} else if (!in_array($type, Friend::TYPES)) {
			$this->addException(new InvalidTypeOfFriendException($type), $errors);
		}

		//Valid param friendshipValue
		if ($friendshipValue === null) {
			$this->addException(new MissingParametersException('friendshipValue'), $errors);
		} else if (!is_numeric($friendshipValue)) {
			$this->addException(new WrongTypeForParameterException('friendshipValue', gettype($friendshipValue), 'integer'), $errors);
		} else if ($friendshipValue < 0 || $friendshipValue > 100) {
			$this->addException(new FriendshipOutOfBoundsException(), $errors);
		}

		//Valid param tags
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