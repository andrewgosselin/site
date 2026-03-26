#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8080}"
ROOT_DIR="${NIXPACKS_PHP_ROOT_DIR:-/app/public}"
FALLBACK_PATH="${NIXPACKS_PHP_FALLBACK_PATH:-/index.php}"

cat >/etc/nginx.conf <<EOF
user www-data www-data;
worker_processes auto;
daemon off;

worker_rlimit_nofile 8192;

events {
  worker_connections 4096;
}

http {
    include /etc/nginx/mime.types;
    index index.html index.htm index.php;

    default_type application/octet-stream;
    log_format main '\$remote_addr - \$remote_user [\$time_local]  \$status '
        '"\$request" \$body_bytes_sent "\$http_referer" '
        '"\$http_user_agent" "\$http_x_forwarded_for"';
    access_log /var/log/nginx-access.log;
    error_log /var/log/nginx-error.log;
    sendfile on;
    tcp_nopush on;
    server_names_hash_bucket_size 128;

    server {
        listen ${PORT};
        listen [::]:${PORT};
        server_name localhost;
        root ${ROOT_DIR};

        add_header X-Content-Type-Options "nosniff";
        client_max_body_size 35M;

        index index.php;
        charset utf-8;

        location / {
            try_files \$uri \$uri/ ${FALLBACK_PATH}?\$query_string;
        }

        location ~ ^/(assets|fonts)/ {
            expires 1y;
            add_header Cache-Control "public, no-transform";
            access_log off;
        }

        location ~ ^/build/.*\.mjs$ {
            default_type application/javascript;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_buffer_size 8k;
            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
            include /etc/nginx/fastcgi_params;
            include /etc/nginx/fastcgi.conf;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
EOF

cd /app
php artisan cache:clear || true
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

exec supervisord -c /etc/supervisord.conf -n
