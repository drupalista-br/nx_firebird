<?php
use Ecocentauro\tools as tools;

include_once 'tools.php';

$products = new tools();
$products->firebird_config_query('product');
$products->store_query_result('products.txt');

