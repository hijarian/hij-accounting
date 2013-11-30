<?php

/** hijarian 22.11.13 18:52 */

class SpendingController extends BaseController
{
	/**
	 * @param Base $f3
	 * @throws Exception
	 */
	public function addNew($f3)
	{
		$check = @$f3->get('POST')['Check'];
		if (!$check)
			throw new Exception('Предоставьте параметр Check!', 400);

		$check = $this->filterCheckData($check);

		$db = $this->makeDb($f3);
		$places = new PlaceStorage($db);
		$units = new MeasureUnitStorage($db);
		$factory = new SpendingFactory($places, $units);

		$expenses = $factory->makeFromForm($check);

		$storage = new ExpensesStorage($db);
		$storage->store($expenses);

		$f3->reroute('/spending');
	}



	/**
	 * @param Base $f3
	 */
	public function showUi($f3)
	{
		$f3->set('title', 'Учёт расходов');
		$f3->set('content', 'src/ui/spending.html');

		$db = $this->makeDb($f3);

		$units = new MeasureUnitStorage($db);
		$f3->set('units', $units->getList());

		$urlParams = $f3->get('GET');

		$expenses_storage = new ExpensesStorage($db);
		list($count, $expenses) = $expenses_storage->query($urlParams);

		$f3->set('expenses', $expenses);
		$f3->set('pageLength', min(count($expenses), ExpensesQuery::PAGE_LENGTH));
		$f3->set('itemsCount', $count);

		$pagination = $this->makePagination($urlParams, $count);

		$f3->set('pagination', $pagination);

		$this->installAssets($f3);

		$this->render($f3);
	}

	/**
	 * @param array $check Мы получаем данные о тратах из формы в таком виде:
	 *
	 * array (size=3)
	 *   'date' => string '2013.11.16' (length=10)
	 *   'place' => string 'ООО "СМАК"' (length=17)
	 *   'expenses' =>
	 * 	   0 =>
	         array (size=1)
	           'name' => string 'Хлеб челнинский' (length=29)
	       1 =>
	         array (size=1)
	           'amount' => string '1' (length=1)
	       2 =>
	         array (size=1)
	           'unit' => string '1' (length=1)
	       3 =>
	         array (size=1)
	           'price' => string '13,8' (length=4)
		   4 =>
	         array (size=1)
	           'discount' => string '1,8' (length=3)
	       5 =>
	         array (size=1)
	           'tags' => string 'еда, хлеб' (length=16)
     	   6 =>
			 array (size=1)
	           'name' => string 'Йогурт' (length=12)
		   7 =>
	         array (size=1)
	           'amount' => string '1' (length=1)
	       8 =>
	         array (size=1)
	           'unit' => string '1' (length=1)
	       9 =>
	         array (size=1)
	           'price' => string '61,60' (length=5)
	       10 =>
	         array (size=1)
	           'discount' => string '1,60' (length=4)
	       11 =>
	         array (size=1)
	           'tags' => string 'еда, молочные продукты' (length=41)
		   12 =>
	         array (size=1)
	           'name' => string '' (length=0)
		   13 =>
	         array (size=1)
	           'amount' => string '' (length=0)
	       14 =>
	         array (size=1)
	           'unit' => string '1' (length=1)
	       15 =>
	         array (size=1)
	           'price' => string '' (length=0)
		   16 =>
	         array (size=1)
	           'discount' => string '' (length=0)
	       17 =>
	         array (size=1)
	           'tags' => string '' (length=0)
	 *
	 * We need to process the 'expenses' value and collapse these rows to simple array of arrays
	 * @return array
	 */
	private function filterCheckData($check)
	{
		if (!$check)
			return [];

		$expenses = $check['expenses'];

		$item = [];
		$correct_expenses = [];
		foreach ($expenses as $raw)
		{
			$item[key($raw)] = current($raw);

			if ($this->itemFilled($item))
			{
				if ($this->itemIsNotEmpty($item))
				{
					$correct_expenses[] = $item;
				}
				$item = [];
			}
		}
		$check['expenses'] = $correct_expenses;
		return $check;
	}

	/**
	 * @param $item
	 * @return bool
	 */
	private function itemFilled($item)
	{
		return array_key_exists('name', $item)
		and array_key_exists('amount', $item)
		and array_key_exists('unit', $item)
		and array_key_exists('price', $item)
		and array_key_exists('discount', $item)
		and array_key_exists('tags', $item);
	}

	/**
	 * @param $item
	 * @return bool
	 */
	private function itemIsNotEmpty($item)
	{
		return $item['name'] !== ''
		or $item['amount'] !== ''
		or $item['price'] !== ''
		or $item['tags'] !== '';
	}

	/**
	 * @param $urlParams
	 * @param $count
	 * @return array
	 */
	private function makePagination($urlParams, $count)
	{
		$pagination = new Pagination($urlParams, ExpensesQuery::PAGE_LENGTH, $count);

		$page = @$urlParams['page'];
		if (!$page)
			$page = 1;

		$pagination->currentPage = $page;

		return $pagination->getScheme();
	}

	/** @param Base $f3 */
	private function installAssets($f3)
	{
		$f3->set(
		   'cssfiles',
			[
				"/assets/css/pikaday.css",
				"/assets/css/jquery-editable.css",
				"/assets/css/tip-yellowsimple.css"
			]
		);

		$f3->set(
			'jsbodyfiles',
			[
				"/assets/js/foundation/foundation.tab.js",
				"/assets/js/vendor/pikaday.js",
				"/assets/js/vendor/pikaday.jquery.js",
				"/assets/js/vendor/jquery.poshytip.min.js",
				"/assets/js/vendor/jquery-editable-poshytip.min.js",

				"/assets/js/spending.js",
			]
		);
	}

	/** @param Base $f3 */
	public function correctField($f3)
	{
		$data = $f3->get('POST');
		if (!$data)
			$f3->error(400, 'Это конечная точка для X-Editable, где ожидаемые данные?');

		extract($data);
		/**
		 * @var string $pk Record ID
		 * @var string $name Column name
		 * @var string $value New value
		 */

		$db = $this->makeDb($f3);
		$storage = new ExpensesStorage($db);

		$storage->correct($pk, $name, $value);

		echo json_encode(['success' => true]);
	}

}