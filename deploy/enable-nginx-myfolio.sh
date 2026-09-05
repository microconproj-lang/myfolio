#!/usr/bin/env bash

set -Eeuo pipefail

CONFIG="/etc/nginx/sites-available/scilab-equipment"
SNIPPET="/var/www/html/portal/myfolio/deploy/nginx-myfolio.conf"
if [[ ! -f "${SNIPPET}" ]]; then
    SNIPPET="/var/www/html/portal/myfolio/deploy/nginx-myfolio.conf.example"
fi
PHPMyAdmin_SNIPPET="/var/www/html/portal/myfolio/deploy/nginx-phpmyadmin.conf.example"
BACKUP="${CONFIG}.bak.myfolio.$(date +%Y%m%d%H%M%S)"

if [[ "$(id -u)" -ne 0 ]]; then
    echo "Run this script with sudo: sudo bash deploy/enable-nginx-myfolio.sh" >&2
    exit 1
fi

[[ -f "${CONFIG}" ]] || { echo "Nginx site config not found: ${CONFIG}" >&2; exit 1; }
[[ -f "${SNIPPET}" ]] || { echo "MyFolio snippet not found: ${SNIPPET}" >&2; exit 1; }
[[ -f "${PHPMyAdmin_SNIPPET}" ]] || { echo "phpMyAdmin snippet not found: ${PHPMyAdmin_SNIPPET}" >&2; exit 1; }

MYFOLIO_INCLUDE="    include ${SNIPPET};"
PHPMYADMIN_INCLUDE="    include ${PHPMyAdmin_SNIPPET};"

if grep -Fqx "${MYFOLIO_INCLUDE}" "${CONFIG}" && grep -Fqx "${PHPMYADMIN_INCLUDE}" "${CONFIG}"; then
    echo "MyFolio and phpMyAdmin includes are already present."
else
    cp -a "${CONFIG}" "${BACKUP}"
    python3 - "${CONFIG}" "${MYFOLIO_INCLUDE}" "${PHPMYADMIN_INCLUDE}" <<'PY'
import sys

path, *include_lines = sys.argv[1:]
lines = open(path, encoding="utf-8").readlines()
insert_at = None

index = 0
while index < len(lines):
    if not lines[index].lstrip().startswith("server {"):
        index += 1
        continue

    start = index
    depth = 0
    while index < len(lines):
        depth += lines[index].count("{") - lines[index].count("}")
        index += 1
        if depth == 0:
            break

    block = lines[start:index]
    if any("listen 443" in line for line in block) and any("server_name portal.pccpl.ac.th" in line for line in block):
        for offset, line in enumerate(block):
            if "server_name portal.pccpl.ac.th" in line:
                insert_at = start + offset + 1
                break
        break

if insert_at is None:
    raise SystemExit("HTTPS server block for portal.pccpl.ac.th was not found")

missing_lines = [line for line in include_lines if not any(existing.rstrip("\n") == line for existing in lines)]
lines[insert_at:insert_at] = [line + "\n" for line in missing_lines]
open(path, "w", encoding="utf-8").writelines(lines)
PY
fi

if ! nginx -t; then
    cp -a "${BACKUP}" "${CONFIG}"
    echo "Nginx validation failed; restored ${CONFIG} from backup." >&2
    exit 1
fi

systemctl reload nginx
echo "MyFolio Nginx routing enabled."