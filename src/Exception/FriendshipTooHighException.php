<?php


namespace App\Exception;


use Exception;

class FriendshipTooHighException extends Exception
{
	protected $message = "Value too high for friendshipvalue (> 100). This isn't friendship, this is love <3";
}