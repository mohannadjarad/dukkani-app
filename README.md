# Dukkani — project folder

Everything for the Dukkani validation project is in this folder.

## Files

| File | What it is |
|---|---|
| `dukkani-landing/` | Landing page source (`index.html`, previews, README). |
| `dukkani-landing.zip` | Ready-to-deploy zip. Has `index.html` at the root. |
| `dukkani-spec.md` | Product and technical spec for the Laravel MVP. |
| `dukkani-validation-kit.md` | Interview script, scorecard, concierge pilot SOP, decision memo. |
| `dukkani-outreach-messages.txt` | Arabic/English outreach, follow-up, WhatsApp reply and quick-reply templates. |
| `dukkani-outreach-messages.pdf` | The same file as a readable, printable document (bold, larger type). |
| `dukkani-outreach-messages.docx` | Editable version of the PDF (opens in Word/LibreOffice). |
| `app-opportunities.md` | The original idea research and scoring that led to Dukkani. |

## Repository

**https://github.com/mohannadjarad/dukkani-app** (private)

The zip is not tracked (it is a build artifact — see `.gitignore`). To save work:

```bash
git add -A
git commit -m "what changed"
git push
```

## Live site

**https://dukkani-app.netlify.app**

Two ways to update it.

**A. With the repo connected (do this once, then just `git push`):**

1. Netlify → **dukkani-app** site → **Site configuration** → **Build & deploy**.
2. **Link repository** → GitHub → authorize → pick **dukkani-app**.
3. Branch: `main`. Build command: leave empty. Publish directory: `dukkani-landing`.
4. Deploy. From then on every `git push` publishes automatically.

**B. Manual (dragging):**

1. Open https://app.netlify.com and select the **dukkani-app** site.
2. Go to **Deploys**.
3. Drag the `dukkani-landing` folder onto the deploy area.
4. Wait for **Published**, then hard-refresh the page.

Do **not** drag this parent `dukkani` folder to Netlify — it has no `index.html` at its root.

## Start here

1. Read `dukkani-validation-kit.md`.
2. Send 15 personalised messages using `dukkani-outreach-messages.txt`.
3. Book 10 interviews and run 5 concierge pilots.
4. Ask for **5 JOD/month**.
5. Build the Laravel MVP only after **3 paid commitments**.

The page is already live and the waitlist is connected to Formspree. The next number that matters is paid sellers, not page views.
