#!/usr/bin/env bash
# Link wp-content/themes and wp-content/plugins from this repo into a Local WordPress site.
#
# Usage:
#   ./scripts/link-to-wordpress.sh "/Users/kartik/Local Sites/swarajya-shikshan/app/public/wp-content"

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 /path/to/wordpress/wp-content"
  echo "Example: $0 \"\$HOME/Local Sites/swarajya-shikshan/app/public/wp-content\""
  exit 1
fi

WP_CONTENT="$1"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$REPO_ROOT/wp-content"

if [[ ! -d "$SRC/themes" || ! -d "$SRC/plugins" ]]; then
  echo "Error: expected $SRC/themes and $SRC/plugins"
  echo "Run git pull in the repo root first."
  exit 1
fi

mkdir -p "$WP_CONTENT/themes" "$WP_CONTENT/plugins"

echo "Repo: $REPO_ROOT"
echo "WordPress wp-content: $WP_CONTENT"
echo ""

for dir in "$SRC/themes"/*; do
  [[ -d "$dir" ]] || continue
  name="$(basename "$dir")"
  target="$WP_CONTENT/themes/$name"
  if [[ -e "$target" && ! -L "$target" ]]; then
    rm -rf "$target"
  fi
  ln -sfn "$dir" "$target"
  echo "linked theme: $name"
done

for dir in "$SRC/plugins"/*; do
  [[ -d "$dir" ]] || continue
  name="$(basename "$dir")"
  target="$WP_CONTENT/plugins/$name"
  if [[ -e "$target" && ! -L "$target" ]]; then
    rm -rf "$target"
  fi
  ln -sfn "$dir" "$target"
  echo "linked plugin: $name"
done

echo ""
echo "Done. After git pull in $REPO_ROOT, refresh your browser to see changes."
