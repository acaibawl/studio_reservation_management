# ローカル環境をドメイン指定でアクセス

## /etc/hostsに下記を追加

```
127.0.0.1 front.local back.local
```

## サーバー証明書をOSで読み込んで信頼する。

下記ファイルをfinderでダブルクリックで開く
docker/nginx/certs/studio_reservation_management.local.crt

キーチェーンアクセスに登録されるので、「システムデフォルトを使用」から「常に信頼」に変更する。

![キーチェーンアクセス](docs/keychain.png)

ブラウザを立ち上げ直す

# laravelの.envファイル用意

backend/.env.example をコピーして.envファイルを作成  
APP_KEY変数の生成

```
php artisan key:generate
```