# Dukkani — Validation Kit

**Purpose:** prove that home bakers will pay before you write production code.  
**Time required:** 2 weeks, ~10–15 hours.  
**Success:** 3 paying pilot sellers (or 3 signed commitments).

---

## 1. What you are testing

You are not testing "do people like the idea?" You are testing:

1. **Frequency** — does the pain happen weekly?
2. **Cost** — does it cost money, orders, or reputation?
3. **Workaround** — are they already using a notebook, Excel, or chat search?
4. **Willingness to pay** — will they pay 15–25 JOD/month after a pilot?
5. **Reachability** — can you find 20 more sellers through the same channel?

If the answer to 3, 4, and 5 is yes, build. If not, change the niche or the offer.

---

## 2. Recruiting

### Where to find 15 home bakers

- Facebook groups for home businesses, bakers, and women entrepreneurs in Jordan.
- Instagram hashtags: `#حلويات_منزلية #كيك_عمان #مشاريع_منزلية #صانعة_منزل`
- WhatsApp communities for home businesses.
- Baking workshops and supply shops.
- Friends/family referrals: ask "who do you know who sells cakes from home?"

### Outreach message (Arabic)

> مرحبًا [الاسم]،  
> أنا مهند، أطوّر أداة بسيطة تساعد مشاريع الحلويات المنزلية على تنظيم الطلبات والعربون بدل ما تضيع في رسائل الواتساب.  
> ما بحاجة منك إلا 15 دقيقة من وقتك، وبالمقابل بجهّز لك متجر إلكتروني بسيط مجانًا إذا حبيتِ.  
> ممكن نحكي اليوم أو بكرة؟

### Outreach message (English)

> Hi [Name], I'm building a simple tool that helps home bakers organise orders and deposits instead of losing them in WhatsApp chats. Could I ask you 15 minutes of questions? In return, I'll set up a free storefront for you if you're interested.

### Follow-up

> تذكير لطيف: إذا عندك 10 دقائق هالأسبوع، بحب أسمع عن تجربتك. حتى لو ما بدك تجربي الأداة، رأيك بيساعدني كثير.

**Rules:**

- Personalise every message. No mass copy-paste.
- Ask for a conversation, not a sale.
- Don't promise features you haven't built.
- Get consent before adding anyone to a WhatsApp group.

---

## 3. Screener (2 minutes)

Ask before booking the interview:

1. Do you sell cakes/desserts from home? (Yes/No)
2. How many orders do you get in a normal week? (`0–5`, `6–15`, `16–30`, `30+`)
3. Where do customers order? (Instagram / WhatsApp / both / other)
4. Do you take deposits? (Always / sometimes / never)
5. Who manages the orders? (Me / family member / no one)

**Qualify:** at least 6 orders/week and takes deposits.  
**Disqualify:** hobby only, no paying customers.

---

## 4. Interview script (20 minutes)

Start with permission:

> "I'm researching how home bakers manage orders. There are no right or wrong answers, and I'm not selling anything today. I'll use your feedback to decide what to build. Can I take notes?"

### Warm-up

1. Tell me about your business. What do you sell, and how long have you been doing it?
2. How did you learn to bake/market your products?

### The last order (be concrete)

3. Walk me through the last custom order you received — from the first message to delivery.
4. Where did the conversation happen? Instagram, WhatsApp, phone?
5. What details did you need to collect? How did you record them?
6. How did you calculate the price and the deposit?
7. How did you remember the delivery date and address?
8. What went wrong, if anything?

### Frequency and cost

9. How many orders do you get in a normal week? In a busy week?
10. How many messages get buried or answered late?
11. Have you ever lost an order or a customer because of this? What happened?
12. How much time per day do you spend on order admin?
13. What does a lost order cost you?

### Current alternatives

14. What have you tried to organise this? Notebook, Excel, Google Forms, an app?
15. Why did you stop using it?
16. What do you like about your current way of working?

