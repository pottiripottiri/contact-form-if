# Contact Form If 開発環境

本ファイルはContact Form Ifの開発環境について記述しています。
Contact Form If の使用方法は[readme.txt](readme.txt)の方を御覧ください。

## 必要なソフト

* Visual Studio Code
* Docker Desktop
* composer 2

### 開発環境起動

```
docker-compose up -d
```
http://localhost:3000
でアクセスできます。
詳しくはdocker-compose.ymlを御覧ください。

### 開発準備

開発用のツールと、phpunitの準備を行います。
開発環境を起動した状態で下記コマンドを実行してください。
```
composer install
```

上記コマンド実行後、phpunitを実行可能になります。
```
composer phpunit
```
詳細はcomposer.jsonを御覧ください。

### Visual Studio Codeについて

以下のEXTENSIONを使っています。

* phpcs
* phpcbf

EXTENSIONを追加後、必要な設定を行ってください。

設定例
```
{
  "phpcs.executablePath": "./vendor/squizlabs/php_codesniffer/bin/phpcs",
  "phpcbf.executablePath": "./vendor/squizlabs/php_codesniffer/bin/phpcbf",
  "phpcs.standard": "WordPress", //phpcsにWordPressの規約を適用
  "editor.detectIndentation": false, //デフォルトのタブ設定を解除
  "editor.insertSpaces": false, //インデントをタブ文字にする
  "editor.tabSize": 4, // 【wordpress用】タブのサイズを４に変更
  "files.eol": "\n", //改行コードを変更
  "phpcbf.standard": "WordPress", //phpcdfにWordPressの規約を適用
  "phpcbf.onsave": true, //ファイルを保存したときにフォーマット
}
```
## その他のコマンド

コンテナの停止
```
docker-compose down
```

コンテナのコンソールに入る
```
docker-compose exec wp /bin/bash
```

ボリューム削除
DBのデータは永続化されてるのでボリューム削除しないとリセットされません。
```
docker volume rm contact-form-if_db
```

ボリューム名がわからなくなった場合は下記コマンドで確認可能です。
```
docker volume ls
```
