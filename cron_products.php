<?php
use Ecocentauro\tools as tools;

include_once 'tools.php';

$products = new tools();
$products->firebird_config_query('product');
//$products->store_query_result('products.txt');
$products->compare_last_cron_result('products.txt');

$nx_wsclient_root_folder = $products->config['nx_wsclient']['root_folder'];
$nx_wsclient_config_file = $nx_wsclient_root_folder . DIRECTORY_SEPARATOR . 'config.ini';

$nx_wsclient_config = parse_ini_file($nx_wsclient_config_file, TRUE);

print_r($nx_wsclient_config);
