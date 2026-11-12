# Portfolio contact email setup

The contact form sends submissions to `amantariqa344@gmail.com` through the PHP endpoint `send-email.php`.

## Requirements

- PHP 7.4 or newer
- Composer
- A working SMTP account
- PHP hosting (the form will not send email when the HTML file is opened directly with `file://`)

## Install

Run this in the `Portfolio` folder, then deploy the generated `vendor/` directory with the site:

```sh
composer install --no-dev --optimize-autoloader
```

Configure these environment variables in the hosting control panel or PHP-FPM environment. Do not put SMTP credentials in the HTML, JavaScript, or a public file.

| Variable | Example | Notes |
| --- | --- | --- |
| `SMTP_HOST` | `smtp.example.com` | SMTP server hostname |
| `SMTP_PORT` | `587` | Defaults to `587` |
| `SMTP_ENCRYPTION` | `tls` | Use `tls` or `ssl`; defaults to `tls` |
| `SMTP_USER` | `you@example.com` | SMTP username |
| `SMTP_PASS` | `your-provider-app-password` | SMTP password or provider app password |
| `SMTP_FROM` | `you@example.com` | Optional; defaults to `SMTP_USER` |
| `CONTACT_TO` | `amantariqa344@gmail.com` | Optional recipient override |

Use the email provider's app password where required. Keep credentials in the hosting provider's secret/environment settings, not in this project.

For local testing, start a PHP server from this folder with `php -S localhost:8000` and open the portfolio HTML through `http://localhost:8000/`. SMTP environment variables and Composer dependencies must also be configured for delivery to work.

## GitHub Pages

The workflow in `.github/workflows/pages.yml` publishes the portfolio as a static site. It copies the portfolio HTML to `index.html` and includes the certificate assets. GitHub Pages does not run PHP, so the contact form's `send-email.php` endpoint will not work on the Pages deployment. Use PHP-supported hosting for the site and email endpoint when you are ready to enable email delivery.
