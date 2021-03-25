<?php


namespace App\Controller;


use App\Document\Friend;
use App\Exception\EmptyDBException;
use App\Repository\FriendRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThirdStepController extends AbstractController
{

	/**
	 * Launch the battle between Poppy and it's "friends"
	 *
	 * Each eaten friend give more life to Poppy (the friendship value).
	 * If Poppy's life strictly overcome the sum of friendship values of not eaten friend, it wins
	 *
	 * @Route(name="launchTheBattle", path="/launch_the_battle")
	 * @param DocumentManager $dm
	 * @return Response
	 * @throws Exception
	 */
	public function launchTheBattle(DocumentManager $dm): Response
	{
		/** @var FriendRepository $friendRepository */
		$friendRepository = $dm->getRepository(Friend::class);

		//Return error if no Friend in DB
		if (count($friendRepository->findAll()) === 0) {
			$exception = new EmptyDBException();
			$json['errors'][] = [
				"exception" => get_class($exception),
				"message" => $exception->getMessage()
			];
			return $this->json($json);
		}

		$json['battleLaunched'] = "A fierce battle rages between Poppy and it's 'friends'. They have understood that Poppy is the terrible monster that ate most of them. The want the head of the monster !!";

		//Handle scores of the two sides of the battle
		$poppyLife = $friendRepository->getSideLife([true]);
		$friendsLife = $friendRepository->getSideLife([false, null]);

		$json['scores'] = [
			"poppy" => $poppyLife,
			"friends" => $friendsLife
		];

		//Handle the end of the battle
		$allNoobsAndHoomans = $friendRepository->findBy([Friend::FIELD_TYPE => ['$in' => ['NOOB', 'HOOMAN']]]);
		/** @var Friend $friend */
		foreach ($allNoobsAndHoomans as $friend) {
			$friend->setEaten($poppyLife > $friendsLife);
		}
		$dm->flush();

		if ($poppyLife > $friendsLife) {
			$json['poppyWon'] = "Poppy won the battle, so it eats everyone on the battlefield !!";
		} else {
			$json['poppyLoose'] = "Poppy loose the battle, all eaten friends are freed from its stomach !!";
		}

		return $this->json($json);
	}

	/**
	 * Gods come to save all eaten friends
	 *
	 * @Route(name="deusExMachina", path="deus_ex_machina")
	 * @param DocumentManager $dm
	 * @return JsonResponse
	 * @throws MongoDBException
	 */
	public function deusExMachina(DocumentManager $dm) : JsonResponse
	{
		/** @var FriendRepository $friendRepository */
		$friendRepository = $dm->getRepository(Friend::class);

		//Make every human and noob non eaten
		$allNoobsAndHoomans = $friendRepository->findBy([Friend::FIELD_TYPE => ['$in' => ['NOOB', 'HOOMAN']]]);
		/** @var Friend $friend */
		foreach ($allNoobsAndHoomans as $friend) {
			$friend->setEaten(false);
		}
		$dm->flush();

		$json = [
			"deusExMachina" => "The god of gods (you) has shown mercy and saved all eaten friends !!"
		];

		return $this->json($json);
	}
}