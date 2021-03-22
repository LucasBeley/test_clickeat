<?php


namespace App\Tests\TestCase;


use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\MongoDB\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTestCase extends WebTestCase
{
	use FixturesTrait;

	const DEFAULT_DOC_MANAGER_SERVICE = 'doctrine_mongodb.odm.default_document_manager';

	protected static ?KernelBrowser $client = null;

	/**
	 * @var DocumentManager
	 */
	protected $documentManager;

	protected function setUp(): void
	{
		self::$client = static::createClient();

		$this->documentManager = self::$client->getContainer()
			->get(self::DEFAULT_DOC_MANAGER_SERVICE);
		$this->mongoDbPurge($this->documentManager);
	}
}