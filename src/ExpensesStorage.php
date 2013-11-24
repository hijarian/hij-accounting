<?php
use datatypes\EmptyValue;
use datatypes\Expense;
use datatypes\MeasureUnit;
use datatypes\Place;
use datatypes\Price;
use datatypes\Tags;

/** hijarian 23.11.13 0:14 */

class ExpensesStorage
{
	/** @var PDO */
	private $db;

	/** @param PDO $db */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @param Expense[] $expenses
	 * @throws StorageOperationException
	 */
	public function store($expenses)
	{
		$began = $this->db->beginTransaction();
		if (!$began)
			throw new StorageOperationException('Не удалось начать транзакцию записи трат в БД!', $this->db->errorInfo());

		foreach ($expenses as $expense)
			$this->storeExpense($expense);

		$this->db->commit();
	}

	/**
	 * @param Expense $expense
	 * @throws StorageOperationException
	 */
	private function storeExpense($expense)
	{
		$values = [
			':date' => $expense->date->format('Y-m-d'),  // no need in precision by time
			':place' => $expense->place->id, 
			':name' => $expense->name, 
			':amount' => $expense->amount, 
			':unit' => $expense->unit->id, 
			':price' => $expense->price->raw,
			':discount' => $expense->discount->raw,
			':tags' => (string)$expense->tags, 
		];
		
		$inserter = $this->db->prepare('insert into spending (date, place, name, amount, unit, price, discount, tags) values (:date, :place, :name, :amount, :unit, :price, :discount, :tags)');
		$inserted = $inserter->execute($values);
		if ($inserted === false) {
			var_dump($inserter->errorInfo());
			throw new StorageOperationException('Не удалось записать трату в БД!', $inserter->errorInfo());
		}
	}

	public function getList($queryData)
	{
		$query = $this->makeQuery($queryData);
		list($sql, $countSql, $params) = $query->getSQL();

//		var_dump($sql);
//		var_dump($countSql);
//		var_dump($params);

		$finder = $this->db->prepare($sql);
		if (!$finder)
			throw new StorageOperationException("Не удалось подготовить запрос на выборку", $this->db->errorInfo());

		$found = $finder->execute($params);
		if ($found === false)
			throw new StorageOperationException('Не удалось выполнить команду выборки трат из БД!', $finder->errorInfo());

		$result = $finder->fetchAll(PDO::FETCH_ASSOC);

		$counter = $this->db->prepare($countSql);
		if (!$counter)
			throw new StorageOperationException('Не удалось подготовить запрос на подсчёт', $this->db->errorInfo());

		$counted = $counter->execute($params);
		if ($counted === false)
			throw new StorageOperationException('Не удалось выполнить команду подсчёта трат из БД!', $counter->errorInfo());

		$count = $counter->fetchAll(PDO::FETCH_ASSOC)[0]['results_number'];

		return [$count, $result];
	}

	/**
	 * @param array
	 * @return Expense[]
	 */
	public function query($query)
	{
		list($count, $data) = $this->getList($query);

		$expenses = array_map([$this, 'makeExpense'], $data);

		return [$count, $expenses];
	}

	/**
	 * @param $data
	 * @return Expense
	 */
	private function makeExpense($data)
	{
		$expense = new Expense;
		$expense->date = empty($data['date']) ? new EmptyValue : new DateTime($data['date']);
		$expense->name = $data['name'];

		$expense->place = new Place($data['place_id'],$data['place_name']);
		$expense->unit = new MeasureUnit($data['unit_id'],$data['unit_name']);

		$expense->amount = $data['amount'];
		$expense->price = Price::fromRaw($data['price']);
		$expense->discount = Price::fromRaw($data['discount']);
		$expense->tags = new Tags($data['tags']);
		return $expense;
	}

	/**
	 * @param array $queryData
	 * @return ExpensesQuery
	 */
	private function makeQuery($queryData)
	{
		$query = new ExpensesQuery;
		$this->fillParams(
		     $query,
		     $queryData,
		     [
			     'name',
			     'after' => 'afterDate',
			     'before' => 'beforeDate',
			     'tags',
			     'where' => 'place_name',
			     'place_id',
			     'minPrice',
			     'maxPrice',
			     'sort',
			     'page' => 'pageNumber'
		     ]
		);

		return $query;
	}

	/**
	 * @param ExpensesQuery $query
	 * @param array $queryData
	 * @param array $params
	 */
	private function fillParams(&$query, $queryData, $params)
	{
		foreach ($params as $datakey => $queryproperty)
		{
			if (is_numeric($datakey))
				$datakey = $queryproperty;

			if (empty($queryData[$datakey]))
				continue;

			if (!property_exists($query, $queryproperty))
				continue;

			$query->$queryproperty = $queryData[$datakey];
		}
	}

} 