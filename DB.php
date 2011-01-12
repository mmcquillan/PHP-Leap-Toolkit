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

class DB
{
	private $dbx;
	private $debug = false;

	public function __construct($DBServer, $DBName, $DBLogin, $DBPasswd)
	{
		$this->dbx = mysql_connect($DBServer, $DBLogin, $DBPasswd);
		mysql_select_db($DBName, $this->dbx);
	}
	
	public function Debug() {
		$this->debug = true;
	}
	
	public function BeginTransaction() {
		return $this->ExecSQL("BEGIN TRANSACTION");
	}

	public function CommitTransaction() {
		return $this->ExecSQL("COMMIT TRANSACTION");
	}
	
	public function RollbackTransaction() {
		return $this->ExecSQL("ROLLBACK TRANSACTION");
	}

	public function ExecSQL($sql)
	{
		if($this->debug) { print "[" . $sql . "]\n"; }
		mysql_query($sql, $this->dbx);
		return mysql_affected_rows();
	}

	public function GetID()
	{
		return mysql_insert_id($this->dbx);
	}

	public function GetScaler($sql)
	{
		if($this->debug) { print "[" . $sql . "]\n"; }
		$result = mysql_query($sql, $this->dbx);
		$row = mysql_fetch_array($result);
		return $row[0];
	}

	public function GetDataSet($sql)
	{
		if($this->debug) { print "[" . $sql . "]\n"; }
		$result = mysql_query($sql, $this->dbx);
		if(mysql_affected_rows() > 0)
		{
			for($i = 0; $i < mysql_num_fields($result); $i++)
			{
				$fields[$i] = mysql_field_name($result, $i);
			}
			$i = 0;
			while($row = mysql_fetch_array($result))
			{
				for($j = 0; $j < count($fields); $j++)
				{
					$ds[$i][$fields[$j]] = $row[$j];
				}
				$i++;
			}
			return $ds;
		}
		else
		{
			return false;
		}

	}

	public function PrepSQL($sql, $vars)
	{
		foreach($vars as $key => $var)
		{
			$vars[$key] = $this->Quote($var);
		}
		return vsprintf($sql, $vars);
	}

	public function Quote($value)
	{
		if(get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}
		if(!is_numeric($value))
		{
			$value = "'" . mysql_real_escape_string($value, $this->dbx) . "'";
		}
		return $value;
	}

	public function LastError()
	{
		return mysql_error();
	}

	public function Now()
	{
		return strftime("%Y-%m-%d %H:%M:%S");
	}

	public function __destruct()
	{
		mysql_close();
	}

}

?>
