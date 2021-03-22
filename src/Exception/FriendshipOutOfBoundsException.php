<?php


namespace App\Exception;


use Exception;

class FriendshipOutOfBoundsException extends Exception
{
	protected $message = "Value out of bounds for friendshipvalue. Should be between 0 and 100.";
}