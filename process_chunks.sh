#!/usr/bin/env bash
set -euo pipefail
# process_chunks.sh — run crawler.php in parallel over each 10k‑line chunk

# Number of PHP processes per chunk
WORKERS=32

for chunk in urls_chunk_*; do
  echo "→ processing $chunk ($(date))"
  # feed each URL in $chunk into php crawler.php
  #   -a  read args from file
  #   -L1 one URL per command
  #   -P$WORKERS up to $WORKERS processes in parallel
  xargs -a "$chunk" -L1 -P"$WORKERS" php crawler.php
  echo "→ finished $chunk ($(date))"
done

echo "All chunks done."
