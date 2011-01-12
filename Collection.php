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

class Collection implements Iterator
{

	// properties
	private $db;
	private $sql;
	public $collection = array();
	
	// constructor
	public function __construct($DBObject, $SelectSQL)
	{
		$this->db = &$DBObject;
		$this->sql = $SelectSQL;
	}

	// fetch
	public function Fetch()
	{
		$ds = &$this->db->GetDataSet($this->sql);
		if($ds !== false) {
			foreach($ds as $row) {
				$this->collection[count($this->collection)] = new CollectionItem($row);
			}
		}
	}

	// count
	public function Len()
	{
		if($this->collection === false) {
			return 0;
		}
		else {
			return count($this->collection);
		}
	}

	// exists
	public function Exists()
	{
		if($this->collection === false || count($this->collection) == 0) {
			return false;
		}
		else {
			return true;
		}
	}

	//////////////////////////////////////////////////////////////////////////////
	// iterator implementations //////////////////////////////////////////////////
	
	public function rewind() {
		reset($this->collection);
	}

	public function current() {
		$i = current($this->collection);
		return $i;
	}

	public function key() {
		$i = key($this->collection);
		return $i;
	}

	public function next() {
		$i = next($this->collection);
		return $i;
	}

	public function valid() {
		$i = $this->current() !== false;
		return $i;
	}
	
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	
}

class CollectionItem {

	// properties
	private $Record = array();

	// 
	public function __construct($row)
	{
		$this->Record = &$row;
	}

	// generic get
	public function __get($member)
	{
		return $this->Record[$member];
	}
    
}


?>
