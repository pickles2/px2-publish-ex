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

次のコマンドラインオプションが、 `PX=publish.run` と合わせて使用できます。

#### `path_region`
対象範囲とするディレクトリパスを1つ指定します。省略時はカレントディレクトリが対象になります。
`/?PX=publish.run&path_region=/a/b/` と `/a/b/?PX=publish.run` は同じ意味です。

#### `paths_region`
対象範囲を追加指定します。配列で複数指定可能です。

#### `paths_ignore`
`path_region` で指定した対象範囲のうち、パブリッシュを除外するパスを指定します。複数指定可能です。

#### `keep_cache`
1を指定し、パブリッシュ処理の初期化時に、キャッシュの削除および再生成をスキップします。

#### 実行例
オプションを設定した実行例を示します。

```
$ php .px_execute.php "/?PX=publish.run&path_region=/a/b/&paths_region[]=/a/c/&paths_region[]=/a/d/&paths_ignore[]=/a/b/ignore1/&paths_ignore[]=/a/b/ignore2/&keep_cache=1"
```

この例では、対象範囲を `/a/b/` に絞った上で、 `/a/b/ignore1/` と `/a/b/ignore2/` を対象外に指定しています。


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
		// (この挙動を変更したい場合は、次の `skip_default_device` に true を設定します)
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
		),

		// 標準デバイスを出力しない (default to `false`)
		// `true` を設定すると、標準デバイスでのパブリッシュはされなくなります。
		'skip_default_device' => false,
	)).')' ,
);
```


## PX Commands

次の PX Command が登録されます。

- `PX=publish` : パブリッシュのホーム画面を表示します。
- `PX=publish.run` : パブリッシュを実行します。
- `PX=publish.version` : パブリッシュプラグインのバージョン番号を返します。


## 更新履歴 - Change log

### pickles2/px2-publish-ex v2.0.4 (2019年9月4日)

- パブリッシュが2重に起動することがある問題を修正。
- PHP 7.3 系で発生する不具合を修正。

### pickles2/px2-publish-ex v2.0.3 (2019年6月8日)

- `path_region`、`paths_region`、`paths_ignore` で、各行の先頭にスラッシュを補完するようになった。
- CSSが参照するファイル名に `)` 記号を含められない不具合を修正。

### pickles2/px2-publish-ex v2.0.2 (2019年4月19日)

- パス変換時に、もとの文字セットが無視されて UTF-8 に変換されてしまう不具合を修正。

### pickles2/px2-publish-ex v2.0.1 (2019年2月4日)

- Windowsで、パス変換時に相対パスの階層がズレることがある不具合を修正。

### pickles2/px2-publish-ex v2.0.0 (2018年8月18日)

- Initial release.


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
