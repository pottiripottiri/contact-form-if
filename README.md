# Contact Form If

Wordpressのプラグイン：Contact Form 7において、  
必須チェックなどに条件分岐を付与するプラグインです。  

## Environment(動作環境)

* WordPress5.8以上
* PHP7.0以上

**Contact Form 7がインストールしてある必要があります。**  

## Usage（使い方)
 
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
 
- フォーム
```
<label> 項目1
    [text column-1] </label>
<label> 項目2
    [text column-2] </label>
<label> 項目3
    [text column-3] </label>
```
- その他の設定
```
requireif-column-2: column-1,eq,1
requireif-column-3: column-1,eq,2
```
この設定で、項目2は、column-1が1の時だけ(完全一致)必須チェックの対象になります。  
項目3は、column-1が2の時だけ必須チェックの対象になります。

## Example

### 空白
- text-1が空白の時のみ必須
```
requireif-text-2: text-1,is_null,
```
- text-1が空白でない時のみ必須
```
requireif-text-2: text-1,not_null,
```

### 完全一致
- text-1が1の時のみ必須
```
requireif-text-2: text-1,equal,1
```
- text-1が1でない時のみ必須
```
requireif-text-2: text-1,not_equal,1
```

### より大きい、以上
- text-1が1より大きい時のみ必須
```
requireif-text-2: text-1,greater_than,1
```
- text-1が1以上の時のみ必須
```
requireif-text-2: text-1,greater_qual,1
```

### 未満、以下
- text-1が2未満の時のみ必須
```
requireif-text-2: text-1,less_than,2
```
- text-1が2以下の時のみ必須
```
requireif-text-2: text-1,less_equal,2
```
### いずれか、いずれでもない
- text-1が1〜3のいずれかの時のみ必須
```
requireif-text-2: text-1,in,1 2 3
```
- text-1が1〜3のどれでもない時のみ必須
```
requireif-text-2: text-1,not_in,1 2 3
```