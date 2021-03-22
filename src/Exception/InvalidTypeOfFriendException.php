<?php


namespace App\Exception;


use Exception;

class InvalidTypeOfFriendException extends Exception
{
	public function __construct(string $param)
	{
		parent::__construct("Friend's type should be in ['HOOMAN', 'NOOB', 'UNICORN', 'GOD'] (got : " . $param . ")");
	}
}