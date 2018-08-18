# pickles2/px2-publish-ex

機能拡張された Pickles 2 のパブリッシュプラグインです。


## 導入手順 - Setup

### 1. composer.json に pickles2/px2-publish-ex を追加

`require` の項目に、`pickles2/px2-publish-ex` を追加します。

```json
{
	"require": {
		"pickles2/px2-publish-ex": "^2.0.0"
	}
}
```


追加したら、`composer update` を実行して変更を反映することを忘れずに。

```
$ composer update
```


### 2. config.php に、プラグインを設定

設定ファイル `config.php` (通常は `./px-files/config.php`) を編集します。
`before_content` にある、`PX=publish` の設定を、次の例を参考に書き換えます。

```php
<?php

/* 中略 */

/**
 * funcs: Before content
 *
 * サイトマップ読み込みの後、コンテンツ実行の前に実行するプラグインを設定します。
 */
$conf->funcs->before_content = array(

	// PX=publish (px2-publish-ex)
	'tomk79\pickles2\publishEx\publish::register()' , // オプションについては後述
);
```

※ Pickles 2 の設定をJSON形式で編集している方は、`config.json` の該当箇所に追加してください。


### 3. パブリッシュを実行

標準的な Pickles 2 のパブリッシュと同じ手順で、パブリッシュコマンドを実行します。

```
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
				// USER_AGENT 文字列
				'user_agent'=>'iPhone',

				// このデバイス向けのパブリッシュ先ディレクトリ
				'path_publish_dir'=>'./px-files/dist_smt/',

				// パスの書き換えロジック
				// 次の部品を組み合わせて、書き換え後のパスの構成規則を指定します。
				// - `{$dirname}` = 変換前のパスの、ディレクトリ部分
				// - `{$filename}` = 変換前のパスの、拡張子を除いたファイル名部分
				// - `{$ext}` = 変換前のパスの、拡張子部分
				//
				// または次のように、コールバックメソッド名を指定します。
				// > 'path_rewrite_rule'=>'functionNameOf::rewrite_smt',
				// コールバックメソッドには、 引数 `$path` が渡されます。
				// これを加工して、書き換え後のパスを返してください。
				'path_rewrite_rule' => '{$dirname}/{$filename}.smt.{$ext}',

				// このデバイス向けに出力するファイルのパス
				'paths_target' => array(
					'*.html',
				),

				// このデバイス向けには出力しないファイルのパス
				'paths_ignore'=>array(
					'/default_only/*',
				),

				// リンクの書き換え方向
				// `origin2origin`、`origin2rewrited`、`rewrited2origin`、`rewrited2rewrited` のいずれかで指定します。
				// `origin` は変換前のパス、 `rewrited` は変換後のパスを意味します。
				// 変換前のパスから変換後のパスへのリンクとして書き換える場合は `origin2rewrited` のように指定します。
				'rewrite_direction'=>'rewrited2rewrited',
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


## PX Commands

次の PX Command が登録されます。

- `PX=publish` : パブリッシュのホーム画面を表示します。
- `PX=publish.run` : パブリッシュを実行します。
- `PX=publish.version` : パブリッシュプラグインのバージョン番号を返します。


## 更新履歴 - Change log

### pickles2/px2-publish-ex v2.0.0 (リリース日未定)

- Initial release.


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