### Willingness to pay

17. If a tool captured every order, reminded you about deposits, and gave you a storefront link, what would that be worth per month?
18. Would you pay 15 JOD/month? 25? 35? (Ask directly; don't accept "maybe.")
19. What would make you say "this is too expensive"?
20. Who else do you know who has this problem?

### Close

> "I'm running a free 2-week pilot for 5 bakers. You'd share a link, I'd set up your storefront, and we'd manage your orders together. After 2 weeks, if it saves you time, it's 15 JOD/month for the first 20 sellers (normally 25 JOD/month). Interested?"

**Record:** name, phone, Instagram, orders/week, current tools, biggest pain, willingness to pay, referral.

---

## 5. Interview scorecard

Rate each interview 1–5:

| Signal | 1 | 3 | 5 |
|---|---|---|---|
| Pain frequency | Rarely | Monthly | Weekly/daily |
| Cost of pain | Annoyance | Some lost time | Lost orders/money |
| Workaround | None | Notebook/Excel | Chaotic chat search |
| Urgency | "Someday" | "Would be nice" | "I need this now" |
| Willingness to pay | 0 | 15 JOD | 25+ JOD |
| Reachability | Hard | Some groups | Active community |

**Green light:** average ≥ 4 on pain, cost, and willingness to pay across 10+ interviews.

---

## 6. Concierge MVP (2 weeks)

### Setup

1. Create a Google Form with these fields:
   - Customer name
   - WhatsApp number
   - Product
   - Quantity
   - Size/flavour
   - Pickup or delivery
   - Date and time
   - Address (if delivery)
   - Notes / inspiration photo upload
   - Deposit amount
2. Create a Google Sheet with columns:
   - Order ID
   - Date received
   - Customer
   - Phone
   - Product
   - Quantity
   - Total
   - Deposit status
   - Delivery date/time
   - Address
   - Status
   - Notes
3. Create a WhatsApp message template for each status:
   - **New order:** "أهلًا [الاسم]، وصلنا طلبك: [المنتج] ليوم [التاريخ]. العربون [المبلغ] د.أ. من فضلك حوّلي العربون لتأكيد الطلب."
   - **Confirmed:** "تم تأكيد طلبك [رقم الطلب] ليوم [التاريخ]. شكرًا!"
   - **Ready:** "طلبك جاهز! [تفاصيل الاستلام/التوصيل]."
   - **Thank you:** "شكرًا لطلبك! إذا حبيتِ، شاركينا صورة الكيكة ووسمينا."

### Daily routine

- Morning: review new orders, confirm availability, reply to customers.
- Afternoon: update the sheet, check deposits, send reminders.
- Evening: send the seller a summary of tomorrow's orders.

### What to measure

| Metric | How |
|---|---|
| Orders captured | Count per week |
| Orders lost/missed | Ask the seller at the end of each day |
| Time saved | Seller's estimate before/after |
| Deposits collected | Amount and percentage |
| Seller satisfaction | 1–5 after 2 weeks |
| Willingness to pay | Ask for 15 JOD/month (founding price) |

### After 2 weeks

Ask:

> "This is the pilot. If you want to keep using it, it's 15 JOD/month for the first 20 sellers (normally 25 JOD/month). Should I set up your subscription?"

**Payment is the validation.** A "yes" without payment is a no.

---

## 7. Landing page copy (Arabic)

### Hero

**Badge:** صُنع لمشاريع المنزل في الأردن

**Headline:**  
حوّلي رسائل الواتساب إلى **طلبات منظّمة**

**Subhead:**  
دكاني متجر بسيط ولوحة طلبات لمشاريع المنزل. سجّلي الطلبات، تابعي العربون، وذكّري زبائنك — كل ذلك من رابط واحد.

**CTA:** ابدئي مجانًا  
**Secondary:** شاهدي كيف يعمل

**Note:** بدون بطاقة ائتمان · إعداد في 10 دقائق · دعم بالعربي

### Problem

**Headline:** كل طلب يبدأ برسالة… وينتهي بفوضى

- **طلبات مدفونة:** رسائل إنستغرام وواتساب تختلط مع الطلبات القديمة. الطلب الجديد يضيع، والزبونة تنتظر.
- **عربون منسي:** من دفع؟ ومن لم يدفع؟ دفتر الملاحظات لا يخبرك.
- **مواعيد متضاربة:** عيد ميلاد يوم السبت، وتوصيل يوم الجمعة، وطلب ثالث نسيته. السمعة تدفع الثمن.

### How it works

1. **أنشئي متجرك:** أضيفي منتجاتك، أسعارك، وصورك في دقائق.
2. **شاركي الرابط:** حطّيه في البايو أو أرسليه للزبائن على واتساب.
3. **استلمي الطلبات:** كل طلب يصل إلى لوحة واحدة، مع تفاصيله وعربونه.

### Features

- **رابط متجر احترافي:** صفحة أنيقة تعرض منتجاتك وأسعارك.
- **لوحة طلبات:** من «جديد» إلى «تم التسليم» — كل شيء واضح.
- **تذكير بالعربون:** لا تتذكري من دفع؟ دكاني يتذكر.
- **رسائل واتساب جاهزة (ستارتر وما فوق):** قوالب جاهزة بالعربي، بضغطة واحدة.
- **سجل الزبائن:** تاريخ كل زبونة، طلباتها، وملاحظاتها.
- **تقارير بسيطة:** الأكثر مبيعًا، الإيرادات، والزبائن المتكررون.

### Pricing

- **مجاني:** 0 د.أ — حتى 15 طلبًا شهريًا، متجر ورابط، لوحة طلبات أساسية.
- **ستارتر:** 25 د.أ/شهر — طلبات غير محدودة، سجل زبائن، قوالب واتساب، تقارير. (15 د.أ/شهر لأول 20 مشروعًا.)
- **برو:** 45 د.أ/شهر — كل مزايا ستارتر، دومين خاص، حسابات فريق، دعم أولوية.

**Note:** سعر التأسيس لأول 20 مشروعًا فقط. بدون عمولة على الطلبات.

### FAQ

- **هل أحتاج إلى موقع إلكتروني؟** لا. دكاني يعطيك رابط جاهز.
- **هل تحتاج زبونتي إلى تطبيق؟** لا. تفتح الرابط وتطلب.
- **هل يعمل بالعربي؟** نعم، مصمم للعربي واتجاه RTL.
- **هل أستطيع استخدام واتساب؟** نعم. في البداية نجهز لك رسائل جاهزة بضغطة، ولاحقًا أتمتة كاملة.
- **هل هناك عمولة على الطلبات؟** لا. تدفعين اشتراكًا شهريًا فقط.
- **هل أستطيع تجربته قبل الدفع؟** نعم، 14 يومًا مجانًا.

### Waitlist

**Headline:** كوني من أول 20 صانعة منزل  
**Subhead:** سجّلي اسمك، وسنساعدك على إعداد متجرك مجانًا.

Fields: الاسم، رقم الواتساب، نوع المشروع، رابط إنستغرام (اختياري).

**Button:** أرسلي الطلب  
**Note:** بإرسالك، توافقين على التواصل معك عبر واتساب.

**Success:** تم! سنتواصل معك قريبًا لتجهيز متجرك.

---

## 8. Landing page copy (English)

### Hero

**Badge:** Built for home businesses in Jordan

**Headline:**  
Turn WhatsApp messages into **organised orders**

**Subhead:**  
Dukkani gives home businesses a simple storefront and order board. Capture orders, track deposits, and remind customers — all from one link.

**CTA:** Start free  
**Secondary:** See how it works

**Note:** No credit card · 10-minute setup · Arabic support

### Problem

**Headline:** Every order starts with a message… and ends in chaos

- **Buried orders:** Instagram and WhatsApp messages mix with old conversations. New orders get lost, and customers wait.
- **Forgotten deposits:** Who paid? Who didn't? A notebook won't tell you.
- **Clashing dates:** A birthday on Saturday, a delivery on Friday, and a third order you forgot. Your reputation pays the price.

### How it works

1. **Create your store:** Add your products, prices, and photos in minutes.
2. **Share the link:** Put it in your bio or send it on WhatsApp.
3. **Receive orders:** Every order lands on one board with its details and deposit.

### Features

- **Professional storefront link:** A clean page with your products and prices.
- **Order board:** From "new" to "delivered" — everything is clear.
- **Deposit tracking:** Can't remember who paid? Dukkani does.
- **Ready-made WhatsApp messages (Starter and up):** Arabic templates, one tap away.
- **Customer book:** Every customer's history, orders, and notes.
- **Simple reports:** Best sellers, revenue, and repeat customers.

### Pricing

- **Free:** 0 JOD — up to 15 orders/month, storefront link, basic order board.
- **Starter:** 25 JOD/month — unlimited orders, customer book, WhatsApp templates, reports. (15 JOD/month for the first 20 sellers.)
- **Pro:** 45 JOD/month — everything in Starter, custom domain, team accounts, priority support.

**Note:** Founding price for the first 20 sellers only. No commission on orders.

### FAQ

- **Do I need a website?** No. Dukkani gives you a ready link.
- **Does my customer need an app?** No. They open the link and order.
- **Does it work in Arabic?** Yes, built for Arabic and RTL.
- **Can I use WhatsApp?** Yes. Start with one-tap templates; automation comes later.
- **Is there a commission?** No. You pay a monthly subscription only.
- **Can I try it before paying?** Yes, 14 days free.

### Waitlist

**Headline:** Join the first 20 home businesses  
**Subhead:** Leave your details and we'll help you set up your storefront for free.

Fields: Name, WhatsApp number, business type, Instagram link (optional).

**Button:** Join the waitlist  
**Note:** By submitting, you agree to be contacted on WhatsApp.

**Success:** Done! We'll be in touch soon to set up your store.

---

## 9. Outreach tracker

Create a simple Google Sheet:

| Date | Name | Instagram | Phone | Orders/week | Current tools | Biggest pain | WTP (JOD) | Pilot? | Paid? | Notes |
|---|---|---|---|---|---|---|---|---|---|---|

Update after every conversation. Review weekly.

---

## 10. Weekly checklist

### Week 1

- [ ] List 30 candidate sellers.
- [ ] Send 15 personalised messages.
- [ ] Book 10 interviews.
- [ ] Publish the landing page.
- [ ] Collect 20 waitlist signups.
- [ ] Run 5 interviews.

### Week 2

- [ ] Finish 10–15 interviews.
- [ ] Start 5 concierge pilots.
- [ ] Measure orders/week and time saved.
- [ ] Ask for 15 JOD/month (founding price) after the pilot.
- [ ] Write a one-page findings summary.
- [ ] Decide: build, pivot, or stop.

---

## 11. Decision memo template

After 2 weeks, write:

```
What I tested:
Who I talked to:
What I expected:
What I learned:
  - Frequency:
  - Cost:
  - Current workaround:
  - Willingness to pay:
  - Reachability:
Paying pilots:
Biggest surprise:
Biggest risk:
Decision: build / pivot / stop
If build, the first 3 features are:
```

---

## 12. Ethics and practical notes

- Ask for consent before recording or adding anyone to a group.
- Don't spam Facebook groups; contribute useful answers first.
- Don't promise features you haven't built.
- Don't fake testimonials or user counts.
- Protect phone numbers and addresses; don't share screenshots without permission.
- If someone says "I'd pay," ask them to pay a deposit or commit to the pilot. Words are cheap.
- If 3 people don't pay, that's a valid result. Stop or change the niche.
