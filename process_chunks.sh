set -u -o pipefail

WORKERS=32


for chunk in urls_chunk_*; do
  if [[ $(tail -c1 "$chunk") != $'\n' ]]; then
    echo >> "$chunk"
  fi
done


for chunk in urls_chunk_*; do
  echo "→ processing $chunk ($(date))"
  if ! xargs -a "$chunk" -n1 -P"$WORKERS" php crawler.php; then
    echo "⚠️ some URLs in $chunk failed, moving on…"
  fi
  echo "→ finished $chunk ($(date))"
done

echo "All chunks done."
