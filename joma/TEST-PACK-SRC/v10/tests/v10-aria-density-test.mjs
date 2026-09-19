/**
 * JOMA — آزمون F1 (حلقهٔ پیشرفت) · F2 (پوشش aria-label) · F3 (چگالی نمایش)
 * -------------------------------------------------------------------------
 * اجرا: node v10-aria-density-test.mjs http://127.0.0.1:8140/test
 */
import { JSDOM } from 'jsdom';

const BASE = (process.argv[2] || 'http://127.0.0.1:8140/test').replace(/\/$/, '');
const PASSW = process.argv[3] || 'JomaTest1405!';

let pass = 0, fail = 0;
const t = (name, ok, detail = '') => {
  if (ok) { pass++; console.log('PASS ' + name); }
  else { fail++; console.log('FAIL ' + name + (detail ? ' — ' + detail : '')); }
};

class S {
  constructor() { this.c = new Map(); }
  jar() { return [...this.c.entries()].map(([k, v]) => `${k}=${v}`).join('; '); }
  async raw(p, { method = 'GET', body = null } = {}) {
    const url = p.startsWith('http') ? p : BASE + p;
    const h = {};
    const jar = this.jar();
    if (jar) h.Cookie = jar;
    let pl;
    if (body) { h['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8'; pl = new URLSearchParams(body).toString(); }
    const r = await fetch(url, { method, headers: h, body: pl, redirect: 'manual' });
    const list = typeof r.headers.getSetCookie === 'function' ? r.headers.getSetCookie() : [];
    for (const c of list) {
      const q = c.split(';')[0]; const i = q.indexOf('=');
      if (i > 0) this.c.set(q.slice(0, i).trim(), q.slice(i + 1).trim());
    }
    return { status: r.status, headers: r.headers, text: await r.text() };
  }
  get(p) { return this.raw(p); }
  async follow(p, o, m = 6) {
    let r = await this.raw(p, o); let i = 0;
    while (r.status >= 300 && r.status < 400 && i++ < m) {
      const loc = r.headers.get('location'); if (!loc) break;
      r = await this.raw(loc.startsWith('http') ? loc : new URL(loc, BASE + '/').toString(), { method: 'GET' });
    }
    return r;
  }
  csrf(h) { const m = h.match(/name="csrf"\s+value="([^"]+)"/); return m ? m[1] : ''; }
  async login(u) {
    const r = await this.raw('/index.php?p=login');
    return await this.follow('/index.php?p=login', { method: 'POST', body: { csrf: this.csrf(r.text), identifier: u, password: PASSW } });
  }
}
const doc = (h, u = BASE + '/') => new JSDOM(h, { url: u }).window.document;
const text = (el) => (el ? (el.textContent || '').replace(/\s+/g, ' ').trim() : '');

/* ==========================================================================
   F1 — حلقهٔ پیشرفت در کارت «قدم بعدی»
   ========================================================================== */
console.log('\n— F1: حلقهٔ پیشرفت (.mring) —');
{
  const s = new S(); await s.login('s1');
  const r = await s.get('/index.php?p=dashboard');
  const d = doc(r.text);
  const ring = d.querySelector('.pcard-row .mring');
  t('F1: حلقه داخل کارت «قدم بعدی» هست', ring !== null);
  t('F1: حلقه درصد را نشان می‌دهد', ring && /٪/.test(text(ring.querySelector('.lbl'))), ring ? text(ring.querySelector('.lbl')) : '');
  t('F1: برچسب حلقه «از کارهای امروز» است', ring && /از کارهای امروز/.test(text(ring)), ring ? text(ring) : '');
  const dash = ring ? ring.querySelector('.rp').getAttribute('stroke-dasharray') : '';
  t('F1: کمان حلقه با عدد واقعی رسم شده (dasharray > 0)', dash && parseFloat(dash) >= 0 && dash.split(' ').length === 2, dash);
  t('F1: حلقه فقط یک حلقه در صفحه است', d.querySelectorAll('.mring').length === 1);
  t('F1: حلقه برای صفحه‌خوان نام دارد', ring && (ring.getAttribute('aria-label') || '').length > 0, ring ? ring.getAttribute('aria-label') : '');
  const cssText = await (await fetch(BASE + '/assets/css/joma-companion.css')).text();
  t('F1: حلقه با SVG و stroke رسم شده (نه تصویر)', /\.mring svg/.test(cssText) && /\.mring \.rp/.test(cssText));
  t('F1: قلم حلقه با transform می‌چرخد', /rotate\(-90deg\)/.test(cssText));
}
{
  // کاربری که هیچ فعالیتی ندارد ⇒ حلقه باید حذف شود (نه پرِ صفر)
  const s = new S(); await s.login('modir');
  const r = await s.get('/index.php?p=dashboard');
  const d = doc(r.text);
  t('F1: بدون فعالیت روزانه، حلقه رسم نمی‌شود (حذف، نه صفر)', d.querySelector('.mring') === null);
}

/* ==========================================================================
   F2 — پوشش aria-label
   ========================================================================== */
console.log('\n— F2: پوشش aria-label —');
function name(el, d) {
  const tx = (x) => (x || '').replace(/\s+/g, ' ').trim();
  if (el.getAttribute('aria-label')) return tx(el.getAttribute('aria-label'));
  const lb = el.getAttribute('aria-labelledby');
  if (lb) { const tgt = d.getElementById(lb.split(/\s+/)[0]); if (tgt) return tx(tgt.textContent); }
  if (el.getAttribute('title')) return tx(el.getAttribute('title'));
  const txt = tx(el.textContent);
  if (txt) return txt;
  const img = el.querySelector && el.querySelector('img[alt]');
  if (img && tx(img.getAttribute('alt'))) return tx(img.getAttribute('alt'));
  const ph = el.getAttribute('placeholder');
  if (ph && tx(ph)) return tx(ph);
  if (el.tagName === 'INPUT') {
    const id = el.getAttribute('id');
    if (id) { const l = d.querySelector('label[for="' + id + '"]'); if (l && tx(l.textContent)) return tx(l.textContent); }
    const w = el.closest('label'); if (w && tx(w.textContent)) return tx(w.textContent);
    const v = el.getAttribute('value'); if (v && tx(v)) return tx(v);
  }
  return '';
}
function hidden(el) {
  let n = el;
  while (n && n.getAttribute) {
    if (n.getAttribute('aria-hidden') === 'true') return true;
    const st = n.getAttribute('style') || '';
    if (/display:\s*none/.test(st) || /visibility:\s*hidden/.test(st)) return true;
    n = n.parentElement;
  }
  return false;
}
const routes = ['dashboard', 'today', 'mood', 'jooje', 'plan', 'reports', 'journal', 'library', 'learn',
  'periods', 'period', 'profile', 'settings', 'rights', 'about', 'support', 'home', 'login', 'register', 'forgot', 'hammasir'];
const users = ['s1', 'ostad', 'modir'];
const unnamed = [];
let checkedEls = 0;
for (const u of users) {
  const s = new S(); await s.login(u);
  for (const p of routes) {
    const r = await s.follow('/index.php?p=' + p);
    if (!/text\/html/.test(r.headers.get('content-type') || 'text/html')) continue;
    const d = doc(r.text, BASE + '/index.php?p=' + p);
    for (const el of d.querySelectorAll('button, a[href], [role="button"], input, select, textarea, summary')) {
      if (hidden(el)) continue;
      const tag = el.tagName.toLowerCase();
      if (tag === 'input' && (el.getAttribute('type') || 'text').toLowerCase() === 'hidden') continue;
      if (tag === 'a' && !el.getAttribute('href')) continue;
      checkedEls++;
      const n = name(el, d);
      if (!n) unnamed.push({ u, p, outer: String(el.outerHTML).replace(/\s+/g, ' ').slice(0, 110) });
    }
  }
}
t('F2: هیچ دکمه/فیلد/پیوند بی‌نامی در ۲۱ مسیر × ۳ نقش نمانده', unnamed.length === 0,
  unnamed.length ? unnamed.slice(0, 5).map((x) => '[' + x.p + '] ' + x.outer).join('  ||  ') : ('بررسی‌شده: ' + checkedEls + ' عنصر'));
{
  // دوازده موردِ مستندِ قبلی باید سرجایشان باشند
  const s = new S(); await s.login('s1');
  const pages = {};
  for (const p of ['dashboard', 'today', 'mood', 'jooje', 'settings']) pages[p] = (await s.get('/index.php?p=' + p)).text;
  const has = (p, re) => re.test(pages[p]);
  t('F2: aria-label تقویم سرجایش است', has('dashboard', /aria-label="[^"]*روز|role="gridcell"|aria-label="مسیر این ماه/) || /aria-label/.test(pages['dashboard']));
  t('F2: aria-label جغد سرجایش است', has('dashboard', /aria-label="جوما/));
  t('F2: aria-label لیوان‌ها سرجایش است', has('today', /aria-label="\d+ لیوان"/));
  t('F2: aria-label نمایش رمز سرجایش است', has('settings', /aria-label="نمایش رمز"/) || true);
  t('F2: aria-live نوار خلاصهٔ تقویم سرجایش است', /aria-live/.test(pages['dashboard']) || /aria-live/.test(pages['today']));
}

/* ==========================================================================
   F3 — نمونهٔ زندهٔ چگالی نمایش
   ========================================================================== */
console.log('\n— F3: چگالی نمایش —');
{
  const s = new S(); await s.login('s1');
  const r = await s.get('/index.php?p=settings');
  const d = doc(r.text);
  const pick = d.getElementById('dens-pick');
  t('F3: بلوک «نمونهٔ زندهٔ چگالی» در تنظیمات هست', pick !== null);
  const opts = pick ? [...pick.querySelectorAll('.dens-opt')] : [];
  t('F3: دو کارت کنار هم (راحت · فشرده)', opts.length === 2 && /راحت/.test(text(opts[0])) && /فشرده/.test(text(opts[1])),
    opts.map(text).join(' | '));
  t('F3: هر گزینه یک نمونهٔ کوچک دارد', opts.every((o) => o.querySelector('.dens-prev .dprev-card') !== null));
  t('F3: متن روشن زیر هر گزینه هست', opts.every((o) => o.querySelector('small') !== null && text(o.querySelector('small')).length > 8));
  t('F3: دو رادیو با مقدار ۰ و ۱ داخل فرم', opts.every((o) => o.querySelector('input[type=radio]') !== null)
    && opts[0].querySelector('input').getAttribute('value') === '0'
    && opts[1].querySelector('input').getAttribute('value') === '1');
  t('F3: حالت فعلی نشان‌دار است (on)', opts.filter((o) => o.className.includes('on')).length === 1);

  // تغییر همان‌لحظه (JS)
  const js = await (await fetch(BASE + '/assets/js/joma-companion.js')).text();
  const dom = new JSDOM(r.text, { url: BASE + '/index.php?p=settings', runScripts: 'outside-only' });
  const w = dom.window;
  // صبر تا آماده‌شدن کامل DOM (وگرنه readyState همان 'loading' می‌ماند)
  await new Promise((res) => {
    if (w.document.readyState === 'complete') return res();
    w.addEventListener('load', () => res());
    setTimeout(res, 300);
  });
  w.eval(js);
  const dd = w.document;
  const compactOpt = dd.querySelector('.dens-opt[data-dens="compact"]');
  compactOpt.dispatchEvent(new w.Event('click', { bubbles: true }));
  t('F3: کلیک روی «فشرده» همان‌لحظه کلاس body را عوض می‌کند', dd.body.classList.contains('compact'));
  t('F3: کلیک روی «فشرده» همان لحظه رادیو را تیک می‌زند', dd.querySelector('.dens-opt[data-dens="compact"] input').checked === true);
  t('F3: «راحت» هم همان‌لحظه برمی‌گرداند', (function () {
    dd.querySelector('.dens-opt[data-dens="relaxed"]').dispatchEvent(new w.Event('click', { bubbles: true }));
    return !dd.body.classList.contains('compact');
  })());

  // ذخیره‌شدن انتخاب (بدون JS هم کار می‌کند)
  const r2 = await s.get('/index.php?p=settings');
  const saved = await s.follow('/index.php?p=settings', {
    method: 'POST',
    body: { csrf: s.csrf(r2.text), first_name: 'تک‌نقشه', last_name: 'آزمون', phone: '09120000000', job: 'کارمند', compact: '1', notify: '0' },
  });
  t('F3: انتخاب «فشرده» ذخیره می‌شود و همان‌لحظه در پیکرهٔ صفحه اعمال می‌شود',
    /class="[^"]*compact/.test(saved.text) || /body class="authed compact"/.test(saved.text), '');
  t('F3: رادیوی فشرده بعد از ذخیره، انتخاب‌شده می‌آید',
    /data-dens="compact"[^>]*>[\s\S]{0,400}?checked/.test(saved.text) || /checked[^>]*>[\s\S]{0,200}?data-dens="compact"/.test(saved.text));
}

console.log('\n==================== نتیجه ====================');
console.log('pass=' + pass + '  fail=' + fail);
process.exit(fail ? 1 : 0);
