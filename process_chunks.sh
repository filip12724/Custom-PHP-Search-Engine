set -u -o pipefail  

WORKERS=32
TIMEOUT=30

for chunk in urls_chunk_*; do
  echo "→ Processing $chunk ($(date))"

  # run the pipeline but never let it kill the script
  if ! xargs -a "$chunk" -n1 -P"$WORKERS" --no-run-if-empty \
         bash -c '
           timeout '"$TIMEOUT"' php crawler.php "$1" 2>&1 \
             | ts "[%Y-%m-%d %H:%M:%S]"
         ' _ \
       | tee -a "${chunk}.log"
  then
    echo "⚠ Warning: some URLs in $chunk failed (see ${chunk}.log), continuing…" >&2
  fi

  # kill any stray PHP crawlers belonging to us
  if children=$(pgrep -P $$ php); then
    kill $children || true
  fi

  echo "→ Finished $chunk ($(date))"
  sleep 1
done

echo "All chunks processed."
