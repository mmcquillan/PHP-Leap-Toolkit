<?php 
/******************************************************************************
*** PHP Leap Toolkit version 0.4 **********************************************
*******************************************************************************
Copyright (c) 2008 Matt McQuillan - http://mattmcquillan.com/phpleap

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
	private $EntityChanged = false;
	
	// constructor
	public function __construct($DBObject, $TableName)
	{
		$this->db = &$DBObject;
		$this->Table = $TableName;
		$this->InitColumns();
	}

	// debug
	public function Debug()
	{
		foreach($this->Fields as $field) {
			print "[Name:" . $field->Name . "]";
			print "[DataType:" . $field->DataType . "]";
			print "[Length:" . $field->Length . "]";
			print "[Required:" . $field->Nullable . "]";
			print "[PK:" . $field->PrimaryKey . "]";
			print "[AutoNumber:" . $field->AutoNumber . "]";
			print "[OriginalValue:" . $field->OriginalValue . "]";
			print "[CurrentValue:" . $field->OriginalValue . "]";
			print "\n";
		}
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
		$this->Fields[$Name]->Nullable= $Nullable;
		$this->Fields[$Name]->PrimaryKey= $PrimaryKey;
		$this->Fields[$Name]->AutoNumber= $AutoNumber;
		$this->Fields[$Name]->OriginalValue = null;
		$this->Fields[$Name]->CurrentValue = null;
		$this->Fields[$Name]->IsChanged = false;
	}

	// generic get
	public function __get($member)
	{
		return $this->Fields[$member]->CurrentValue;
	}

	// generic set
	public function __set($member, $value)
	{
		if($this->Fields[$member]->OriginalValue != $value) {
			$this->Fields[$member]->IsChanged = true;
		}
		$this->Fields[$member]->CurrentValue = $value;
	}

	// is changed
	public function IsChanged($Name)
	{
		return $this->Fields[$Name]->IsChanged;
	}

	// generic fetch
	public function Fetch()
	{
		$sql = "SELECT * FROM " . $this->Table . " WHERE ";
		$first = true;
		foreach($this->Fields as $field) {
			if(!is_null($field->CurrentValue)) {
				if(!$first) {
					$sql .= " AND ";
				}
				$sql .= $field->Name . "=" . $this->db->Quote($field->CurrentValue);
				$first = false;
			}
		}
		$sql .= " LIMIT 1";
		$ds = &$this->db->GetDataSet($sql);
		if(count($ds) == 1) {
			foreach($ds[0] as $k => $v) {
				$this->Fields[$k]->OriginalValue = $v;
				$this->Fields[$k]->CurrentValue = $v;
				$this->Fields[$k]->IsChanged = false;
			}
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
		foreach($this->Record as $k => $v) {
			if(!is_null($v)) {
				if(!$first) {
					$sql .= ",";
				}
				$sql .= $k;
				$first = false;
			}
		}
		$sql .= ") VALUES (";
		$first = true;
		foreach($this->Record as $k => $v) {
			if(!is_null($v)) {
				if(!$first) {
					$sql .= ",";
				}
				$sql .= $this->db->Quote($v);
				$first = false;
			}
		}
		$sql .= ")";
		
		// assemble the sql statement
		$this->db->ExecSQL($sql);
		
		// set the auto number if exists
		foreach($this->Columns as $column) {
			if($column->AutoNumber) {
				$this->Record[$column->Name] = $this->db->GetID();
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
		foreach($this->Columns as $column) {
			if($column->PrimaryKey) {
				if(!$postfirst) {
					$post .= " AND ";
				}
				$post .= $column->Name . "=" . $this->db->Quote($this->Record[$column->Name]);
				$postfirst = false;
			}
			else {
				if(!is_null($this->Record[$column->Name])) {
					if(!$prefirst) {
						$pre .= ", ";
					}
					$pre .= $column->Name . "=" . $this->db->Quote($this->Record[$column->Name]);
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
		$this->Fields[$k]->OriginalValue = $v;
		$sql = "DELETE FROM " . $this->Table . " WHERE ";
		foreach($this->Columns as $column) {
			$first = true;
			if($column->PrimaryKey) {
				if(!$first) {
					$sql .= " AND ";
				}
				$sql .= $column->Name . "=" . $this->db->Quote($this->Record[$column->Name]);
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

}

class Field
{
    public $Name;
    public $DataType;
    public $Length;
    public $Required;
    public $PrimaryKey;
    public $AutoNumber;
    public $OriginalValue;
    public $CurrentValue;
    public $IsChanged;
}

?>
