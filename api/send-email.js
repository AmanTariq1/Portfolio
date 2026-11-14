import nodemailer from 'nodemailer';

function json(res, status, message) {
  res.status(status).setHeader('Content-Type', 'application/json; charset=utf-8')
    .setHeader('Cache-Control', 'no-store')
    .send(JSON.stringify({ message }));
}

export default async function handler(req, res) {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  const origin = req.headers.origin || '';
  const allowed = (process.env.ALLOWED_ORIGIN || '').split(',').map(s => s.trim()).filter(Boolean);
  if (allowed.length && origin && allowed.includes(origin)) {
    res.setHeader('Access-Control-Allow-Origin', origin);
    res.setHeader('Vary', 'Origin');
  }
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    return res.status(204).end();
  }
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST, OPTIONS');
    return json(res, 405, 'This endpoint only accepts contact form submissions.');
  }

  const body = typeof req.body === 'string' ? Object.fromEntries(new URLSearchParams(req.body)) : (req.body || {});
  const name = String(body.name || '').trim();
  const email = String(body.email || '').trim();
  const message = String(body.message || '').trim();
  if (!name || !email || !message) return json(res, 400, 'Please complete all contact form fields.');
  if (name.length > 120 || email.length > 254 || message.length > 5000 || /[\r\n]/.test(name) || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return json(res, 400, 'Please check your name, email and message, then try again.');
  }

  const host = (process.env.SMTP_HOST || '').trim();
  const user = (process.env.SMTP_USER || '').trim();
  const pass = String(process.env.SMTP_PASS || '');
  const port = parseInt(process.env.SMTP_PORT || '587', 10);
  const encryption = (process.env.SMTP_ENCRYPTION || 'tls').toLowerCase();
  const from = (process.env.SMTP_FROM || user).trim();
  const to = (process.env.CONTACT_TO || 'amantariqa344@gmail.com').trim();
  if (!host || !user || !pass || !from || !to || !['tls','ssl'].includes(encryption) || !Number.isFinite(port)) {
    console.error('Portfolio contact form: SMTP environment configuration is incomplete or invalid.');
    return json(res, 503, 'Email is not configured yet. Please try again later.');
  }

  try {
    const transporter = nodemailer.createTransport({
      host, port, secure: encryption === 'ssl', auth: { user, pass }
    });
    await transporter.sendMail({
      from: `Aman Tariq Portfolio <${from}>`,
      to,
      replyTo: `"${name.replace(/"/g, '')}" <${email}>`,
      subject: 'Portfolio contact form message',
      text: `Name: ${name}\nEmail: ${email}\n\nMessage:\n${message}`
    });
    return json(res, 200, 'Thanks for your message. I will get back to you soon.');
  } catch (err) {
    console.error('Portfolio contact form: SMTP delivery failed:', err && err.message);
    return json(res, 502, 'Your message could not be sent right now. Please try again later.');
  }
}
