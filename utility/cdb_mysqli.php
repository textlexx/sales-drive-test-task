<?php

class CDBMysqli{
	
	/*---------------------------------------------------------------------------------------*/
	
	private static $db = EMPTY_OBJ;
	public $error = '';
	private static $db_object = EMPTY_OBJ;
	private static $add_insert_ignore = '';
	
	/*---------------------------------------------------------------------------------------*/
	
	private function __construct(){}	
	private function __clone(){}	
	private function __sleep(){}	
	private function __wakeup(){}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Creating an object to work with a database
	public static function createDb(){
		
		if(CDBMysqli::$db->empty){
			
			CDBMysqli::$db = new self();
		}
		
		if(CDBMysqli::$db_object->empty){
			
			if(is_array(DB_CONNECTIONS[0])){
			
				$db_extension = new mysqli(
					DB_CONNECTIONS[0]['server'], 
					DB_CONNECTIONS[0]['user'], 
					DB_CONNECTIONS[0]['pass'], 
					DB_CONNECTIONS[0]['db'],
                    DB_CONNECTIONS[0]['port']
				);
				
				//------------------------------------------------
				if($db_extension->connect_errno) return false;
				//------------------------------------------------
				
				if($db_extension){
					
					CDBMysqli::$db_object = $db_extension;
				}else{
					
					if($db_extension->error){
						
						Notifications::set_e($db_extension->error);
						
						return false;
					}
				}
			}else{
				
				Notifications::set_e('Error mysqli. Connection to db. Config variable is not array.');

				return false;
			}
		}
		
		return CDBMysqli::$db;
	}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Returns the object of the extension itself for interaction with the database
	public static function getExtensionDbObject(){
		
		return CDBMysqli::$db_object;
	}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Setting the database encoding 
	public static function setDbCharset($charset = 'utf8', $collation = 'utf8_general_ci'){
		
		if(!is_object(CDBMysqli::$db_object)){return false;}
		
		CDBMysqli::$db_object->query('SET NAMES "'.$charset.'"');
		CDBMysqli::$db_object->query('SET CHARACTER SET "'.$charset.'"');
		CDBMysqli::$db_object->query('SET SESSION collation_connection = "'.$collation.'"');
	}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Inserting into a database
	/*
	$table_field = array(
		0 => 'field name 1',
		1 => 'field name 1',
		2 => 'field name 1',
	);
	
	$insert_data = array(
		0 => array(
			0 => 'value 1',
			1 => 'value 2',
			2 => 'value 3',
		),
		1 => array(
			0 => 'value 4',
			1 => 'value 5',
			2 => 'value 6',
		),
		2 => array(
			0 => 'value 7',
			1 => 'value 8',
			2 => 'value 9',
		),
	);
	*/
	public function dbInsert($table_name = '', $table_field = array(), $insert_data = array()){
		
		// grouping fields
		$field_group = '(';
		foreach($table_field as $value){
			
			$field_group .= '`'.CDBMysqli::$db_object->real_escape_string($value).'`,';
		}
		
		$field_group = preg_replace('#,[ \t\r\n]*$#', ')', $field_group);
		
		// grouping field values
		$data_group = '';
		foreach($insert_data as $value){
			
			$data_group .= '(';
			foreach($value as $value2){
				
				$data_group .= '"'.CDBMysqli::$db_object->real_escape_string($value2).'",';
			}
			$data_group = preg_replace('#,[ \t\r\n]*$#', '),'."\r\n", $data_group);
		}
		$data_group = preg_replace('#,[ \t\r\n]*$#', "\r\n", $data_group);
		
		// Completing and fulfilling the request
		$sql = '
			INSERT '.CDBMysqli::$add_insert_ignore.' INTO `'.CDBMysqli::$db_object->real_escape_string($table_name).'`
			'.$field_group.'
			VALUES
			'.$data_group.'
		';

		// Clean insert ignore for other sql query
		$this->delInsertIgnore();
		
		$executed_sql = CDBMysqli::$db_object->query($sql);
		
		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $executed_sql;
	}
	
	/*---------------------------------------------------------------------------------------*/

	public function escape($value = false){
		
		return CDBMysqli::$db_object->real_escape_string($value);
	}
	
	// If IGNORE is required when using dbInsert(), this function can be used to add it, it must be called before dbInsert()
	public function addInsertIgnore(){
		
		CDBMysqli::$add_insert_ignore = 'IGNORE';
	}
	
	/*---------------------------------------------------------------------------------------*/

