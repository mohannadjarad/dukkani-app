/**
 * Build the "3 signs" reel from rendered frames + the recorded Arabic voice.
 *
 *   node social/client-kit/build-reel.cjs
 *
 * Output: video/dukkani-3signs-ar.mp4 (1080x1920, 30 fps, H.264 + AAC).
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const DIR = __dirname;
const VOICE = path.join(DIR, 'voice', 'ar');
const FRAMES = '/tmp/opencode/client-reel-frames';
const CLIPS = '/tmp/opencode/client-reel-clips';
const OUT = path.join(DIR, 'video', 'dukkani-3signs-ar.mp4');
const FPS = 30;
const XFADE = 0.5;

const t = JSON.parse(fs.readFileSync(path.join(VOICE, 'timeline.json'), 'utf8'));
const durs = [1, 2, 3, 4, 5, 6].map((i) => t.duration[i]);

const starts = [];
let acc = 0;
for (let i = 0; i < 6; i++) {
  starts.push(acc);
  acc += durs[i] - XFADE;
}
const total = starts[5] + durs[5];

fs.mkdirSync(path.dirname(OUT), { recursive: true });
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

const inputs = [];
for (let i = 1; i <= 6; i++) inputs.push('-i', path.join(CLIPS, `clip-${i}.mp4`));
for (let i = 1; i <= 6; i++) inputs.push('-i', path.join(VOICE, `${i}.mp3`));

let fc = '';
let prev = '[0:v]';
for (let i = 1; i <= 5; i++) {
  const next = `[v${i}]`;
  fc += `${prev}[${i}:v]xfade=transition=fade:duration=${XFADE}:offset=${starts[i].toFixed(3)}${next};`;
  prev = next;
}

const delays = starts.map((s, i) => {
  const ms = Math.round((s + t.lead) * 1000);
  console.log(`voice ${i + 1} starts at ${(ms / 1000).toFixed(3)}s`);
  return ms;
});
for (let i = 0; i < 6; i++) {
  fc += `[${i + 6}:a]adelay=${delays[i]}:all=1[a${i}];`;
}
fc += '[a0][a1][a2][a3][a4][a5]amix=inputs=6:duration=longest:normalize=0[araw];';
fc += '[araw]loudnorm=I=-16:LRA=11:TP=-1.5[aout]';

execFileSync('ffmpeg', [
  '-y', ...inputs,
  '-filter_complex', fc,
  '-map', prev, '-map', '[aout]',
  '-c:v', 'libx264', '-preset', 'medium', '-crf', '18', '-pix_fmt', 'yuv420p',
  '-c:a', 'aac', '-b:a', '192k', '-ar', '44100',
  '-t', total.toFixed(3), '-movflags', '+faststart',
  OUT,
], { stdio: 'inherit' });

console.log(`\nwrote video/${path.basename(OUT)} (${total.toFixed(2)}s)`);
