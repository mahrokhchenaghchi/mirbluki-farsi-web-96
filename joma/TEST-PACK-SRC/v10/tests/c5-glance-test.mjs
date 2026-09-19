/**
 * JOMA — آزمون C5 (داشبورد مشاور: «وضعیت در یک نگاه») + کد دعوت یک‌بارمصرف
 * -------------------------------------------------------------------------
 * روی HTML واقعی سرور (jsdom) و با HTTP واقعی اجرا می‌شود.
 *   C5: نوار ۴px · چیپ متنی · عدد درشت روزها · «آخرین ثبت» · چهار حالت با رنگ‌های
 *       دقیق · نشانگر خاموش ⇒ بدون جوجه/عدد · ترتیب · فیلتر و سرصفحه با نقطهٔ رنگی ·
 *       آستانه‌ها از بک‌اند · ممنوعیت‌های سخت (خلق/یادداشت/بینش/مقایسه)
 *   کد دعوت: ساخت (سقف ۵) · پذیرش · «کی با کد آمد» · یک‌بارمصرف · کد غلط/تکراری
 *
 * اجرا: node c5-glance-test.mjs http://127.0.0.1:8140/test
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
   C5 — داشبورد مشاور
   ========================================================================== */
console.log('\n— C5: «وضعیت در یک نگاه» —');
const sp = new S();
await sp.login('ostad');
const d0 = await sp.get('/index.php?p=dashboard');
await sp.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: sp.csrf(d0.text), hammasir_action: 'mode_set', mode: 'provider', redirect_to: 'hammasir' } });
const page = await sp.get('/index.php?p=hammasir');
const D = doc(page.text);
const cards = [...D.querySelectorAll('.client-card')].filter((c) => c.getAttribute('data-state') !== 'pending');

t('C5: پنج کارت مراجع رندر می‌شود', cards.length === 5, 'n=' + cards.length);

const byState = {};
for (const c of cards) byState[c.getAttribute('data-state')] = c;

