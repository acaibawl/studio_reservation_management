# Node.js 公式イメージを使用
FROM node:22.9.0-alpine3.19

# 作業ディレクトリを設定
WORKDIR /app

# 依存関係をコピーしてインストール
# COPY app/package.json app/package-lock.json ./
# RUN npm install

# 必要なパッケージのインストール
RUN apk update \
    && apk add --no-cache bash

RUN apk add --no-cache ca-certificates
COPY studio_reservation_management.local.crt /usr/local/share/ca-certificates/studio_reservation_management.local.crt
RUN update-ca-certificates

# ポート3000を開放
EXPOSE 3000

# Nuxtアプリを起動
CMD ["npm", "run", "dev"]
# CMD ["tail", "-f", "/dev/null"]