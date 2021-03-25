<?php


namespace App\Exception;


use Exception;

class FriendNotFoundException extends Exception
{
	protected $message = "These aren't the droids you're looking for. The object you are searching for does not exist.";
}