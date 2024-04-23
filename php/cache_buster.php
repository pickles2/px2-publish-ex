<?php
/**
 * PX Commands "publish" cache buster
 */
namespace tomk79\pickles2\publishEx;

/**
 * PX Commands "publish" cache buster
 */
class cache_buster{

	/** Picklesオブジェクト */
	private $px;

	/** プラグイン設定 */
	private $plugin_conf;

	/** コンテンツハッシュ値のキャッシュ */
	private $content_hash_cache = array();

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定
	 */
	public function __construct( $px, $json ){
		$this->px = $px;
		$this->plugin_conf = $json;
	}

	/**
	 * キャッシュバスティングの対象となるパスか判定する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @return boolean キャッシュバスティングの対象となる場合に true, それ以外の場合に false
	 */
	public function is_enabled_path($path){
		if( preg_match('/\.(?:css|js|gif|png|jpg|jpeg|jpe|webp)$/i', $path) ){
			return true;
		}
		return false;
	}

	/**
	 * コンテンツハッシュ値をキャッシュに記憶する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @param string $hash 対象コンテンツのハッシュ値
	 * @return boolean 成功時に true を返します。
	 */
	public function set_content_hash($path, $hash){
		if( !strlen($hash ?? '') ){
			return false;
		}
		if( strlen($this->content_hash_cache[$path] ?? '') ){
			return true;
		}
		$this->content_hash_cache[$path] = $hash;
		return true;
	}

	/**
	 * コンテンツハッシュ値を取得する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @return string ハッシュ値を返します。
	 */
	public function get_content_hash($path){
		return $this->content_hash_cache[$path] ?? false;
	}

}
