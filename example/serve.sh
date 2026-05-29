#!/usr/bin/env bash
set -e

cd "$(dirname "$0")"

mkdir -p icons

php ../bin/iconify-php scan icons .

php -S localhost:8000 router.php
