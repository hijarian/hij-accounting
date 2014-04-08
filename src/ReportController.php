<?php
use datatypes\Price;
use datatypes\Tags;

/** hijarian 27.11.13 13:21 */

class ReportController extends BaseController
{
	/**
	 * Show histogram with expenses by type and date.
	 *
	 * @param Base $f3
	 */
	public function histogram($f3)
	{
		$f3->set('title', 'Отчёт о тратах');
		$f3->set('content', 'src/ui/histogram.html');

		list($categories, $series) = $this->getHistogramData($f3);

		$f3->set('categories', json_encode($categories));
		$f3->set('series', json_encode($series));
		$this->installAssets($f3);
		$this->render($f3);
	}

	/** @param Base $f3 */
	private function installAssets($f3)
	{
		$f3->set(
		   'jsbodyfiles',
		   [
			   "/assets/js/vendor/highcharts.js",
			   "/assets/js/vendor/highcharts.themes.grid.js",
			   "/assets/js/histogram.js",
		   ]
		);
	}

	/**
	 * @param Base $f3
	 * @return array(array, array) First element is `categories` second one is `series` from the Highcharts settings.
	 */
	private function getHistogramData($f3)
	{
		$db = $this->makeDb($f3);

		$urlParams = $f3->get('GET');

		$sql = "select strftime('%Y-%m', date) as month, sum(price * amount) as spending from spending where date between :after and :before and tags like '%' || :tag || '%' group by month order by month DESC";

		$after = @$urlParams['after'];
		if (!$after)
			$after = '2010-01';
		$before = @$urlParams['before'];
		if (!$before)
			$before = date('Y-m-d', time() + 60*60*24);

		$tags = @$urlParams['tags'];
		if (!$tags)
			$tags = ['еда', 'оборудование', 'хозтовары', 'квартплата', 'кредиты'];
		else
			$tags = array_map('trim', explode(',', $tags));


		$fetcher = $db->prepare($sql);
		if (!$fetcher) {
			var_dump($db->errorInfo()); die();
		}

		$temp = [];
		$categories = [];
		$series = []; // each element is tuple (name, data)
		foreach ($tags as $tag)
		{
			$params = [':tag' => $tag, ':after' => $after, ':before' => $before];

			$executed = $fetcher->execute($params);
			if (!$executed)
			{
				var_dump($fetcher->errorInfo()); die();
			}

			while($resultForTag = $fetcher->fetch(PDO::FETCH_ASSOC))
			{
				$month = $resultForTag['month'];
				$spending = new Price(0);
				$spending->raw = $resultForTag['spending'];
				$temp[$month][$tag] = $spending->toFloat();
			}

			$series[$tag] = [
				'name' => $tag,
				'data' => []
			];
		}

		foreach ($temp as $month => $data)
		{
			$categories[] = $month;

			foreach ($tags as $tag)
			{
				$series[$tag]['data'][] = array_key_exists($tag, $data)
					? $data[$tag]
					: (new Price(0))->toFloat();
			}
		}

		$series = array_values($series);

		return [$categories, $series];
	}
}