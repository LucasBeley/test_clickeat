<?php


namespace App\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class Friend
 *
 * @MongoDB\Document(collection="friends", db="test_clickeat")
 */
class Friend
{
	/**
	 * @MongoDB\Id()
	 * @var string
	 */
	private $id;

	/**
	 * @MongoDB\Field(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @MongoDB\Field(type="string")
	 * @var string
	 */
	private $type;

	/**
	 * @MongoDB\Field(type="int")
	 * @var integer
	 */
	private $friendshipValue;

	/**
	 * @MongoDB\Field(type="collection")
	 * @var string[]
	 */
	private $tags;

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId(string $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type): void
	{
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getFriendshipValue(): int
	{
		return $this->friendshipValue;
	}

	/**
	 * @param int $friendshipValue
	 */
	public function setFriendshipValue(int $friendshipValue): void
	{
		$this->friendshipValue = $friendshipValue;
	}

	/**
	 * @return string[]
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * @param string[] $tags
	 */
	public function setTags(array $tags): void
	{
		$this->tags = $tags;
	}
}