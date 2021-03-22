<?php


namespace App\Exception;


use Exception;

class WrongTypeForParameterException extends Exception
{
	public function __construct(string $param, string $actual, string $expected)
	{
		parent::__construct("Wrong type for parameter " . $param . ". (Expected " . $expected . ", got " . $actual . ")");
	}
}