<?php


namespace App\Exception;


use App\Document\Friend;
use Exception;

class FriendshipOutOfBoundsException extends Exception
{
	protected $message = "Value out of bounds for friendshipvalue. Should be between ".Friend::MIN_FRIENDSHIP_VALUE." and ".Friend::MAX_FRIENDSHIP_VALUE;
}