/**
 * Build the Dukkani "two clients" reels from rendered frames + voiceover.
 *
 *   node social/dukkani-clients/build-video.cjs
 *
 * Outputs:
 *   video/dukkani-clients-reel.mp4        1080x1920, voice + captions baked in
 *   video/dukkani-clients-reel-silent.mp4 same visuals, no audio (add trending
 *                                         sound inside Instagram / TikTok)
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const DIR = __dirname;
const VOICE = path.join(DIR, 'assets', 'voice');
const FRAMES = '/tmp/opencode/clients-frames';
const CLIPS = '/tmp/opencode/clients-clips';
const OUT = path.join(DIR, 'video', 'dukkani-clients-reel.mp4');
const OUT_SILENT = path.join(DIR, 'video', 'dukkani-clients-reel-silent.mp4');
const FPS = 30;
const XFADE = 0.5;

const t = JSON.parse(fs.readFileSync(path.join(VOICE, 'timeline.json'), 'utf8'));
const durs = [1, 2, 3, 4, 5, 6].map((i) => t.duration[i]);
const lead = t.lead;

const starts = [];
let acc = 0;
for (let i = 0; i < 6; i++) {
  starts.push(acc);
  acc += durs[i] - XFADE;
}
const total = starts[5] + durs[5];

// 1. Encode each scene's frames to a clip.
fs.rmSync(CLIPS, { recursive: true, force: true });
fs.mkdirSync(CLIPS, { recursive: true });
for (let i = 1; i <= 6; i++) {
  console.log(`encoding scene ${i}...`);
  execFileSync('ffmpeg', [
    '-y', '-framerate', String(FPS),
    '-i', path.join(FRAMES, `scene-${i}`, '%05d.jpg'),
    '-c:v', 'libx264', '-preset', 'medium', '-crf', '18',
    '-pix_fmt', 'yuv420p', '-r', String(FPS),
    path.join(CLIPS, `clip-${i}.mp4`),
  ], { stdio: ['ignore', 'ignore', 'ignore'] });
}

// 2. Crossfade the six clips into one video stream.
const videoInputs = [];
for (let i = 1; i <= 6; i++) videoInputs.push('-i', path.join(CLIPS, `clip-${i}.mp4`));

let videoFc = '';
let prev = '[0:v]';
for (let i = 1; i <= 5; i++) {
  const next = `[v${i}]`;
  videoFc += `${prev}[${i}:v]xfade=transition=fade:duration=${XFADE}:offset=${starts[i].toFixed(3)}${next};`;
  prev = next;
}

const voiceFile = (i) => {
  const a = path.join(VOICE, `scene-${i}.mp3`);
  return fs.existsSync(a) ? a : path.join(VOICE, `${i}.mp3`);
};

// 3. Voiced reel: delay + mix the six lines, normalise loudness.
const audioInputs = [];
for (let i = 1; i <= 6; i++) audioInputs.push('-i', voiceFile(i));

let audioFc = videoFc;
const delays = starts.map((s, i) => {
  const ms = Math.round((s + lead) * 1000);
  console.log(`voice ${i + 1} starts at ${(ms / 1000).toFixed(3)}s`);
  return ms;
});
for (let i = 0; i < 6; i++) {
  audioFc += `[${i + 6}:a]adelay=${delays[i]}:all=1[a${i}];`;
}
audioFc += '[a0][a1][a2][a3][a4][a5]amix=inputs=6:duration=longest:normalize=0[araw];';
audioFc += '[araw]loudnorm=I=-16:LRA=11:TP=-1.5[aout]';

execFileSync('ffmpeg', [
  '-y', ...videoInputs, ...audioInputs,
  '-filter_complex', audioFc,
  '-map', prev, '-map', '[aout]',
  '-c:v', 'libx264', '-preset', 'medium', '-crf', '18', '-pix_fmt', 'yuv420p',
  '-c:a', 'aac', '-b:a', '192k', '-ar', '44100',
  '-t', total.toFixed(3), '-movflags', '+faststart',
  OUT,
], { stdio: 'inherit' });

// 4. Silent reel (same visuals) for trending audio in-app.
execFileSync('ffmpeg', [
  '-y', ...videoInputs,
  '-filter_complex', videoFc,
  '-map', prev,
  '-c:v', 'libx264', '-preset', 'medium', '-crf', '19', '-pix_fmt', 'yuv420p',
  '-an', '-t', total.toFixed(3), '-movflags', '+faststart',
  OUT_SILENT,
], { stdio: 'inherit' });

console.log(`\nwrote ${path.relative(process.cwd(), OUT)} (${total.toFixed(2)}s)`);
console.log(`wrote ${path.relative(process.cwd(), OUT_SILENT)} (${total.toFixed(2)}s)`);
