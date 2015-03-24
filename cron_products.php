<?php
use Ecocentauro\tools as tools,
	Zend\Config\Writer\Ini as IniWriter;

include_once 'tools.php';
$separator = DIRECTORY_SEPARATOR;

$products = new tools();
$products->firebird_config_query('product');
$products->compare_last_cron_result('products.txt');
$products->store_query_result('products.txt');

if (!empty($products->last_cron_difference)) {
  $diff = $products->last_cron_difference;

  // Get NX Wsclient Config.
  $nx_wsclient_root_folder = $products->config['nx_wsclient']['root_folder'];
  $nx_wsclient_config_file = $nx_wsclient_root_folder . DIRECTORY_SEPARATOR . 'config.ini';
  $nx_wsclient_config = parse_ini_file($nx_wsclient_config_file, TRUE);
  $nx_cod_cidade = $products->config['nx_wsclient']['cod_cidade'];
  
  // Get NX Wsclient Produto Folder.
  $nx_dados_folder = $products->config['nx_wsclient']['root_folder'] . DIRECTORY_SEPARATOR . 'dados';
  if (isset($nx_wsclient_config['pastas']['dados']) && file_exists($nx_wsclient_config['pastas']['dados'])) {
	$nx_dados_folder = $nx_wsclient_config['pastas']['dados'];
  }
  $nx_produto_folder = $nx_dados_folder . DIRECTORY_SEPARATOR . 'produto';

  $ini_writter = new IniWriter();

  // Deletions | Set stock to zero.
  if (isset($diff['deleted'])) {
	foreach($diff['deleted'] as $product_id => $product) {
	  $product['qtde_em_estoque'] = 0;
	  $product['cod_cidade'] = $nx_cod_cidade;

	  $ini_writter->toFile("$nx_produto_folder$separator$product_id.txt", $product);
	}
	unset($diff['deleted']);
  }

  // Insertions and Updates.
  foreach($diff as $product_id => $product) {
	$product['cod_cidade'] = $nx_cod_cidade;
	$ini_writter->toFile("$nx_produto_folder$separator$product_id.txt", $product);
  }

  // Call NX WSClient sync.
  $cli = "$nx_wsclient_root_folder$separator" . "cli.php";
  passthru("php $cli sincronizar");
}
