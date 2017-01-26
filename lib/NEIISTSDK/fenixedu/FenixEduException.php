<?php

//Basic class for exceptions
class FenixEduException extends Exception {

	private $error;
	private $errorDescription;

	public function __construct($result) {
		$this->error = $result->error;
		$this->errorDescription = $result->errorDescription;
	}

	public function getError() {
		return $this->error;
	}

	public function getErrorDescription() {
		return $this->errorDescription;
	}

}

?>
