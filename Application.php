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

class Application {

	// properties
	private $Params = array();
	public $RootURL;
	public $RootDIR;

	// constructor
	public function __construct() {

		// the special params
		$this->Params['Action'] = 'default';
		
		// default roots
		$this->RootURL = 'http://' . $_SERVER['SERVER_NAME'] . '/';
		$this->RootDIR = $_SERVER['DOCUMENT_ROOT'] . '/';

		// the gets
		foreach($_GET as $key => $value) {
			$this->Params[$key] = $value;
		}

		// the posts
		foreach($_POST as $key => $value) {
			$this->Params[$key] = $value;
		}

	}

	// init Session
	public function Session($vars) {

		// if they passed vars
		if(count($vars) > 0) {

			// start the php session
			session_start();

			// loop through an init
			foreach($vars as $var) {
				if(isset($_SESSION[$var]) === false) {
					session_register($var);
					$_SESSION[$var] = NULL;
				}
			}

			// add to the params
			foreach($_SESSION as $key => $value) {
				$this->Params[$key] = $value;
			}

		}

	}

	// init Secure
	public function Secure() {
		
		// secure
		if(is_null($_SESSION['UserID'])) {
			$this->Redirect('');
		}
		
	}

	// redirect page
	public function Redirect($url) {
		header('Location: ' . $this->RootURL . $url);
		exit;
	}

	// generic get
	public function __get($member)
	{
		return $this->Params[$member];
	}

}

?>
