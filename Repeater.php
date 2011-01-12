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

class Repeater {

	// properties
	private $list = array();
	private $prelist = '';
	private $postlist = '';
	private $preitem = '';
	private $postitem = '';
	
	// constructor
	public function __construct() {
		// do nothing
	}

	// count
	public function Len()
	{
		return count($this->list);
	}

	// exists
	public function Exists()
	{
		if(count($this->list) == 0) {
			return false;
		}
		else {
			return true;
		}
	}

	// add a new item
	public function Add($item) {
		if(is_array($item)) {
			$this->list = array_merge($this->list, $item);
		}
		else {
			$this->list[count($this->list)] = $item;
		}
	}

	// set pre list
	public function SetPreList($val)
	{
		$this->prelist = $val;
	}

	// set post list
	public function SetPostList($val)
	{
		$this->postlist = $val;
	}

	// set pre item
	public function SetPreItem($val)
	{
		$this->preitem = $val;
	}

	// set post item
	public function SetPostItem($val)
	{
		$this->postitem = $val;
	}

	// print list
	public function Render() {
		if($this->Exists()) {
			print $this->prelist;
			foreach($this->list as  $item) {
				print $this->preitem;
				print $item;
				print $this->postitem;
			}
			print $this->postlist;
		}
	}

}

?>
