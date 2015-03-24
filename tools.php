<?php
namespace Ecocentauro;

class tools {
  protected $cwd;
  public $config;
  protected $firebird_credentials;
  protected $db_connection;
  public $query_result;
  public $last_cron_difference;

  /**
   * Loads the config.ini.
   */
  public function __construct() {
	$cwd = pathinfo(__DIR__);
	$this->cwd = $cwd = $cwd['dirname'] . DIRECTORY_SEPARATOR . $cwd['basename'];
	$config_file = $cwd . DIRECTORY_SEPARATOR . "config.ini";
	$this->config = $config = parse_ini_file($config_file, TRUE);

	$nx_wsclient_root_folder = $config['nx_wsclient']['root_folder'];
	$nx_wsclient_autoload_file = $nx_wsclient_root_folder . DIRECTORY_SEPARATOR . $config['nx_wsclient']['autoload_file'];

	if (file_exists($nx_wsclient_autoload_file)) {
	  include_once $nx_wsclient_autoload_file;
	}
  }

  /**
   * Creates a firebird connection resource.
   */
  private function firebird_db_connect() {
	$db = $this->config['firebird_credentiais']['db'];
	$username = $this->config['firebird_credentiais']['username'];
	$password = $this->config['firebird_credentiais']['password'];

	$this->db_connection = @ibase_connect($db, $username, $password);
	
	if (ibase_errmsg()) {
	  $msg = ibase_errmsg();
	  $nxtools = new nxtools();
	  print "SEND AN EMAIL $msg\n";
	  
	  throw new \Exception($msg);
	}
  }

  /**
   * Query the database with a pre-defined table/fields from the config file.
   *
   * @param String $table
   *   The config.ini section holding the table details for quering it.
   *   i.e. [firebird_$table]
   */
  public function firebird_config_query($table) {
	if (!is_resource($this->db_connection)) {
	  $this->firebird_db_connect();
	}

	if (isset($this->config["firebird_$table"]['table_name'])) {
	  $table_name = $this->config["firebird_$table"]['table_name'];
	  $table_fields = $this->config["firebird_$table"]['fields'];
	  $field_id = $this->config["firebird_$table"]['field_id'];
  
	  $query_fields = '';
	  foreach ($table_fields as $table_field_name => $nx_table_field_name) {
		if (!empty($query_fields)) {
		  $query_fields .= ', ';
		}
		$query_fields .= $table_field_name;
	  }
  
	  $query = "SELECT $query_fields FROM $table_name";
	  $query = @ibase_query($this->db_connection, $query);

	  if (ibase_errmsg()) {
		$test = ibase_errmsg();
		print "SEND AN EMAIL $test\n";
	  }

  	  $result = array();

	  $row = 0;
	  while ($row_temp = ibase_fetch_assoc($query)) {
		foreach($row_temp as $table_field_name => $table_field_value) {
		  $nx_table_field_name = $table_fields[$table_field_name];
		  $field_id_value = $row_temp[$field_id];
	  
		  $result[$field_id_value][$nx_table_field_name] = $table_field_value;
		}
		$row++;
	  }
	  $this->query_result = $result;
	}
	else {
	  print "SEND AN EMAIL\n";
	}

	if (is_resource($this->db_connection)) {
	  ibase_close($this->db_connection);
	}
  }

  /**
   * Converts the query_result property value to json and saves it into a file
   * located in the tmp folder.
   *
   * @param String $file_name
   *   The name of the file to be saved in the tmp folder.
   */
  public function store_query_result($file_name) {
	$cwd = $this->cwd;
	$tmp_folder = $cwd . DIRECTORY_SEPARATOR . 'tmp';
	$tmp_file_path = $tmp_folder . DIRECTORY_SEPARATOR . $file_name;

	$file_content = json_encode($this->query_result);

	if (!file_exists($tmp_folder)) {
	  mkdir($tmp_folder, 0777, true);
	}
	file_put_contents($tmp_file_path, $file_content);
  }

  /**
   * Search for differences between last's cron result and current's cron
   * result. If any difference is found then it is saved to
   * last_cron_difference property.
   *
   * @param String $last_cron_file_name
   *   The file name containing last's cron content.
   */
  public function compare_last_cron_result($last_cron_file_name) {
	$cwd = $this->cwd;
	$tmp_folder = $cwd . DIRECTORY_SEPARATOR . 'tmp';
	$tmp_file_path = $tmp_folder . DIRECTORY_SEPARATOR . $last_cron_file_name;
	$content_last_cron = array();
	$content_current_cron = $this->query_result;
	$content_difference = array();

	if (file_exists($tmp_file_path)) {
	  $content_last_cron = json_decode(file_get_contents($tmp_file_path), TRUE);
	}

	foreach($content_current_cron as $content_id => $content_row) {
	  // New content.
	  if (!array_key_exists($content_id, $content_last_cron)) {
		$content_difference[] = $content_row;
	  }
	  else {
		// Content update.
		$diff = array_diff($content_current_cron[$content_id], $content_last_cron[$content_id]);
		if (!empty($diff)) {
		  $content_difference[] = $content_row;
		}
	  }
	}

	// Content deleted.
	/*$rows_deleted = array_diff_key($content_last_cron, $content_current_cron);
	if (!empty($rows_deleted)) {
	  foreach($rows_deleted as $content_id => $content_row) {
		
	  }
	}*/

	$this->last_cron_difference = $content_difference;
  }
}
