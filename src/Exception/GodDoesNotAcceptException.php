<?php


namespace App\Exception;


use Exception;

class GodDoesNotAcceptException extends Exception
{
	protected $message = "A GOD doesn't accept that.";
}