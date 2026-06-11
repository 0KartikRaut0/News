#!/usr/bin/env bash
# Copy wp-content/themes and wp-content/plugins into a local WordPress site (no symlinks).
# Usage: ./scripts/sync-to-wordpress.sh "/path/to/wordpress/wp-content"

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 /path/to/wordpress/wp-content"
  exit 1
fi

WP_CONTENT="$1"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$REPO_ROOT/wp-content"

rsync -a --delete "$SRC/themes/" "$WP_CONTENT/themes/"
rsync -a --delete "$SRC/plugins/" "$WP_CONTENT/plugins/"

echo "Synced themes and plugins to $WP_CONTENT"