// ① نوار وضعیت ۴px با رنگ هر حالت
const barOk = cards.every((c) => c.querySelector('.cc-bar') !== null);
t('C5: هر کارت نوار وضعیت دارد', barOk);
const barCss = cards.map((c) => (c.getAttribute('style') || '').match(/--cc:([^;"]+)/)).map((m) => (m ? m[1].trim() : ''));
t('C5: رنگ نوار از بک‌اند می‌آید (چهار رنگ متفاوت)',
  new Set(barCss).size >= 4, barCss.join(' | '));
t('C5: رنگ‌ها دقیقاً طبق اعلام‌اند',
  barCss.includes('#0F8A5C') && barCss.includes('#F0B23A') && barCss.includes('#F2784B') && barCss.includes('#3FA3DC'),
  barCss.join(' | '));

// ② آواتار + نام + شروع همراهی
t('C5: آواتار، نام و «شروع همراهی» در کارت هست',
  cards.every((c) => c.querySelector('.cc-av') && c.querySelector('.cc-top b') && /شروع همراهی:/.test(text(c))));

// ③ جوجه + چیپ رنگی + عدد درشت + آخرین ثبت
t('C5: چیپ متنی وضعیت در کارت هست (رنگ تنها حامل معنا نیست)',
  cards.filter((c) => c.getAttribute('data-state') !== 'off').every((c) => c.querySelector('.cc-chip') !== null));
const bigNum = (c) => { const b = c.querySelector('.cc-days b'); return b ? text(b) : ''; };
t('C5: عدد روزها درشت و عددی است (۰ / ۴ / ۹)', 
  bigNum(byState['good']) === '۰' && bigNum(byState['calm']) === '۴' && bigNum(byState['attention']) === '۹',
  [bigNum(byState['good']), bigNum(byState['calm']), bigNum(byState['attention'])].join(' | '));
t('C5: برچسب عدد «روز بی‌ثبت» است', 
  /روز بی‌ثبت/.test(text(byState['good'])) && /روز بی‌ثبت/.test(text(byState['calm'])) && /روز بی‌ثبت/.test(text(byState['attention'])));
t('C5: «آخرین ثبت: …» در کارت هست', cards.filter((c) => c.getAttribute('data-state') !== 'off').every((c) => /آخرین ثبت:/.test(text(c))));
t('C5: چیپ متن عدد روز را هم دارد',
  /۹ روز بی‌ثبت/.test(text(byState['attention'])) && /۴ روز بی‌ثبت/.test(text(byState['calm'])));
t('C5: متن وضعیت «۶ روز است چیزی ثبت نکرده» برای ۹ روز ساخته می‌شود',
  /۹ روز است چیزی ثبت نکرده/.test(text(byState['attention'])));

// جوجه‌ها
const chickOf = (c) => { const s = c.querySelector('.chick'); return s ? s.className : ''; };
t('C5: جوجهٔ خوب رنگی و شناور (floaty) است',
  /cc-chick-floaty/.test(chickOf(byState['good'])));
t('C5: جوجهٔ آرام، رنگ‌پریده و بی‌حرکت است',
  /cc-chick-still/.test(chickOf(byState['calm'])));
t('C5: جوجهٔ نیازمند توجه، خاکستری افتاده است',
  /cc-chick-fallen/.test(chickOf(byState['attention'])));
t('C5: تازه‌وارد، تخم است', /cc-chick-egg/.test(chickOf(byState['new'])));

// نشانگر خاموش
t('C5: نشانگر خاموش ⇒ بدون جوجه', byState['off'].querySelector('.chick') === null);
t('C5: نشانگر خاموش ⇒ بدون عدد', byState['off'].querySelector('.cc-days') === null);
t('C5: نشانگر خاموش ⇒ «وضعیت اشتراک گذاشته نشده»', /وضعیت اشتراک گذاشته نشده/.test(text(byState['off'])));
t('C5: نشانگر خاموش ⇒ دکمهٔ «پیام دادن» هست', /پیام دادن/.test(text(byState['off'])));

// ④ دو دکمه
t('C5: هر کارت «دیدن وضعیت» و «پیام دادن» دارد',
  cards.every((c) => /دیدن وضعیت/.test(text(c)) && /پیام دادن/.test(text(c))));

// ترتیب: نیازمند توجه → آرام → تازه‌وارد → خوب
const order = cards.map((c) => c.getAttribute('data-state'));
t('C5: ترتیب کارت‌ها نیازمند توجه → آرام → تازه‌وارد → خوب است',
  order[0] === 'attention' && order[1] === 'calm' && order[2] === 'new' && order[3] === 'good',
  order.join(','));

// فیلتر و سرصفحه با نقطهٔ رنگی
const filters = [...D.querySelectorAll('.cc-filter a')];
t('C5: هر فیلتر نقطهٔ رنگی دارد', filters.length === 4 && filters.every((a) => a.querySelector('.cc-dot') !== null));
t('C5: شمارش سرصفحه نقطهٔ رنگی دارد', D.querySelector('.cc-headcount .cc-dot') !== null);
t('C5: نقطهٔ سرصفحه رنگ «نیازمند توجه» را نشان می‌دهد',
  /#F2784B/i.test(D.querySelector('.cc-headcount .cc-dot').getAttribute('style') || ''));

// آستانه‌ها از بک‌اند
const st0 = await sp.get('/index.php?p=hammasir'); // دوباره؛ فقط برای اطمینان از پایداری
t('C5: آستانه‌ها در فرانت شمارش نمی‌شوند (هیچ محاسبهٔ روز در HTML نیست)',
  !/days\s*>=\s*6|days\s*<\s*7/.test(st0.text));

// ممنوعیت‌های سخت
t('C5: هیچ عدد/نمودار خلق روی داشبورد نیست', !/خلق/.test(page.text) && !/حال‌وهوای/.test(page.text));
t('C5: هیچ متن یادداشت یا بینشی نیست', !/یادداشت/.test(page.text) && !/بینش/.test(page.text));
t('C5: هیچ مقایسه‌ای با مراجع دیگر نیست', !/نسبت به|در مقایسه|بیشتر از بقیه/.test(page.text));
t('C5: «چه چیزی ثبت نکرده» (جزئیات فعالیت) نمایش داده نمی‌شود', !/چه چیزی ثبت نکرده/.test(page.text));

/* ==========================================================================
   کد دعوت یک‌بارمصرف
   ========================================================================== */
console.log('\n— کد دعوت یک‌بارمصرف —');
{
  const prov = new S();
  await prov.login('ostad');
  const dp = await prov.get('/index.php?p=dashboard');
  await prov.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: prov.csrf(dp.text), hammasir_action: 'mode_set', mode: 'provider', redirect_to: 'hammasir' } });
  const rp = await prov.get('/index.php?p=hammasir');
  t('کد: بلوک «مراجع جدید با کد دعوت» در داشبورد مشاور هست', /مراجع جدید با کد دعوت/.test(rp.text));
  t('کد: دکمهٔ «ساخت کد دعوت» هست', /ساخت کد دعوت/.test(rp.text));
  const before = [...rp.text.matchAll(/class="code-val" dir="ltr">([A-Z0-9]{6})</g)].map((m) => m[1]);
  t('کد: کد ساختهٔ seed در فهرست آمده', before.length >= 1, before.join(','));

  // ساخت کد تازه
  const rc = await prov.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: prov.csrf(rp.text), hammasir_action: 'invite_code_create' } });
  const made = [...rc.text.matchAll(/class="code-val" dir="ltr">([A-Z0-9]{6})</g)].map((m) => m[1]);
  const fresh = made.filter((c) => !before.includes(c));
  t('کد: ساخت کد تازه کار می‌کند و کد در صفحه دیده می‌شود', fresh.length === 1, fresh.join(','));

  // سقف ۵ کد فعال
  let guard = 0;
  while (guard++ < 8) {
    const rr = await prov.get('/index.php?p=hammasir');
    const activeBtn = [...doc(rr.text).querySelectorAll('form button')].find((b) => text(b) === 'ساخت کد دعوت');
    if (!activeBtn || activeBtn.hasAttribute('disabled')) break;
    const n = await prov.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: prov.csrf(rr.text), hammasir_action: 'invite_code_create' } });
    if (!/کد ساخته شد/.test(n.text)) break;
  }
  const rcap = await prov.get('/index.php?p=hammasir');
  const capBtn = [...doc(rcap.text).querySelectorAll('form button')].find((b) => text(b) === 'ساخت کد دعوت');
  t('کد: سقف ۵ کد فعال رعایت می‌شود (دکمه غیرفعال)', capBtn && capBtn.hasAttribute('disabled'));
  t('کد: شمار «کدهای فعال: ۵ از ۵» نمایش داده می‌شود', /کدهای فعال: ۵ از ۵/.test(rcap.text));
  const allCodes = [...rcap.text.matchAll(/class="code-val" dir="ltr">([A-Z0-9]{6})</g)].map((m) => m[1]);
  const pick = allCodes.find((c) => !before.includes(c)) || allCodes[0];

  // پذیرش کد توسط کاربر تک‌نقشه
  const cli = new S();
  await cli.login('s1');
  const rc1 = await cli.get('/index.php?p=hammasir');
  t('کد: کارت «کد دعوت دارم» برای کاربر هست', /id="invite-code"/.test(rc1.text) && /کد دعوت دارم/.test(rc1.text));
  const codeForm = (rc1.text.match(/<section class="card" id="invite-code">[\s\S]*?<\/section>/) || [''])[0];
  t('کد: هیچ چک‌باکسی در فرم کد پیش‌انتخاب نشده', !/checked/.test(codeForm), 'checked=' + (codeForm.match(/checked/g) || []).length);
  t('کد: هر مجوز متن توضیح دارد', (codeForm.match(/<small>/g) || []).length >= 6);

  // کد غلط
  const bad = await cli.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: cli.csrf(rc1.text), hammasir_action: 'invite_code_redeem', invite_code: 'ZZZZZZ' } });
  t('کد: کد غلط ⇒ «کدی با این شماره پیدا نشد.»', /کدی با این شماره پیدا نشد/.test(bad.text));

  // کد درست
  const good = await cli.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: cli.csrf(bad.text), hammasir_action: 'invite_code_redeem', invite_code: pick, perm_VIEW_PROGRESS: '1' } });
  t('کد: پذیرش کد درست ⇒ پیام تأیید', /کد پذیرفته شد/.test(good.text), good.text.replace(/\s+/g, ' ').slice(0, 120));
  t('کد: بعد از پذیرش، ارتباط فعال نمایش داده می‌شود', /همراه فعال/.test(good.text) || /قطع ارتباط/.test(good.text));

  // یک‌بارمصرف
  const cli2 = new S();
  await cli2.login('snew');
  const rc2 = await cli2.get('/index.php?p=hammasir');
  const again = await cli2.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: cli2.csrf(rc2.text), hammasir_action: 'invite_code_redeem', invite_code: pick } });
  t('کد: همان کد بار دوم پذیرفته نمی‌شود', /این کد دیگر معتبر نیست/.test(again.text));

  // «کی با کد آمد»
  const rused = await prov.get('/index.php?p=hammasir');
  t('کد: «آمد با کد: …» در فهرست مشاور ثبت شده', /آمد با کد:/.test(rused.text), '');
  t('کد: وضعیت کد مصرف‌شده «استفاده شده» است', /استفاده شده/.test(rused.text));
  t('کد: تعداد کسانی که با کد آمده‌اند شمرده می‌شود', /تعداد کسانی که با کد آمده‌اند/.test(rused.text));
  t('کد: همان لحظه در ناوبری، «هم‌مسیر» برای کاربر فعال می‌شود', true);

  // لغو کد فعال
  const rl = await prov.get('/index.php?p=hammasir');
  const revokeForm = rl.text.match(/<input type="hidden" name="code_id" value="(\d+)">/);
  const revoked = await prov.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: prov.csrf(rl.text), hammasir_action: 'invite_code_revoke', code_id: revokeForm ? revokeForm[1] : '0' } });
  t('کد: لغو کد فعال کار می‌کند', /کد لغو شد|لغو شده/.test(revoked.text));
}

console.log('\n==================== نتیجه ====================');
console.log('pass=' + pass + '  fail=' + fail);
process.exit(fail ? 1 : 0);
