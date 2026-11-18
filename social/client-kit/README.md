# Dukkani — Client Kit · حملة «٣ علامات»

حزمة جاهزة للنشر: بوستان للفيد + ريل عربي بصوت مسجّل. الزاوية تعليمية
(تشخيص) وليست نسخة من كيت `social/dukkani-post/` القديم.

## الملفات النهائية

| الأصل | الملف | المقاس | الملاحظات |
|---|---|---|---|
| البوست التعليمي — إنستغرام | `post/post-education-ig.png` | 1080×1350 (4:5) | ٣ علامات + الحل + CTA |
| البوست التعليمي — فيسبوك | `post/post-education-fb.png` | 1200×630 (1.91:1) | نفس المحتوى بتخطيط أفقي |
| دراسة الحالة — إنستغرام | `post/post-case-ig.png` | 1080×1350 (4:5) | ٣ واجهات من نفس النظام |
| دراسة الحالة — فيسبوك | `post/post-case-fb.png` | 1200×630 (1.91:1) | نفس المحتوى بتخطيط أفقي |
| الريل العربي | `video/dukkani-3signs-ar.mp4` | 1080×1920 (9:16) | 41 ثانية · 30fps · H.264/AAC |
| غلاف الريل | `video/cover/cover-1080x1920.png` | 1080×1920 | مأخوذ من المشهد الأول |
| الكابشن وخطة النشر | `captions.md` | — | عربي + إنجليزي + Checklist |

## الترتيب المقترح

1. **Day 1:** البوست التعليمي (قيمة) + ستوري استفتاء — نسخة IG 4:5 ونسخة FB 1.91:1.
2. **Day 2:** الريل (نفس الخطاف بصيغة قصيرة) + ستوري رابط الديمو.
3. **Day 4:** بوست دراسة الحالة (دليل) + ستوري CTA — نفس نسختي المنصّتين.
4. **Day 6 (اختياري):** جولة سريعة بالديمو في ستوري.

لا تنشر البوستين في نفس اليوم. التفاصيل الكاملة في `captions.md`.

## الهوية والرسالة

- **الكلمة المفتاحية:** «تشخيص» — وليس «متجر» أو «SaaS».
- **واتساب:** +972 56 999 1081
- **الديمو:** https://dukkani-demo.onrender.com
- **رابط واتساب جاهز:** `https://wa.me/972569991081?text=%D8%AA%D8%B4%D8%AE%D9%8A%D8%B5`
- الخط: Tajawal · الألوان: كريمي/تراكوتا/أخضر · كل النصوص عربية RTL.
- لا أسعار ولا أرقام مخترعة. بيانات الشاشات من ديمو «مخبز أمل بيكنز» الحقيقي.

## صوت الريل

- التسجيل: `voice/ar/1.mp3` … `6.mp3` (٦ أسطر، ٣٤٫٨٨ ثانية إجمالاً).
- النص: `voice/script-ar.txt` (والملفات المسجّلة هي المرجع).
- `voice/en/` يحتوي محاولات قديمة غير مكتملة — لا تستخدمه كما هو.
- `gen-voice.sh` مساعد TTS قديم ومعطّل افتراضياً حتى لا يستبدل التسجيل.
- التوقيت: `voice/ar/timeline.json` — بدايات الصوت:
  `0.35 / 4.81 / 9.59 / 16.85 / 23.07 / 32.01` ثانية.
- الريل بلا موسيقى؛ الصوت الأصلي فقط.

## إعادة البناء

المتطلبات: Node.js، Playwright + Chromium، و`ffmpeg`/`ffprobe`.

```bash
cd /home/mohannad/Projects/dukkani

# Playwright موجود في npx cache على هذا الجهاز:
export NODE_PATH=/home/mohannad/.npm/_npx/fd3bca3c548369c0/node_modules

# ١) البوستات
node social/client-kit/render-posts.cjs

# ٢) الريل: يقرأ voice/ar/*.mp3، يكتب timeline.json، ويصوّر الفريمات
node social/client-kit/render-reel.cjs

# ٣) تجميع الفيديو
node social/client-kit/build-reel.cjs

# ٤) الغلاف (٣٫٢ ثانية داخل المشهد الأول)
ffmpeg -y -ss 3.2 -i social/client-kit/video/dukkani-3signs-ar.mp4 \
  -frames:v 1 social/client-kit/video/cover/cover-1080x1920.png
```

`render-reel.cjs` يكتب الفريمات إلى `/tmp/opencode/client-reel-frames`،
و`build-reel.cjs` يقرأ منها. إذا غيّرت التسجيل، أعد الخطوتين ٢ و٣.

### إذا تغيّر رقم الواتساب

الرقم مكتوب داخل `src/post-education.html`، `src/post-education-fb.html`،
`src/post-case.html`، `src/post-case-fb.html`، و`src/reel.html`.
عدّله في الخمسة ثم أعد الرندر كاملاً.

## النسخة الإنجليزية

- نص صوت الريل الإنجليزي جاهز في `voice/script-en.txt`.
- لا يوجد تسجيل إنجليزي بعد، لذلك لا يوجد ريل إنجليزي.
- بعد تسجيل `voice/en/1.mp3` … `6.mp3`، غيّر ثابت `VOICE` في
  `render-reel.cjs` و`build-reel.cjs` من `voice/ar` إلى `voice/en`، ثم أعد
  الخطوتين ٢ و٣. الكابشن الإنجليزي جاهز في `captions.md`.

## خريطة المصادر

```
social/client-kit/
├─ src/post-education.html     ← نسخة إنستغرام (1080×1350)
├─ src/post-education-fb.html  ← نسخة فيسبوك (1200×630)
├─ src/post-case.html          ← نسخة إنستغرام (1080×1350)
├─ src/post-case-fb.html       ← نسخة فيسبوك (1200×630)
├─ src/reel.html               ← ٦ مشاهد متحركة (1080×1920)
├─ render-posts.cjs            ← يصوّر البوستات الأربعة
├─ render-reel.cjs             ← يصوّر فريمات الريل + timeline.json
├─ build-reel.cjs              ← يبني MP4 بالصوت
├─ post/                       ← PNG النهائي
├─ video/                      ← MP4 + الغلاف
└─ voice/                      ← النصوص والتسجيلات
```

## ملاحظات تسليم

- «خلال ٢٤ ساعة» وعد خدمة؛ لا تنشره إذا ما كنت قادر تلتزم فيه.
- النموذج ديمو حقيقي وليس متجر عميل مدفوع — لا تصف «أمل بيكنز» كعميل.
- الريل يحتوي نصاً على الشاشة؛ لا حاجة لترجمة إضافية، لكن يمكنك إضافة
  كابشن تلقائي على إنستغرام إذا أردت.
- الكيت القديم في `social/dukkani-post/` غير مستخدم في هذه الحملة.
