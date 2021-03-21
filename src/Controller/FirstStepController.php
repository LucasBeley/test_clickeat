<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\FriendshipTooHighException;
use App\Exception\FriendshipTooLowException;
use App\Exception\NoFriendshipValueForFriendException;
use App\Exception\NoNameForFriendException;
use App\Exception\NoTypeForFriendException;
use App\Exception\WrongTypeForFriendException;
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
	 * @Route(name="createFriend", path="/create_friend")
	 * @param Request $request
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws MongoDBException
	 */
	public function createFriend(Request $request, DocumentManager $dm): Response
	{
		$errors = [];
		if ($request->get('name') === null) {
			$this->addException(new NoNameForFriendException(), $errors);
		}
		if ($request->get('type') === null) {
			$this->addException(new NoTypeForFriendException(), $errors);
		} else if (!in_array($request->get('type'), Friend::TYPES)) {
			$this->addException(new WrongTypeForFriendException(), $errors);
		}
		if ($request->get('friendshipvalue') === null) {
			$this->addException(new NoFriendshipValueForFriendException(), $errors);
		} else if ($request->get('friendshipvalue') < 0) {
			$this->addException(new FriendshipTooLowException(), $errors);
		} else if ($request->get('friendshipvalue') > 100) {
			$this->addException(new FriendshipTooHighException(), $errors);
		}

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

	private function addException(Exception $exception, &$errors) {
		$errors['errors'][] = [
			"exception" => get_class($exception),
			"message" => $exception->getMessage()
		];
	}
}