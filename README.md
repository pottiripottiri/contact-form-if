# Contact Form If(β)

Wordpressのプラグイン：Contact Form 7において、  
必須チェックなどに条件分岐を付与するプラグインです。  
2021/08/15時点開発中

##　動作環境

2021/08/15時点

* WordPress5.8
* PHP7.0

**Contact Form 7がインストールしてある必要があります。**  

##　使用方法

2021/08/15時点  
1. 本GitHubページから落としたソースを、  
WordPressのwp-content/pluginsにおいてください。  
```
wordpress/wp-content/plugins
└── contact-form-if
    ├── README.md
    ├── contact-form-if.php
    └── includes
        └── class-wpcfif.php
```
2. Wordpressの管理画面で、Contact Form Ifを有効化してください。

3. コンタクトフォームに設定を記述します。
例）  
フォーム
```
<label> 項目1
    [text column-1] </label>

<label> 項目2
    [text column-2] </label>

<label> 項目3
    [text column-3] </label>
```

その他の設定
```
requireif-column-2: column-1,eq,1
requireif-column-3: column-1,eq,2
```
この設定で、項目2は、column-1が1の時だけ必須チェックの対象になります。  
項目3は、column-1が2の時だけ必須チェックの対象になります。