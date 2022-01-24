<?php

namespace Pulse\View;

class View
{
	protected $layout;
	protected $sections = [];
	protected $currentSection;

	function __construct()
	{
	}

	public function Render($name, $data)
	{

		$path = APP_PATH . DIRECTORY_SEPARATOR . "Views" . DIRECTORY_SEPARATOR;

		$name = str_replace("/", DIRECTORY_SEPARATOR, $name);
		$file_loc = $path . $name . '.php';

		if (!is_file($file_loc)) {
			throw new \Exception("`$name` is Not Found in Views");
		}
		//get the output
		$output = (function () use ($file_loc, $data): string {
			extract($data);
			ob_start();
			include $file_loc;
			return ob_get_clean() ?: '';
		})();

		// if we have setted a layout
		if (!is_null($this->layout)) {
			$layoutView   = $this->layout;
			$this->layout = null;
			//reread it again
			$output     = $this->Render($layoutView, $data);
		}

		return $output;
	}

	public function RenderCell($name, $data)
	{
		list($class, $metod) = explode("::", $name);
		$class = "Cells\\" . $class;

		$obj = new $class;
		return call_user_func_array(
			array($obj, $metod),
			$data
		);
	}

	public function extend(string $layout)
	{
		$this->layout = $layout;
	}

	public function renderSection(string $sectionName)
	{
		if (!isset($this->sections[$sectionName])) {
			echo '';
			return;
		}

		foreach ($this->sections[$sectionName] as $key => $contents) {
			echo $contents;
			unset($this->sections[$sectionName][$key]);
		}
	}

	public function section(string $name)
	{
		$this->currentSection = $name;
		ob_start();
	}

	public function endSection()
	{
		$contents = ob_get_clean();

		if (empty($this->currentSection)) {
			throw new \Exception('View themes, no current section.');
		}

		// Ensure an array exists so we can store multiple entries for this.
		if (!array_key_exists($this->currentSection, $this->sections)) {
			$this->sections[$this->currentSection] = [];
		}
		$this->sections[$this->currentSection][] = $contents;

		$this->currentSection = null;
	}
}
