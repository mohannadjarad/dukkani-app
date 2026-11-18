/**
 * Build the Dukkani "studio" promo video from rendered frames.
 *
 *   node social/dukkani-promo/render.cjs   # render 990 PNG/JPG frames first
 *   node social/dukkani-promo/build.cjs    # encode + add the music bed
 *
 * Outputs (1080x1920, 30 fps, H.264):
 *   video/dukkani-promo.mp4         full cut with the "Inspired" music bed
 *   video/dukkani-promo-silent.mp4  same visuals, no audio track (for adding a
 *                                   trending sound inside Instagram / TikTok)
 *
 * Music: "Inspired" by Kevin MacLeod (incompetech.com),
 * licensed under Creative Commons: By Attribution 4.0.
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const DIR = __dirname;
const FRAMES = '/tmp/opencode/promo-frames';
const MUSIC = path.join(DIR, 'assets', 'music', 'inspired.mp3');
const VIDEO = path.join(DIR, 'video');
const SILENT = path.join(VIDEO, 'dukkani-promo-silent.mp4');
const OUT = path.join(VIDEO, 'dukkani-promo.mp4');

const FPS = 30;
const TOTAL = 33;
const FADE_IN = 1.2;
const FADE_OUT_START = TOTAL - 3;

fs.mkdirSync(VIDEO, { recursive: true });

// 1. Frames -> silent master (also the deliverable for trending audio).
console.log('encoding 1080x1920 master...');
execFileSync('ffmpeg', [
  '-y',
  '-framerate', String(FPS), '-i', path.join(FRAMES, '%05d.jpg'),
  '-c:v', 'libx264', '-preset', 'medium', '-crf', '18',
  '-pix_fmt', 'yuv420p', '-r', String(FPS),
  '-an', '-movflags', '+faststart',
  SILENT,
], { stdio: ['ignore', 'ignore', 'inherit'] });

// 2. Music bed on top of the master. Normalise the bed once, then fade.
console.log('adding the music bed...');
execFileSync('ffmpeg', [
  '-y',
  '-i', SILENT,
  '-i', MUSIC,
  '-filter_complex', [
    `[1:a]atrim=0:${TOTAL},asetpts=N/SR/TB,`,
    `loudnorm=I=-16:LRA=11:TP=-1.5,`,
    `afade=t=in:st=0:d=${FADE_IN},`,
    `afade=t=out:st=${FADE_OUT_START}:d=3,`,
    `aformat=sample_fmts=fltp:channel_layouts=stereo[aout]`,
  ].join(''),
  '-map', '0:v', '-map', '[aout]',
  '-c:v', 'copy', '-c:a', 'aac', '-b:a', '192k', '-ar', '44100',
  '-t', String(TOTAL), '-movflags', '+faststart',
  OUT,
], { stdio: ['ignore', 'ignore', 'inherit'] });

console.log(`\nwrote ${path.relative(process.cwd(), OUT)}`);
console.log(`wrote ${path.relative(process.cwd(), SILENT)}`);
