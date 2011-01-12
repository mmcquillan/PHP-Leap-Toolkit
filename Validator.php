<?php
/******************************************************************************
*** PHP Leap Toolkit version 0.5 **********************************************
*******************************************************************************
Copyright (c) 2009 Matt McQuillan - http://mattmcquillan.com

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE.
******************************************************************************/

class Validator {

	// properties
	private $errors;
	private $isvalid;

	// constructor
	public function __construct($repeater) {
		
		$this->isvalid = true;
		$this->errors = &$repeater;

	}

	// valid
	public function IsValid() {
		return $this->isvalid;
	}

	// validate required
	public function Required($val, $msg) {
		$ret = true;
		if(trim($val) == '') {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}
	
	// validate compare
	public function Compare($val1, $val2, $msg) {
		$ret = true;
		if($val1 != $val2) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate email
	public function Email($val, $msg) {
		$ret = true;
		if(!preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $val)) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate number
	public function Number($val, $msg) {
		$ret = true;
		if(!is_numeric($val)) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate string length
	public function StringLength($val, $len, $msg) {
		$ret = true;
		if(strlen($val) != $len) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}
	
	// validate string greater than length
	public function StringLengthGT($val, $gt, $msg) {
		$ret = true;
		if(strlen($val) <= $gt) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate string less than length
	public function StringLengthLT($val, $lt, $msg) {
		$ret = true;
		if(strlen($val) >= $lt) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}
	
	// validate number greater than
	public function NumberGT($val, $gt, $msg) {
		$ret = true;
		if($val <= $gt) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate number less than
	public function NumberLT($val, $lt, $msg) {
		$ret = true;
		if($val >= $lt) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

	// validate regular expression
	public function Regex($val, $pattern, $msg) {
		$ret = true;
		if(ereg($pattern, $val) == false) {
			$ret = false;
			$this->isvalid = false;
			$this->errors->Add($msg);
		}
		return $ret;
	}

}

?>
