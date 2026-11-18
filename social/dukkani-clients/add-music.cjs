/**
 * Add the royalty-free background track to the Dukkani "two clients" reels.
 *
 *   node social/dukkani-clients/add-music.cjs
 *
 * Outputs:
 *   video/dukkani-clients-reel-music.mp4         voice + ducked music
 *   video/dukkani-clients-reel-silent-music.mp4  music only
 *
 * The music is "Life of Riley" by Kevin MacLeod (incompetech.com),
 * licensed under Creative Commons: By Attribution 4.0.
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const DIR = __dirname;
const VIDEO = path.join(DIR, 'video');
const MUSIC = path.join(DIR, 'assets', 'music', 'life-of-riley.mp3');
const VOICED = path.join(VIDEO, 'dukkani-clients-reel.mp4');
const SILENT = path.join(VIDEO, 'dukkani-clients-reel-silent.mp4');
const OUT_VOICE = path.join(VIDEO, 'dukkani-clients-reel-music.mp4');
const OUT_MUSIC = path.join(VIDEO, 'dukkani-clients-reel-silent-music.mp4');

const t = JSON.parse(
  fs.readFileSync(path.join(DIR, 'assets', 'voice', 'timeline.json'), 'utf8')
);
const TOTAL = Number(t.total.toFixed(3));
const FADE_OUT_START = Math.max(0, TOTAL - 3).toFixed(3);

// 1. Voice + music: normalise the bed to -30 LUFS, duck it under the
//    narration, then limit peaks. No second loudnorm on the mix, so the
//    voice keeps the exact level it had in the voice-only cut (-16 LUFS).
execFileSync('ffmpeg', [
  '-y',
  '-i', VOICED,
  '-stream_loop', '-1', '-i', MUSIC,
  '-filter_complex', [
    `[1:a]atrim=0:${TOTAL},asetpts=N/SR/TB,`,
    `loudnorm=I=-30:LRA=7:TP=-9,`,
    `afade=t=in:st=0:d=1.2,afade=t=out:st=${FADE_OUT_START}:d=3[mus];`,
    `[0:a]asplit=2[voice][sc];`,
    `[mus][sc]sidechaincompress=threshold=0.03:ratio=8:attack=15:release=400[duck];`,
    `[voice][duck]amix=inputs=2:duration=first:normalize=0[mix];`,
    `[mix]alimiter=limit=0.89:level=disabled,`,
    `aformat=sample_fmts=fltp:channel_layouts=stereo[aout]`,
  ].join(''),
  '-map', '0:v', '-map', '[aout]',
  '-c:v', 'copy', '-c:a', 'aac', '-b:a', '192k', '-ar', '44100',
  '-t', String(TOTAL), '-movflags', '+faststart',
  OUT_VOICE,
], { stdio: 'inherit' });

// 2. Music only (for the silent cut).
execFileSync('ffmpeg', [
  '-y',
  '-i', SILENT,
  '-stream_loop', '-1', '-i', MUSIC,
  '-filter_complex', [
    `[1:a]atrim=0:${TOTAL},asetpts=N/SR/TB,volume=0.8,`,
    `afade=t=in:st=0:d=1.2,afade=t=out:st=${FADE_OUT_START}:d=3,`,
    `loudnorm=I=-16:LRA=11:TP=-1.5[aout]`,
  ].join(''),
  '-map', '0:v', '-map', '[aout]',
  '-c:v', 'copy', '-c:a', 'aac', '-b:a', '192k', '-ar', '44100',
  '-t', String(TOTAL), '-movflags', '+faststart',
  OUT_MUSIC,
], { stdio: 'inherit' });

console.log(`\nwrote ${path.relative(process.cwd(), OUT_VOICE)}`);
console.log(`wrote ${path.relative(process.cwd(), OUT_MUSIC)}`);
