<?php
use datatypes\Price;
use datatypes\Tags;

/** hijarian 24.11.13 9:42 */

class ExpensesQuery
{
	private $select = 'select spending.id as id, date, places.id as place_id, places.name as place_name, spending.name as name, amount, units.id as unit_id, units.name as unit_name, price, discount, tags';

	private $count = 'select count(spending.id) as results_number';

	private $from = 'from spending left join places on places.id = place join units on units.id = unit';

	const PAGE_LENGTH = 100;

	/** @var string Only expenses occurred after this date */
	private $afterDate;

	public function setAfterDate($input)
	{
		$this->afterDate = $this->toSqliteDateLiteral($input);
	}

	/** @var string Only expenses occurred after this date */
	private $beforeDate;

	public function setBeforeDate($input)
	{
		$this->beforeDate = $this->toSqliteDateLiteral($input);
	}

	/** @var string Only expenses having this as part of name */
	public $name;

	/** @var string Only expenses having this as part of name of place of purchase */
	public $placeName;

	/** @var string Only expenses from this place of purchase */
	public $placeId;

	/** @var string Only expenses larger than this value */
	private $minPrice;

	public function setMinPrice($input)
	{
		$this->minPrice = $this->toPriceLiteral($input);
	}

	/** @var string Only expenses smaller than this value */
	public $maxPrice;

	/** @var string Only show expenses on this block of $this->pageLength records. */
	public $pageNumber = 1;

	/** @var string Only show expenses which have all of given tags */
	public $tags;

	/**
	 * @var bool Whether to search expenses which contain ALL tags listed in $this->tags or just one of them.
	 * This flag effectively defines how to combine statements: by AND or by OR operators.
	 */
	public $needAllTags = true;

	public function __set($attr, $value)
	{
		$methodName = sprintf("set%s", ucfirst($attr));
		if (method_exists($this, $methodName)) {
			$this->$methodName($value);
		} else {
			$this->$attr = $value;
		}
	}

	/** @var array Names of the columns by which the resulting list should be sorted. */
	private $sort;

	public function setSort($input)
	{
		$this->sort = array_map('trim', explode(',', $input));
	}

	/**
	 * @return array(string, array) First element is the SQL query text with params, second element is array of params
	 */
	public function getSQL()
	{
		list($conditions, $params) = $this->makeConditions();

		$where = count($conditions)
			? sprintf(' where %s', implode(' AND ', $conditions))
			: '';

		// Full SQL to get the data
		$sql = sprintf("%s %s%s", $this->select, $this->from, $where);

		// Separate query just to count total number of results. If we stuff this part in main query it'll become aggregate one, collapsing to single row.
		$counter = sprintf("%s %s%s", $this->count, $this->from, $where);

		$sort = $this->makeSort();
		if ($sort)
			$sql .= sprintf(' order by %s', implode(',', $sort));

		// NOTE: offset and limits must be AT END of query, after "Order" statements.
		list($offset, $limit) = $this->makeOffsetAndLimit();
		$sql .= sprintf(' limit %d offset %d', $limit, $offset);

		return [$sql, $counter, $params];
	}

	private function toSqliteDateLiteral($input)
	{
		return date('Y-m-d H:i:s', strtotime($input));
	}

	/**
	 * @param float $input
	 * @return int
	 */
	private function toPriceLiteral($input)
	{
		$price = new Price($input);
		return $price->raw;
	}

	/**
	 * @return array
	 */
	private function makeDateCondition()
	{
		$dateCondition = '';
		$dateParams = [];
		if ($this->afterDate and $this->beforeDate)
		{
			$dateCondition = 'date between :after and :before';
			$dateParams[':after'] = $this->afterDate;
			$dateParams[':before'] = $this->beforeDate;
		}
		else if ($this->afterDate)
		{
			$dateCondition = 'date > :after';
			$dateParams[':after'] = $this->afterDate;
		}
		else if ($this->beforeDate)
		{
			$dateCondition = 'date < :before';
			$dateParams[':before'] = $this->beforeDate;
		}
		return array( $dateCondition, $dateParams );
	}

