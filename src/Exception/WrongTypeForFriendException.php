<?php


namespace App\Exception;


use Exception;

class WrongTypeForFriendException extends Exception
{
	protected $message = "Friend's type should be in ['HOOMAN', 'NOOB', 'UNICORN', 'GOD']";
}