	public function delInsertIgnore(){
		
		CDBMysqli::$add_insert_ignore = '';
	}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Updating the database
	/*
	$update_data = array(
		'field 1' => 'data 1',
		'field 2' => 'data 2',
		'field 3' => 'data 3',
	);

	// This add if is mathematic actions:
	$update_data = array(
		'field1' => array(
			'noQuots' => true,
			'value' => 'field1 - 1',
		),
		'field 2' => 'data 2',
		'field 3' => 'data 3',
	),
	*/
	public function dbUpdate($table_name = '', $update_data = array(), $where = ''){
		
		// Grouping fields and data
		$group_data = '';
		foreach($update_data as $key => $value){
			
			// If there are additional parameters for this value, 
			// for example a mathematical expression does not require quotation marks
			if(is_array($value)){

				if($value['noQuots']){

					$inSqlValue = CDBMysqli::$db_object->real_escape_string($value['value']);
				}
			}else{

				$inSqlValue = '"'.CDBMysqli::$db_object->real_escape_string($value).'"';
			}

			$group_data .=
			'`'.CDBMysqli::$db_object->real_escape_string($key).'` = '.$inSqlValue.','."\r\n";
		}
		$group_data = preg_replace('#,[ \t\r\n]*$#', "\r\n", $group_data);
		
		if($where && $where!=''){$where = 'WHERE '.$where;}
		
		// Completing and fulfilling the request
		$sql = '
			UPDATE `'.CDBMysqli::$db_object->real_escape_string($table_name).'`
			SET '.$group_data.' 
			'.$where.'
		';
		
		$executed_sql = CDBMysqli::$db_object->query($sql);
		
		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $executed_sql;
	}
	
	/*---------------------------------------------------------------------------------------*/
	
	// Deleting from the database
	public function dbDelete($query){
		
		$executed_sql = CDBMysqli::$db_object->query($query);
		
		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $executed_sql;
	}
	
	/*---------------------------------------------------------------------------------------*/

	public function dbOneSelect($query){

		$row = CDBMysqli::$db_object->query($query);
		
		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $row->fetch_assoc();
	}

	/*---------------------------------------------------------------------------------------*/

	public function dbAllSelect($query){

		$row = CDBMysqli::$db_object->query($query);
		
		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $row->fetch_all(MYSQLI_ASSOC);
	}

	/*---------------------------------------------------------------------------------------*/

	public function condWhereConstruct(
		$data = array(), 
		$fieldName = 'id', 
		$logOp = 'AND', 
		$addWhereWord = false
	){

		$where = '';

		foreach($data as $key => $val) $where .= ' `'.$fieldName.'` = "'.$val.'" '.$logOp;

		$where = preg_replace('#'.$logOp.'[ \t\r\n]*$#', ' ', $where);

		if($addWhereWord) $where = ' WHERE '.$where;

		return $where;
	}

	/*---------------------------------------------------------------------------------------*/

	public function condWhereConstructHardArray(
		$data = array(), 
		$fieldName = 'id', 
		$logOp = 'AND', 
		$addWhereWord = false
	){

		$where = '';

		foreach($data as $key => $val) {
			
			foreach($val as $key2 => $val2) $where .= ' `'.$fieldName.'` = "'.$val2.'" '.$logOp;
		}

		$where = preg_replace('#'.$logOp.'[ \t\r\n]*$#', ' ', $where);

		if($addWhereWord) $where = ' WHERE '.$where;

		return $where;
	}

	/*---------------------------------------------------------------------------------------*/

	public static function genNextId($tableName = 'users', $rowName = 'id'){
		
		$data = CDBMysqli::$db->dbOneSelect(
            'SELECT MAX(`'.$rowName.'`) AS `max_id` FROM `'.$tableName.'`'
        );

		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		if(!is_numeric($data['max_id'])) return 1;
		return ($data['max_id'] + 1);
	}

	/*---------------------------------------------------------------------------------------*/

	public function doQuery($query){

		$executed_sql = CDBMysqli::$db_object->query($query);

		if(CDBMysqli::$db_object->error){
			
			CDBMysqli::$db->error = CDBMysqli::$db_object->error;
			Notifications::set_e(CDBMysqli::$db_object->error);
			return false;
		}
		
		return $executed_sql;
	}

	/*---------------------------------------------------------------------------------------*/

	public function eString($value){

		return CDBMysqli::$db_object->real_escape_string($value);
	}

	/*---------------------------------------------------------------------------------------*/
}

?>