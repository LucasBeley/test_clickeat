<?php


namespace App\Controller;


use App\Document\Friend;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController  extends AbstractController
{
	/**
	 * Documentation page
	 *
	 * @Route(name="default", path="")
	 */
	public function index()
	{
		return $this->json([
			"Welcome to Poppy's API ! Here is a list of what you can do :",
			[
				"/create_friend" => [
					"description" => "Create a friend for Poppy",
					"example" => "/create_friend?name=Example&type=HOOMAN&friendshipValue=54&tags[]=Tag 1&tags[]=Tag 2",
					"parameters" => [
						Friend::FIELD_NAME => [
							'required' => true,
							'type' => 'string'
						],
						Friend::FIELD_TYPE => [
							'required' => true,
							'type' => 'string',
							'constraints' => "Must choose between " . implode(", ", Friend::TYPES)
						],
						Friend::FIELD_FRIENDSHIP_VALUE => [
							'required' => true,
							'type' => 'integer',
							'constraints' => "Must be a value between ".Friend::MIN_FRIENDSHIP_VALUE." and ".Friend::MAX_FRIENDSHIP_VALUE,
						],
						Friend::FIELD_TAGS => [
							'required' => false,
							'type' => 'array of string'
						]
					]
				],

				"/list_friends" => [
					"description" => "List all Poppy's friends, parameters used for filtering",
					"example" => "/list_friends?name=Example",
					"parameters" => [
						Friend::FIELD_NAME => [
							'required' => false,
							'type' => 'string'
						],
						Friend::FIELD_TYPE => [
							'required' => false,
							'type' => 'string'
						],
						Friend::FIELD_FRIENDSHIP_VALUE => [
							'required' => false,
							'type' => 'integer'
						],
						Friend::FIELD_TAGS => [
							'required' => false,
							'type' => 'array of string'
						]
					]
				],

				"/call_the_monster" => [
					"description" => "Call the monster, it eats a friend of Poppy. Randomly if no parameters or selected id.".
										" Beware, GODS does not like to be eaten.",
					"example" => "/call_the_monster",
					"parameters" => [
						Friend::FIELD_ID => [
							'required' => false,
							'type' => 'string'
						],
					]
				],

				"/list_eaten" => [
					"description" => "List all eaten friends of Poppy.",
					"example" => "/list_eaten"
				],

				"/change_friendship_value" => [
					"description" => "Change the value of a friend of Poppy.",
					"example" => "/change_friendship_value?id=60579d48383600002f004b74&friendshipValue=20",
					"parameters" => [
						Friend::FIELD_ID => [
							'required' => true,
							'type' => 'string'
						],
						Friend::FIELD_FRIENDSHIP_VALUE => [
							'required' => true,
							'type' => 'integer',
							'constraints' => "Must be a value between ".Friend::MIN_FRIENDSHIP_VALUE." and ".Friend::MAX_FRIENDSHIP_VALUE,
						]
					]
				],

				"/launch_the_battle" => [
					"description" => "Launch the battle between Poppy and it's 'friends'.".
										" Each eaten friend give more life to Poppy (the friendship value).".
	 									" If Poppy's life strictly overcome the sum of friendship values of not eaten friend, it wins",
					"example" => "/launch_the_battle",
				],

				"/deus_ex_machina" => [
					"description" => "Gods come to save all eaten friends, they all become non eaten",
					"example" => "/deus_ex_machina",
				],
			]
		]);
	}
}