	/**
	 * @return array
	 */
	private function makePriceCondition()
	{
		$priceCondition = '';
		$priceParams = [];
		if ($this->minPrice and $this->maxPrice)
		{
			$priceCondition = 'price between :minPrice and :maxPrice';
			$priceParams[':minPrice'] = $this->minPrice;
			$priceParams[':maxPrice'] = $this->maxPrice;
		}
		else if ($this->minPrice)
		{
			$priceCondition = 'price > :minPrice';
			$priceParams[':minPrice'] = $this->minPrice;
		}
		else if ($this->maxPrice)
		{
			$priceCondition = 'price < :maxPrice';
			$priceParams[':maxPrice'] = $this->maxPrice;

		}
		return array( $priceCondition, $priceParams );
	}

	/**
	 * @return array
	 */
	private function makeTagsCondition()
	{
		$tagsCondition = '';
		$tagsParams = [];
		if ($this->tags)
		{ // horror, horror
			$tags = new Tags($this->tags);
			$tags = $tags->getValue(); // do not need Tags obj anymore
			$idx = 0;
			$tag_conditions = [];
			foreach ($tags as $tag)
			{
				++$idx;
				$placeholder_id = ":tag_{$idx}"; // autogenerate
				$tag_conditions[] = "tags like '%' || {$placeholder_id} || '%'";
				$tagsParams[$placeholder_id] = $tag;
			}
			$operator = $this->needAllTags ? ' AND ' : ' OR ';
			$tagsCondition = sprintf('(%s)', implode($operator, $tag_conditions));
		}
		return array( $tagsCondition, $tagsParams );
	}

	/**
	 * @return array
	 */
	private function makeNameCondition()
	{
		$nameCondition = '';
		$nameParams = [];
		if ($this->name)
		{
			$nameCondition = 'spending.name like "%" || :name || "%"';
			$nameParams[':name'] = $this->name;
		}
		return array( $nameCondition, $nameParams );
	}

	/**
	 * @return array
	 */
	private function makePlaceCondition()
	{
		$placeCondition = '';
		$placeParams = [];
		if ($this->placeId)
		{
			$placeCondition = 'places.id = :placeId';
			$placeParams[':placeId'] = $this->placeId;
		}
		else if ($this->placeName)
		{
			$placeCondition = 'places.name like "%" || :placeName || "%"';
			$placeParams[':placeName'] = $this->placeName;
		}

		return array( $placeCondition, $placeParams );
	}

	/** @return array(string, array) First element is the "WHERE" part of SQL query, second element is the list of named parameters in it. */
	private function makeConditions()
	{
		$conditions = [];
		$params = [];

		list($dateCondition, $dateParams) = $this->makeDateCondition();
		if ($dateCondition)
		{
			$conditions[] = $dateCondition;
			$params = array_merge($params, $dateParams);
		}

		list($priceCondition, $priceParams) = $this->makePriceCondition();
		if ($priceCondition)
		{
			$conditions[] = $priceCondition;
			$params = array_merge($params, $priceParams);
		}

		list($nameCondition, $nameParams) = $this->makeNameCondition();
		if ($nameCondition)
		{
			$conditions[] = $nameCondition;
			$params = array_merge($nameParams);
		}

		list($placeCondition, $placeParams) = $this->makePlaceCondition();
		if ($placeCondition)
		{
			$conditions[] = $placeCondition;
			$params = array_merge($params, $placeParams);
		}

		list($tagsCondition, $tagsParams) = $this->makeTagsCondition();
		if ($tagsCondition)
		{
			$conditions[] = $tagsCondition;
			$params = array_merge($params, $tagsParams);
		}

		return array(
			$conditions,
			$params
		);
	}

	private function makeOffsetAndLimit()
	{
		$limit = self::PAGE_LENGTH;
		$offset = $this->pageNumber * self::PAGE_LENGTH - $limit;
		return [$offset, $limit];
	}

	private function makeSort()
	{
		return ($this->sort)
			? $this->sort
			: ['date DESC'];
	}
}