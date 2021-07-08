<?php
class testCallbackFncs{
	public static function rewrite_tab($path){
		$path = dirname($path).'/_tab/'.basename($path);
		return $path;
	}
	public static function rewrite_en($path){
		$path = '/en/'.dirname($path).basename($path);
		return $path;
	}
}
