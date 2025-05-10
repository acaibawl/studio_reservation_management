#!/bin/bash

# 秘密鍵と、対になるサーバー証明書をまとめて作成
openssl req -x509 -nodes -days 365 \
  -newkey rsa:2048 \
  -keyout docker/nginx/certs/studio_reservation_management.local.key \
  -out docker/nginx/certs/studio_reservation_management.local.crt \
  -config docker/nginx/certs/openssl-san.cnf \
  -extensions req_ext
