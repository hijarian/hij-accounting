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
		   ]
		);
	}

	private function getHistogramData()
	{
		return [
			[1, 2, 3],
			[
				[1, 2, 3],
				[4, 5, 6]
			]
		];
	}
}