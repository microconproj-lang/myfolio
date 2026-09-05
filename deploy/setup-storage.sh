#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
UPLOADS="${PROJECT_ROOT}/storage/uploads"

if [[ "$(id -u)" -ne 0 ]]; then
    echo "Run this script with sudo: sudo bash deploy/setup-storage.sh" >&2
    exit 1
fi

install -d -o www-data -g www-data -m 0750 "${UPLOADS}"
find "${UPLOADS}" -maxdepth 1 -type f -exec chown www-data:www-data {} + -exec chmod 0640 {} +

echo "Private upload storage is ready: ${UPLOADS}"