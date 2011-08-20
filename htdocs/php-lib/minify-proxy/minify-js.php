<?php

if( !isset( $_SERVER["REDIRECT_URL"] ) ){
	return;
}

require_once 'lib/JSMinProxy.php';

$js_min_proxy = new JSMinProxy( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"] );
$js_min_proxy->setCashDir( $_SERVER["DOCUMENT_ROOT"] . '/js/build' );
$js_min_proxy->serve();
