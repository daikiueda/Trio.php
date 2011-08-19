<?php

define("BUILD_DIR_NAME", "build");

class JSMinProxy {

	public static function minify( $code_str ){
		require_once '../JSMin/JSMin.php';
		return JSMin::minify($code_str);
	} 
	
	private $loaderSourceDir = '';
	private $loaderSourceFilename = '';
	
	public function __construct(){
		preg_match( "/^(.+\/)([^\/]+)$/", $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REDIRECT_URL"], $matches );
		$this->loaderSourceDir = $matches[1];
		$this->loaderSourceFilename = $matches[2];
	} 
	
	private function adjustSourceFilePath( $my_filepath ){
		return $this->loaderSourceDir . $my_filepath;
	}
	
	private function getSourceFilePathes(){
		$loader_source_code = file_get_contents( $this->loaderSourceDir . $this->loaderSourceFilename );

		preg_match("/required = \[([^\]]*)\]/s", $loader_source_code, $matches);
  		preg_match_all( "/['\"](.+.js)['\"]/", $matches[1], $file_pathes );

  		return array_map( array( $this, adjustSourceFilePath ), $file_pathes[1]);
	}
	
	private function isSourceFileUpdated(  $my_cash_filepath, $my_source_filepathes ){
		$cash_filetime = filemtime($my_cash_filepath);
		
		if( $cash_filetime < filemtime($this->loaderSourceDir . $this->loaderSourceFilename)){
			return true;
		}
		
		for( $i=0, $fileCount = count($my_source_filepathes); $i < $fileCount; $i++ ){
			if( $cash_filetime < filemtime($my_source_filepathes[$i]) ){
				return true;			
			}
		}
				
		return false;
	}

	public function serve(){
		$cash_filepath = $this->loaderSourceDir.'/'.BUILD_DIR_NAME.'/'.$this->loaderSourceFilename;
		$target_files = $this->getSourceFilePathes();
		
		if( file_exists($cash_filepath) ){
			if(!$this->isSourceFileUpdated($cash_filepath, $target_files)){
				readfile($cash_filepath);
				exit;
			}
		}
		
		$code_str = '';
		for( $i=0, $fileCount = count($target_files); $i < $fileCount; $i++ ){
			$code_str .= file_get_contents($target_files[$i]);
		}
		
		
		$code_str = JSMinProxy::minify($code_str);
		
		if( file_exists($this->loaderSourceDir.'/'.BUILD_DIR_NAME) ){
		    $cash_file_handle = fopen($cash_filepath, "w+");
		    fwrite($cash_file_handle, $code_str);
		    fclose($cash_file_handle);
		}

		echo $code_str;
	}
}

if( !isset( $_SERVER["REDIRECT_URL"] ) ) return;

$js_min_proxy = new JSMinProxy();
$js_min_proxy->serve();
