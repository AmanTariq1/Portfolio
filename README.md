# Portfolio

Local source of truth for Aman Tariq's portfolio. Frontend is static (index.html) and can be hosted on GitHub Pages; contact form email is handled by a backend endpoint.

## Contact email (agent response recipient)
- Recipient: mantariqa344@gmail.com (from CONTACT_TO, default in backend).
- Agents should use this email for contact responses.

## Architecture
- **Frontend:** static site (index.html, ssets/) — GitHub Pages/local.
- **Backend:** Vercel serverless function pi/send-email.js (Node, nodemailer) for contact form delivery. send-email.php remains for PHP hosting compatibility.

## Environment files
- .env.example — committed template (no secrets). Push this.
- .env — local secrets/config only. **Never push** (gitignored).

Required vars (set in Vercel Project → Settings → Environment Variables, or PHP host env):
| Variable | Notes |
| --- | --- |
| SMTP_HOST | SMTP server |
| SMTP_PORT | 587 default |
| SMTP_USER | SMTP username |
| SMTP_PASS | SMTP password/app password (secret) |
| SMTP_ENCRYPTION | 	ls or ssl |
| SMTP_FROM | Optional; defaults to SMTP_USER |
| CONTACT_TO | Optional; defaults to mantariqa344@gmail.com |
| ALLOWED_ORIGIN | Optional; comma-separated allowed frontend origins for CORS (e.g. GitHub Pages URL) |

## Deploy (GitHub frontend + Vercel backend)
1. Push this project to GitHub (do not commit .env, endor/, 
ode_modules/).
2. GitHub Pages: workflow .github/workflows/pages.yml deploys static site on push to main.
3. Vercel: import the same repo (or Portfolio project), set env vars above, deploy. Note API URL: https://<vercel-app>.vercel.app/api/send-email.
4. Configure frontend endpoint: in index.html before the contact script, set window.CONTACT_ENDPOINT = 'https://<vercel-app>.vercel.app/api/send-email'; (or host-specific injection). On non-GitHub hosting it falls back to orm.action (send-email.php).
5. Set ALLOWED_ORIGIN to your GitHub Pages origin if needed.

## Local
- Frontend: open index.html or serve statically.
- PHP backend: composer install + php -S localhost:8000 with env vars.
- Vercel backend: 
pm i and ercel dev with env vars.
