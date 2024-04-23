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

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定
	 */
	public function __construct( $px, $json ){
		$this->px = $px;
		$this->plugin_conf = $json;
	}

}
