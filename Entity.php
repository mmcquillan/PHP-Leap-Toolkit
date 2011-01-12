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

class Entity
{

	// properties
	private $db;
	private $Table;
	private $Fields = array();
	private $Fetched = false;
	
	// constructor
	public function __construct($DBObject, $TableName)
	{
		$this->db = &$DBObject;
		$this->Table = $TableName;
		$this->InitColumns();
	}

	// initialize columns
	private function InitColumns() {
		if(count($this->Fields) == 0) {
			$sql = "SHOW Columns FROM " . $this->Table;
			$ds = &$this->db->GetDataSet($sql);
			foreach($ds as $row) {
			
				// get the name
				$Name = $row['Field'];
				
				// get the data type and length
				$fparen = strpos($row['Type'], '(');
				if($fparen > 0) {
					$DataType = substr($row['Type'], 0, $fparen);
					$Length = substr($row['Type'], $fparen+1, strpos($row['Type'], ')')-$fparen-1);
				}
				else {
					$DataType = $row['Type'];
					$Length = 0;
				}
				
				// get required
				if($row['Null'] == 'Yes') {
					$Required = false;
				}
				else {
					$Required = true;
				}
				
				// get primary key
				if($row['Key'] == 'PRI') {
					$PrimaryKey = true;
				}
				else {
					$PrimaryKey = false;
				}
				
				// get auto number
				if(strpos($row['Extra'], 'auto_increment') !== false) {
					$AutoNumber = true;
				}
				else {
					$AutoNumber = false;
				}

				// add to columns
				$this->InitColumn($Name, $DataType, $Length, $Required, $PrimaryKey, $AutoNumber);

			}
		}
		
	}
	
	// initialize column
	public function InitColumn($Name, $DataType, $Length, $Required, $PrimaryKey, $AutoNumber) {
		$this->Fields[$Name] = new Field();
		$this->Fields[$Name]->Name = $Name;
		$this->Fields[$Name]->DataType= $DataType;
		$this->Fields[$Name]->Length= $Length;
		$this->Fields[$Name]->Required= $Required;
		$this->Fields[$Name]->PrimaryKey= $PrimaryKey;
		$this->Fields[$Name]->AutoNumber= $AutoNumber;
		$this->Fields[$Name]->Value = null;
	}

	// generic get
	public function __get($member)
	{
		return $this->Fields[$member]->Value;
	}

	// generic set
	public function __set($member, $value)
	{
		$this->Fields[$member]->Value = $value;
	}

	// generic fetch
	public function Fetch()
	{
		$sql = "SELECT * FROM " . $this->Table . " WHERE ";
		$first = true;
		foreach($this->Fields as $field) {
			if(!is_null($field->Value)) {
				if(!$first) {
					$sql .= " AND ";
				}
				$sql .= $field->Name . "=" . $this->db->Quote($field->Value);
				$first = false;
			}
		}
		$sql .= " LIMIT 1";
		$ds = &$this->db->GetDataSet($sql);
		if($ds !== false && count($ds) == 1) {
			foreach($ds[0] as $k => $v) {
				$this->Fields[$k]->Value = $v;
			}
			$this->Fetched = true;
			return true;
		}
		else {
			return false;
		}
	}

	// save
	public function Save()
	{
		if($this->Fetched) {
			$this->Update();
		}
		else {
			$this->Create();
		}
	}
	
	// create
	public function Create()
	{
		// assemble the sql statement
		$sql = "INSERT INTO " . $this->Table . " (";
		$first = true;
		foreach($this->Fields as $field) {
			if(!is_null($field->Value)) {
				if(!$first) {
					$sql .= ",";
				}
				$sql .= $field->Name;
				$first = false;
			}
		}
		$sql .= ") VALUES (";
		$first = true;
		foreach($this->Fields as $field) {
			if(!is_null($field->Value)) {
				if(!$first) {
					$sql .= ",";
				}
				$sql .= $this->db->Quote($field->Value);
				$first = false;
			}
		}
		$sql .= ")";
		
		// assemble the sql statement
		$this->db->ExecSQL($sql);
		
		// set the auto number if exists
		foreach($this->Fields as $field) {
			if($field->AutoNumber) {
				$this->Fields[$field->Name]->Value = $this->db->GetID();
			}
		}
	}

	// update
	public function Update()
	{
		$pre = "UPDATE " . $this->Table . " SET ";
		$post = " WHERE ";
		$prefirst = true;
		$postfirst = true;
		foreach($this->Fields as $field) {
			if($field->PrimaryKey) {
				if(!$postfirst) {
					$post .= " AND ";
				}
				$post .= $field->Name . "=" . $this->db->Quote($field->Value);
				$postfirst = false;
			}
			else {
				if(!is_null($field->Value)) {
					if(!$prefirst) {
						$pre .= ", ";
					}
					$pre .= $field->Name . "=" . $this->db->Quote($field->Value);
					$prefirst = false;
				}
			}
		}
		$sql = $pre . $post;		
		$ret = $this->db->ExecSQL($sql);
		if($ret == 1) {
			return true;
		}
		else {
			return false;
		}
	}

	// delete
	public function Delete()
	{
		$sql = "DELETE FROM " . $this->Table . " WHERE ";
		foreach($this->Fields as $field) {
			$first = true;
			if($field->PrimaryKey) {
				if(!$first) {
					$sql .= " AND ";
				}
				$sql .= $field->Name . "=" . $this->db->Quote($field->Value);
				$first = false;
			}
		}
		$ret = $this->db->ExecSQL($sql);
		if($ret == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	// validate
	public function Validate($chk)
	{
		foreach($this->Fields as $field) {
			if(!$field->AutoNumber) {
				// check for required
				if($field->Required) {
					$chk->Required($field->Value, $field->Name . ' is required.');
				}
				// check for numbers
				if($field->DataType == 'int' || $field->DataType == 'smallint' || $field->DataType == 'mediumint' || $field->DataType == 'tinyint' || $field->DataType == 'bigint') {
					$chk->Number($field->Value, $field->Name . ' is not a number.');
				}
				// check for strings too big
				if($field->DataType == 'char' || $field->DataType == 'varchar') {
					$chk->StringLengthLT($field->Value, ($field->Length + 1), $field->Name . ' is too long.');
				}
			}
		}
	}

	// to json
	public function ToJSON()
	{
		$json = array();
		foreach($this->Fields as $field) {
			$json[$field->Name] = $field->Value;
		}
		return json_encode($json);
	}

	// to xml
	public function ToXML()
	{
		$xml = '<entity>' . "\n";
		foreach($this->Fields as $field) {
			$xml .= "\t" . '<' . $field->Name . '>' . $field->Value . '</' . $field->Name . '>' . "\n";
		}
		$xml .= '</entity>';
		return $xml;
	}

}

class Field {
    public $Name;
    public $DataType;
    public $Length;
    public $Required;
    public $PrimaryKey;
    public $AutoNumber;
    public $Value;
}

?>
