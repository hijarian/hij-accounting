<?php
/** hijarian 27.11.13 13:23 */

class BaseController
{
	/** @param Base $f3 */
	protected function render($f3)
	{
		$this->installGlobalAssets($f3);

		$this->initMenu($f3);
		echo Template::instance()->render('src/ui/layout.html');
	}

	/** @param Base $f3 */
	private function installGlobalAssets($f3)
	{
		$css = [
			"/assets/css/normalize.css",
		    "/assets/css/foundation.min.css",
			"/assets/css/main.css",
		];
		$this->prependArrayParam($f3, 'cssfiles', $css);

		$head_js = [
			"/assets/js/vendor/custom.modernizr.js",
		];
		$this->prependArrayParam($f3, 'jsheadfiles', $head_js);

		$body_js = [
			"/assets/js/vendor/jquery.js",
			"/assets/js/vendor/underscore.min.js",
			"/assets/js/foundation/foundation.js",

			"/assets/js/vendor/moment-with-langs.min.js",
			"/assets/js/vendor/accounting.min.js",

			"/assets/js/main.js",
		];
		$this->prependArrayParam($f3, 'jsbodyfiles', $body_js);
	}

	/**
	 * @param Base $f3
	 * @param string $paramname
	 * @param array $value
	 */
	private function prependArrayParam($f3, $paramname, $value)
	{
		$original_value = $f3->get($paramname);
		if (!$original_value)
			$original_value = [];
		$f3->set($paramname, array_merge($value, $original_value));
	}

		/**
	 * @param Base $f3
	 * @return PDO
	 */
	protected function makeDb($f3)
	{
		$path = $f3->get('db_path');
		$dsn = $this->makeDSN($path);
		return new PDO($dsn);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function makeDSN($path)
	{
		return sprintf('sqlite:%s', $path);
	}

	/** @param Base $f3 */
	private function initMenu($f3)
	{
		$f3->set('menu', [
			'spending' => ['label' => 'Расходы'],
			'report' => ['label' => 'Отчёты', 'items' => ['report' => ['label' => 'Гистограмма расходов']]]
		]);
	}

}