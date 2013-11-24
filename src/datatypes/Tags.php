<?php
/** hijarian 23.11.13 13:56 */

namespace datatypes;

class Tags
{
	const SEPARATOR = ',';

	/** @var string[] */
	private $value;

	/** @var string */
	private $raw;

	public function __construct($raw)
	{
		$this->raw = $raw;
	}

	public function makeValue($raw)
	{
		$this->value = array_map('trim', explode(self::SEPARATOR, $raw));
		return $this->value;
	}

	public function getValue()
	{
		if (!$this->value)
			$this->makeValue($this->raw);

		return $this->value;
	}

	public function __toString()
	{
		return implode(self::SEPARATOR, $this->getValue());
	}
} 