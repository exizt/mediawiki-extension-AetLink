# AetLink

Links
* Git : https://github.com/exizt/mw-ext-AetLink


## Requirements
- PHP 7.4.3 or later
- MediaWiki 1.35 or later


## Installation
1. Download and place the files in a directory called `AetLink` in your `extensions/` folder.
2. Add the following code at the bottom of your `LocalSettings.php`:
```
wfLoadExtension( 'AetLink' );
```


## Configuration

- `$wgAetLink['disable_external_link']`
    - 외부 링크 external link를 비활성화하는 옵션.
        - type : `bool`
        - default : `false`
- `$wgAetLink['interwiki_link_target']`
    - 인터위키 타겟을 지정할 수 있는 옵션. 주로 `'_blank'`로 설정할 수 있다.
        - type: `string`
        - default : `''`