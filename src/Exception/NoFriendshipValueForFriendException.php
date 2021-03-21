<?php


namespace App\Exception;


use Exception;

class NoFriendshipValueForFriendException extends Exception
{
	protected $message = "Poppy can't have a friend that has no friendship. I mean, think about it, if it has no friendship value, it can't be a friend, seems logical no ?";
}