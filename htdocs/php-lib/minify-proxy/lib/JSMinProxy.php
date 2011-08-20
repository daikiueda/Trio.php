<?php

class JSMinProxy{

	public static function minify( $code_str ){
		require_once 'JSMin/JSMin.php';
		return JSMin::minify( $code_str );
	}

	private $loaderSourceDir;
	private $loaderSourceFilename;
	private $cashDir;

	public function __construct( $loader_filepath ){
		preg_match( "/^(.+\/)([^\/]+)$/", $loader_filepath, $matches );
		$this->loaderSourceDir = $matches[1];
		$this->loaderSourceFilename = $matches[2];
	}

	private function adjustSourceFilePath( $filepath ){
		return $this->loaderSourceDir . $filepath;
	}

	private function getSourceFilePathes(){
		$loader_source_code = file_get_contents( $this->loaderSourceDir . $this->loaderSourceFilename );

		preg_match( "/required = \[([^\]]*)\]/s", $loader_source_code, $matches );
		preg_match_all( "/['\"](.+.js)['\"]/", $matches[1], $file_pathes );

		return array_map( array( $this, 'adjustSourceFilePath' ), $file_pathes[1] );
	}

	private function isSourceFileUpdated( $cash_filepath, $source_filepathes ){
		$cash_filetime = filemtime( $cash_filepath );

		if( $cash_filetime < filemtime( $this->loaderSourceDir . $this->loaderSourceFilename ) ){
			return true;
		}

		for( $i = 0, $fileCount = count( $source_filepathes ); $i < $fileCount; $i++ ){
			if( $cash_filetime < filemtime( $source_filepathes[$i] ) ){
				return true;
			}
		}

		return false;
	}

	public function setCashDir( $dirname ){
		$this->cashDir = rtrim( $dirname, '/' ) . '/';
	}

	public function serve(){
		$target_files = $this->getSourceFilePathes();

		if(
			isset( $this->cashDir ) &&
			file_exists( $cash_filepath = $this->cashDir . $this->loaderSourceFilename ) &&
			!$this->isSourceFileUpdated( $cash_filepath, $target_files )
		){
			readfile( $cash_filepath );
			exit;
		}

		$code_str = '';
		for( $i = 0, $fileCount = count( $target_files ); $i < $fileCount; $i++ ){
			$code_str .= file_get_contents( $target_files[$i] );
		}

		$code_str = JSMinProxy::minify( $code_str );

		if( file_exists( $this->cashDir ) ){
			$cash_file_handle = fopen( $cash_filepath, "w+" );
			fwrite( $cash_file_handle, $code_str );
			fclose( $cash_file_handle );
		}

		echo $code_str;
	}

}
