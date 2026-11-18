#!/usr/bin/env bash
# Generate the Arabic voiceover for the Dukkani "two clients" reel.
#
#   social/dukkani-clients/make-voice.sh
#
# Needs `edge-tts` (pip install edge-tts) and the local proxy that the
# sandbox uses to reach speech.platform.bing.com.
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"
VOICE="${VOICE:-ar-JO-SanaNeural}"
RATE="${RATE:--4%}"
PROXY="${PROXY:-}"
EDGE_TTS="${EDGE_TTS:-/tmp/opencode/tts-venv/bin/edge-tts}"

while IFS='|' read -r n text; do
  [ -z "${n:-}" ] && continue
  out="$DIR/assets/voice/$n.mp3"
  echo "→ line $n"
  if [ -n "$PROXY" ]; then
    "$EDGE_TTS" --proxy "$PROXY" --voice "$VOICE" --rate "$RATE" \
      --text "$text" --write-media "$out" --no-progress
  else
    "$EDGE_TTS" --voice "$VOICE" --rate "$RATE" \
      --text "$text" --write-media "$out" --no-progress
  fi
done < "$DIR/assets/voice/script-ar.txt"

echo "voice ready"
