<?php
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

		list($categories, $series) = $this->getHistogramData();

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

	private function getHistogramData()
	{
		return [
			['2013-10', '2013-11'],
			[
				[
					'name' => 'Еда',
					'data' => [4500, 3750]
				],
				[
					'name' => 'Оборудование',
					'data' => [2300, 14000]
				]
			]
		];
	}
}