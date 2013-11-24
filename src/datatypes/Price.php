<?php
/** hijarian 23.11.13 12:00 */

namespace datatypes;

class Price
{
	/** @var int Целочисленное значение в копейках */
	public $raw;

	/**
	 * На сколько частей делится одна единица валюты.
	 * Для рубля это 100
	 */
	private $multiplier = 100;

	/**
	 * @param string Произвольное действительное значение цены
	 * @param int Множитель, на который надо умножить дробную запись цены, чтобы получить целочисленное значение.
	 * Для рубля это эквивалентно количеству копеек в рубле.
	 */
	public function __construct($value, $multiplier = 100)
	{
		$this->multiplier = $multiplier;
		$this->raw = intval(floatval($value) * $this->multiplier);
	}

	/**
	 * @param int $rawPrice Raw value of Price as it's stored inside database.
	 * @return Price
	 */
	public static function fromRaw($rawPrice)
	{
		$price = new Price(0);
		$price->raw = $rawPrice;
		return $price;
	}

	public function __toString()
	{
		return money_format('%i руб.', $this->toFloat());
	}

	public function toFloat()
	{
		return $this->raw / $this->multiplier;
	}

	/**
	 * @param double $amount
	 * @return Price
	 */
	public function getCostFor($amount)
	{
		// dividing by multiplier because constructor deals with human-readable representation of price
		return new Price($amount * $this->toFloat());
	}
} 