<?php
/** hijarian 23.11.13 11:31 */

use datatypes\EmptyValue;
use datatypes\MeasureUnit;
use datatypes\Place;
use datatypes\Price;
use datatypes\Expense;
use datatypes\Tags;

class SpendingFactory
{
	const DEFAULT_MEASURE_UNIT_NAME = 'шт';

	/** @var PlaceStorage */
	private $place_storage;

	/** @var MeasureUnitStorage */
	private $unit_storage;

	public function __construct($place_storage, $unit_storage)
	{
		$this->place_storage = $place_storage;
		$this->unit_storage = $unit_storage;
	}

	/**
	 * @param array $input Expected structure:
	 * [
	 *  'date' => 'dd.mm.yyyy',
	 *  'place' => 'textual representation of the place of purchase',
	 *  'spendings' => [
	 *    [
	 *      'name' => 'What was bought',
	 *      'amount' => 'How much',
	 *      'unit' => 'What units of measurement "amount" corresponds to, integer ID from DB',
	 *      'price' => 'Cost of one unit in hundredths of ruble (thus, integer)',
	 *      'tags' => 'Comma-separated list of text tags to categorize this purchase'
	 *    ],
	 *    ...
	 *  ]
	 *
	 * @return Expense[]
	 * @throws SpendingMakingException On invalid datetime
	 */
	public function makeFromForm($input)
	{
		$expenses = @$input['expenses'];
		if (!$expenses or !is_array($expenses) or !count($expenses))
			return [];

		$date = $this->makeDate(@$input['date']);
		$place = $this->makePlace(@$input['place']);

		$result = [];
		foreach ($expenses as $expense_data)
			if (!$this->isEmpty($expense_data))
				$result[] = $this->makeExpense($expense_data, $date, $place);

		return $result;
	}

	/**
	 * @param string $input DateTime-parsable representation of the date of purchase
	 * @return DateTime|EmptyValue
	 * @throws Exception When invalid datetime is entered (empty is not invalid)
	 */
	private function makeDate($input)
	{
		return ($input)
			? new DateTime(strtotime($input))
			: new EmptyValue;
	}

	/**
	 * @param string $input Name of a place of purchase
	 * @return Place|EmptyValue
	 * @throws StorageOperationException
	 */
	private function makePlace($input)
	{
		return ($input)
			? $this->place_storage->getPlaceByName($input)
			: new EmptyValue;
	}

	/**
	 * @param string $input
	 * @return Price
	 * @throws SpendingMakingException
	 */
	private function makePrice($input)
	{
		if (!$input)
			throw new SpendingMakingException('Запись о трате должна содержать хотя бы сумму потраченного!');

		return new Price($input);
	}

	/**
	 * @param string $input
	 * @return MeasureUnit
	 * @throws StorageOperationException
	 * @throws SpendingMakingException
	 */
	private function makeMeasureUnit($input)
	{
		if (!$input)
			$input = self::DEFAULT_MEASURE_UNIT_NAME;

		$unit = $this->unit_storage->getById($input);
		if (!$unit)
			throw new SpendingMakingException("Несуществующие единицы измерения не позволяются: {$input}");

		return $unit;
	}

	private function makeAmount($input)
	{
		return ($input)
			? floatval($input)
			: 1.00;
	}

	private function makeTags($input)
	{
		return new Tags($input);
	}

	/**
	 * @param $data
	 * @param $date
	 * @param $place
	 * @return Expense
	 */
	private function makeExpense($data, $date, $place)
	{
		$expense = new Expense;
		$expense->price = $this->makePrice(@$data['price']);
		$expense->discount = $this->makeDiscount(@$data['discount']);
		$expense->name = @$data['name'];
		$expense->amount = $this->makeAmount(@$data['amount']);
		$expense->unit = $this->makeMeasureUnit(@$data['unit']);
		$expense->tags = $this->makeTags(@$data['tags']);
		$expense->date = $date;
		$expense->place = $place;
		return $expense;
	}

	private function isEmpty($expense_data)
	{
		return !(
			@$expense_data['name'] or
			@$expense_data['amount'] or
			@$expense_data['price'] or
			@$expense_data['tags']
		);
	}

	private function makeDiscount($param)
	{
		return new Price($param);
	}
}

