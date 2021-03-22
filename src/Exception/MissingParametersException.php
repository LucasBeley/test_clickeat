<?php


namespace App\Exception;


use Exception;

class MissingParametersException extends Exception
{
	public function __construct(string $param)
	{
		parent::__construct("Missing parameter : " . $param);
	}
}