<?php
/** hijarian 23.11.13 0:00 */

namespace datatypes;

use DateTime;

class Expense
{
	/** @var string ID of the expense in database */
	public $rowid;

	/** @var DateTime */
	public $date;

	/** @var Place */
	public $place;

	/** @var string */
	public $name;

	/** @var double */
	public $amount;

	/** @var MeasureUnit */
	public $unit;

	/** @var Price */
	public $price;

	/** @var Price */
	public $discount;

	/** @var Tags */
	public $tags;

	public function getCost()
	{
		$value = $this->amount * ($this->price->toFloat() - $this->discount->toFloat());
		return new Price($value);
	}

	public function __get($attr)
	{
		$method = sprintf('get%s', ucfirst($attr));
		if (method_exists($this, $method))
			return $this->$method();

		if (property_exists($this, $attr))
			return $this->$attr;

		throw new \InvalidArgumentException;
	}
} 