<?php

/**
 * minify js proxy
 * JSファイルの圧縮
 *
 * このPHPプログラムは、URLリライト経由で呼び出されることを前提としています。
 * リライト前のURLから対象のJSファイルを決定し、圧縮処理を実行します。
 *
 * @author Daiki UEDA
 * @version 1.0
 */
if( $_SERVER["SCRIPT_NAME"] === $_SERVER["REQUEST_URI"] ){
	return;
}

require_once 'lib/JSMinProxy.php';

$js_min_proxy = new JSMinProxy( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REQUEST_URI"] );
$js_min_proxy->setCacheDir( $_SERVER["DOCUMENT_ROOT"] . '/js/build' );
$js_min_proxy->serve();
