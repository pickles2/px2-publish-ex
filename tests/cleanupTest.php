<?php
/**
 * test for pickles2\px2-publish-ex
 */
class cleanupTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}




	/**
	 * Clean Up
	 */
	public function testCleanUp(){
		// 後始末

		$output = $this->passthru( [
			'php', __DIR__.'/testdata/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();

		$output = $this->passthru( [
			'php', __DIR__.'/testdata/publish/px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();

		$output = $this->passthru( [
			'php', __DIR__.'/testdata/skip_default_device/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();

		$output = $this->passthru( [
			'php', __DIR__.'/testdata/publish_vendor_dir/src_px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();

		$this->assertTrue( !is_dir( __DIR__.'/testdata/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testdata/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}//testCleanUp();



	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellcmd($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}
