<?php


namespace App\Exception;


use Exception;

class NoTypeForFriendException extends Exception
{
	protected $message = "Poppy can't have a friend that has no type";
}