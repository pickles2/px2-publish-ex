# pickles2/px2-publish-ex

機能拡張された Pickles 2 のパブリッシュプラグインです。


## 導入手順 - Setup

### 1. composer.json に pickles2/px2-publish-ex を追加

require の項目に、"pickles2/px2-publish-ex" を追加します。

```json
{
	"require": {
		"pickles2/px2-publish-ex": "^2.0.0"
	},
}
```


追加したら、`composer update` を実行して変更を反映することを忘れずに。

```bash
$ composer update
```


### 2. config.php に、プラグインを設定

設定ファイル config.php (通常は `./px-files/config.php`) を編集します。
`before_content` にある、PX=publish の設定を、次の例を参考に書き換えます。

```php
<?php
	/* 中略 */

	/**
	 * funcs: Before content
	 *
	 * サイトマップ読み込みの後、コンテンツ実行の前に実行するプラグインを設定します。
	 */
	$conf->funcs->before_content = array(
		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=publish (px2-publish-ex)
		'tomk79\pickles2\publishEx\publish::register()' , // オプションについては後述
	);
```

Pickles 2 の設定をJSON形式で編集している方は、`config.json` の該当箇所に追加してください。

### 3. パブリッシュを実行

標準的な Pickles 2 のパブリッシュと同じ手順で、パブリッシュコマンドを実行します。

```bash
$ php .px_execute.php /?PX=publish.run
```


## オプション - Options

### コマンドラインオプション - CLI Options

次のコマンドラインオプションは、 `pickles2/px-fw-2.x` に含まれる[オリジナルのパブリッシュ機能](http://pickles2.pxt.jp.pub.localhost/manual/publish/)のオプションと互換します。

- `path_region`
- `paths_region`
- `paths_ignore`
- `keep_cache`

### プラグインオプション - Plugin Options

```php
<?php
	$conf->funcs->before_content = array(
		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=publish (px2-publish-ex)
		'tomk79\pickles2\publishEx\publish::register('.json_encode(array(
			// パブリッシュ対象から常に除外するパスを設定する。
			// (ここに設定されたパスは、動的なプレビューは可能)
			// ※この設定は、 `pickles2/px-fw-2.x` に付属するオリジナルのパブリッシュ機能と互換します。
			'paths_ignore'=> array(
				'/sample_pages/no_publish/*'
			),

			// パブリッシュするデバイスの情報を設定する。
			// 複数のデバイス情報を配列で指定します。
			// ここには、追加で処理したいデバイスの設定だけを記述します。
			// 本来のパブリッシュで処理される標準的なデバイスは、暗黙的に処理されます。
			// つまり、このオプションが空白でも、 1つの標準的なデバイスとしてパブリッシュされます。
			'devices'=>array(
				array(
					'user_agent'=>'iPhone', // USER_AGENT 文字列
					'path_publish_dir'=>'./px-files/dist_smt/', // このデバイス向けのパブリッシュ先ディレクトリ
					'path_rewrite_rule'=>'functionNameOf::rewrite_smt', // パスの書き換えロジック(コールバックメソッド名を指定します)
				),
				array(
					'user_agent'=>'iPad',
					'path_publish_dir'=>'./px-files/dist_tab/',
					'path_rewrite_rule'=>'functionNameOf::rewriter_tab',
				),
				/* ...以下同様... */
			)
		)).')' ,
	);
```


## 更新履歴 - Change log

### pickles2/px2-publish-ex v2.0.0 (リリース日未定)

- First release.


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
