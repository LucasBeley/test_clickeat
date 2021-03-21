<?php


namespace App\Exception;


use Exception;

class FriendshipTooLowException extends Exception
{
	protected $message = "Value too low for friendshipvalue (< 0)";
}