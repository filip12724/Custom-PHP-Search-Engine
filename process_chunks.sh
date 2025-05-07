set -euo pipefail

WORKERS=32

for chunk in urls_chunk_*; do
  echo "→ processing $chunk ($(date))"
  xargs -a "$chunk" -L1 -P"$WORKERS" php crawler.php
  echo "→ finished $chunk ($(date))"
done

echo "All chunks done."
