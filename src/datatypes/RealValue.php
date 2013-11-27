<?php
/** hijarian 27.11.13 10:30 */

namespace datatypes;


class RealValue
{
	/** @var float */
	private $value;

	public function getValue()
	{
		return $this->value;
	}

	public function __toString()
	{
		return (string)$this->getValue();
	}

	public function __construct($raw)
	{
		$this->value = $this->cleanFloatValue($raw);
	}

	private function cleanFloatValue($input)
	{
		return floatval(
			preg_replace(
				'/ /',  '',
				preg_replace(
					'/,/', '.',
					$input)));
	}
} 