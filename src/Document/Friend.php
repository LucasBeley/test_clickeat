<?php


namespace App\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class Friend
 *
 * @MongoDB\Document(collection="friends")
 */
class Friend
{
	const FIELD_ID = "id";
	const FIELD_NAME = "name";
	const FIELD_TYPE = "type";
	const FIELD_FRIENDSHIP_VALUE = "friendshipValue";
	const FIELD_TAGS = "tags";
	const FIELD_EATEN = "eaten";

	const TYPES = ['HOOMAN', 'NOOB', 'UNICORN', 'GOD'];

	/**
	 * @MongoDB\Id()
	 * @var null|string
	 */
	private $id;

	/**
	 * @MongoDB\Field(type="string")
	 * @var null|string
	 */
	private $name;

	/**
	 * @MongoDB\Field(type="string")
	 * @var null|string
	 */
	private $type;

	/**
	 * @MongoDB\Field(type="int")
	 * @var null|integer
	 */
	private $friendshipValue;

	/**
	 * @MongoDB\Field(type="collection")
	 * @var null|string[]
	 */
	private $tags;

	/**
	 * @MongoDB\Field(type="boolean")
	 * @var null|bool
	 */
	private $eaten;

	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->id;
	}

	/**
	 * @param string|null $id
	 */
	public function setId(?string $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @param string|null $type
	 */
	public function setType(?string $type): void
	{
		$this->type = $type;
	}

	/**
	 * @return int|null
	 */
	public function getFriendshipValue(): ?int
	{
		return $this->friendshipValue;
	}

	/**
	 * @param int|null $friendshipValue
	 */
	public function setFriendshipValue(?int $friendshipValue): void
	{
		$this->friendshipValue = $friendshipValue;
	}

	/**
	 * @return string[]|null
	 */
	public function getTags(): ?array
	{
		return $this->tags;
	}

	/**
	 * @param string[]|null $tags
	 */
	public function setTags(?array $tags): void
	{
		$this->tags = $tags;
	}

	/**
	 * @return bool|null
	 */
	public function getEaten(): ?bool
	{
		return $this->eaten;
	}

	/**
	 * @param bool|null $eaten
	 */
	public function setEaten(?bool $eaten): void
	{
		$this->eaten = $eaten;
	}
}