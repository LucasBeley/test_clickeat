<?php


namespace App\Exception;


use Exception;

class NoNameForFriendException extends Exception
{
	protected $message = "Poppy can't have a friend that has no name";
}