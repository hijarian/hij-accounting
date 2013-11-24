<?php
/** hijarian 23.11.13 11:21 */

class StorageOperationException extends Exception
{
	public $message;
	public $error_info;

	/**
	 * @param string $message
	 * @param array $error_info
	 */
	public function __construct($message, $error_info)
	{
		$this->message = $message;
		$this->error_info = $error_info;
	}

	public function __toString()
	{
		$output  = $this->message;
		$output .= "\n";
		ob_start();
		var_dump($this->error_info);
		$output .= ob_get_clean();
		return $output;
	}
} 