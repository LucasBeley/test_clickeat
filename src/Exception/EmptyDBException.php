<?php


namespace App\Exception;


use Exception;

class EmptyDBException extends Exception
{
	protected $message = "Poppy doesn't have any friend :'(. At least no one has been eaten :D.";
}