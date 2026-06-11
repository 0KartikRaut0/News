#!/usr/bin/env bash
# Link wp-content/themes and wp-content/plugins from this repo into a local WordPress site.
# Usage: ./scripts/link-to-wordpress.sh "/path/to/wordpress/wp-content"

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 /path/to/wordpress/wp-content"
  echo "Example: $0 \"\$HOME/Local Sites/my-site/app/public/wp-content\""
  exit 1
fi

WP_CONTENT="$1"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$REPO_ROOT/wp-content"

if [[ ! -d "$SRC/themes" || ! -d "$SRC/plugins" ]]; then
  echo "Error: expected $SRC/themes and $SRC/plugins"
  exit 1
fi

mkdir -p "$WP_CONTENT/themes" "$WP_CONTENT/plugins"

for dir in "$SRC/themes"/*; do
  [[ -d "$dir" ]] || continue
  name="$(basename "$dir")"
  ln -sfn "$dir" "$WP_CONTENT/themes/$name"
  echo "linked theme: $name"
done

for dir in "$SRC/plugins"/*; do
  [[ -d "$dir" ]] || continue
  name="$(basename "$dir")"
  ln -sfn "$dir" "$WP_CONTENT/plugins/$name"
  echo "linked plugin: $name"
done

echo "Done. Run git pull in $REPO_ROOT to update WordPress files."
