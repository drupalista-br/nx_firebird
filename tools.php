<?php
namespace Ecocentauro;

use NXWSClient\tools as nxtools;

class tools {
  protected $cwd;
  protected $config;
  protected $firebird_credentials;

  /**
   *
   */
  public function __construct() {
	$cwd = pathinfo(__DIR__);
	$this->cwd = $cwd = $cwd['dirname'] . DIRECTORY_SEPARATOR . $cwd['basename'];
	$config_file = $cwd . DIRECTORY_SEPARATOR . "config.ini";

	$this->config = $config = parse_ini_file($config_file, TRUE);

	if (file_exists($config['general']['nx_wsclient_autoload'])) {
	  include_once $config['general']['nx_wsclient_autoload'];
	}
  }

  /**
   * 
   */
  public function firebird_db_connect() {
	$db = $this->config['firebird_credentiais']['db'];
	$username = $this->config['firebird_credentiais']['username'];
	$password = $this->config['firebird_credentiais']['password'];
  }

  /**
   * 
   */
  public function firebird_query($table = 'product') {
	$table_name = 'EMPLOYEE';
	$table_fields = array('EMP_NO' => 'test', 'FIRST_NAME' => 'test2');
	
	$query_fields = '';
	foreach ($table_fields as $table_field_name => $nx_table_field_name) {
	  if (!empty($query_fields)) {
		$query_fields .= ', ';
	  }
	  $query_fields .= $table_field_name;
	}
	
	$db_connection = @ibase_connect($db, $username, $password);
	$query = "SELECT $query_fields FROM $table_name";
	$query = @ibase_query($db_connection, $query);
	
	if (!ibase_errmsg()) {
	  $result = array();
	  
	  $row = 0;
	  while ($row_temp = ibase_fetch_assoc($query)) {
		foreach($row_temp as $table_field_name => $table_field_value) {
		  $nx_table_field_name = $table_fields[$table_field_name];
	  
		  $result[$row][$nx_table_field_name] = $table_field_value;
		}
		$row++;
	  }
	
	  print_r($result);
	
	  ibase_close($db_connection);
	}
	else {
	  $test = ibase_errmsg();
	  print "SEND AN EMAIL $test\n";
	}
  }  


}