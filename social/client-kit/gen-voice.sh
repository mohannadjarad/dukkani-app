#!/usr/bin/env bash
# Legacy TTS helper — disabled by default so it cannot overwrite the
# recorded voiceover in voice/ar. The delivered recording is authoritative.
# Set ALLOW_LEGACY_TTS=1 only if you really want to regenerate TTS takes.
set -u
if [ "${ALLOW_LEGACY_TTS:-0}" != "1" ]; then
  echo "Legacy TTS helper disabled. Recorded voiceover in voice/ar is authoritative."
  echo "Set ALLOW_LEGACY_TTS=1 to override (not recommended)."
  exit 1
fi
TTS=/tmp/opencode/tts-venv/bin/edge-tts
PROXY=http://192.168.49.1:8282
DIR="$(cd "$(dirname "$0")" && pwd)"
VOICE="$DIR/voice"

gen() {
  local voice=$1 rate=$2 text=$3 out=$4 srt=$5
  local attempt
  for attempt in 1 2 3 4 5; do
    timeout 150 "$TTS" --proxy "$PROXY" --voice "$voice" --rate="$rate" \
      --text "$text" --write-media "$out" --write-subtitles "$srt" >/dev/null 2>&1
    if [ -s "$out" ]; then
      echo "ok $(basename "$out")"
      return 0
    fi
    echo "retry $attempt $(basename "$out")"
    sleep 2
  done
  echo "FAILED $(basename "$out")"
  return 1
}

mkdir -p "$VOICE/ar" "$VOICE/en"
nl=$'\n'
ar_lines=(); while IFS= read -r l; do ar_lines+=("$l"); done < "$VOICE/script-ar.txt"
en_lines=(); while IFS= read -r l; do en_lines+=("$l"); done < "$VOICE/script-en.txt"

for i in 1 2 3 4 5 6; do
  gen ar-JO-TaimNeural "+4%" "${ar_lines[$((i-1))]}" "$VOICE/ar/$i.mp3" "$VOICE/ar/$i.srt"
done
for i in 1 2 3 4 5 6; do
  gen en-US-AndrewNeural "+3%" "${en_lines[$((i-1))]}" "$VOICE/en/$i.mp3" "$VOICE/en/$i.srt"
done

echo "=== durations ==="
for lang in ar en; do
  echo "$lang"
  for i in 1 2 3 4 5 6; do
    d=$(ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 "$VOICE/$lang/$i.mp3" 2>/dev/null || echo 0)
    echo "  $i $d"
  done
done
