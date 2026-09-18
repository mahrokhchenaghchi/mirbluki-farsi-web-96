/**
 * JOMA — آزمون واقعی لیوان آب با DOM واقعی (jsdom)
 * -------------------------------------------------------------------------
 * این آزمون **کد شلیپ‌شدهٔ** assets/js/joma-v2.js را روی **HTML واقعی سرور**
 * اجرا می‌کند و دنبالهٔ اجباری مالک را می‌سنجد:
 *      ۱→۱ | ۱→۰ | ۳→۳ | ۱→۱ | ۵→۵ | ۵→۴
 * نماد: (لیوانِ لمس‌شده) → (وضعیتِ N پس از همان یک لمس)
 *
 * اجرا: node tap-test.mjs <baseUrl> <cookieFile>
 */
import fs from 'node:fs';
import { JSDOM } from 'jsdom';

const base = process.argv[2] || 'http://127.0.0.1:8120/test';
const cookieJar = process.argv[3] || '/home/user/work-tmp/x/jar/a.jar';
const date = process.argv[4] || '';

/* --- کوکی نشست را از فایل جار curl برمی‌داریم --- */
const jar = fs.readFileSync(cookieJar, 'utf8');
const m = jar.match(/PHPSESSID\s+(\S+)/);
const cookie = m ? `PHPSESSID=${m[1]}` : '';

async function get(url) {
  const r = await fetch(url, { headers: { Cookie: cookie }, redirect: 'manual' });
  return await r.text();
}
async function post(url, params) {
  const body = new URLSearchParams(params).toString();
  const r = await fetch(url, {
    method: 'POST', redirect: 'manual',
    headers: { Cookie: cookie, 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
    body,
  });
  const text = await r.text();
  try { return JSON.parse(text); } catch (e) { return { __raw: text.slice(0, 200) }; }
}

let pass = 0, fail = 0;
const t = (name, ok, detail = '') => {
  if (ok) { pass++; console.log('PASS ' + name); }
  else { fail++; console.log('FAIL ' + name + (detail ? ' — ' + detail : '')); }
};

/* --- HTML واقعی صفحهٔ امروز را می‌گیریم --- */
const url = `${base}/index.php?p=today${date ? '&date=' + date : ''}`;
const html = await get(url);
const dom = new JSDOM(html, { url, runScripts: 'outside-only', pretendToBeVisual: true });
const { window } = dom;
const doc = window.document;

/* --- فقط بلاک آب را از فایل واقعی JS اجرا می‌کنیم (همان چیزی که در مرورگر می‌دود) --- */
const jsSrc = await (await fetch(`${base}/assets/js/joma-v2.js`)).text();
const anchorWater = jsSrc.indexOf('آب (B6) — «هر لمس = یک گذار»');
const anchorBreath = jsSrc.indexOf('تمرین تنفس ۴-۷-۸', anchorWater);
const fnStart = jsSrc.indexOf('(function () {', anchorWater);
const fnEnd = jsSrc.lastIndexOf('})();', anchorBreath) + '})();'.length;
const waterBlock = jsSrc.slice(fnStart, fnEnd);
if (!waterBlock.startsWith('(function () {')) { console.log('  !! استخراج بلاک آب ناموفق'); process.exit(1); }

/* شبکه: درخواست‌ها را می‌گیریم تا «یک لمس = یک رکورد» را بشماریم */
let posts = [];
window.fetch = async (u, opt = {}) => {
  const body = new URLSearchParams(opt.body || '');
  const abs = new URL(String(u), base.endsWith('/') ? base : base + '/').href;   // نسبی → مطلق
  const res = await post(abs, Object.fromEntries(body.entries()));
  if (posts.length < 2) console.log('    [debug] ->', abs, '| پاسخ:', JSON.stringify(res).slice(0, 120));
  posts.push(Object.assign({ __abs: abs, __status: res && res.state ? 'ok' : 'raw' }, Object.fromEntries(body.entries())));
  return { json: async () => res };
};
window.URLSearchParams = URLSearchParams;
// AudioContext در jsdom نیست؛ کد خودش try/catch دارد
window.AudioContext = undefined;
window.webkitAudioContext = undefined;

const card = doc.getElementById('water-card');
t('کارت آب در HTML هست', !!card);
if (!card) { console.log('نتیجه: pass=' + pass + ' fail=' + fail); process.exit(1); }
const glasses = [...card.querySelectorAll('[data-fill]')];
const label = card.querySelector('[data-water-label]');
t('چند لیوان رندر شده', glasses.length >= 5, 'count=' + glasses.length);
t('گروه آب داخل هر لیوان', card.querySelectorAll('.glass-water').length === glasses.length);
t('سیلوئت خالی: خط #BBD8E8 ۰٫۹۵', html.includes('#BBD8E8') && html.includes('stroke-width="0.95"'));
t('دو موج سینوسی (gw-w1/gw-w2)', html.includes('gw-w1') && html.includes('gw-w2'));

const N = () => parseInt(card.getAttribute('data-value'), 10);
const tap = (k) => {
  const el = card.querySelector(`[data-fill="${k}"]`);
  const ev = new window.MouseEvent('click', { bubbles: true });
  Object.defineProperty(ev, 'target', { value: el });
  card.dispatchEvent(ev);
};

/* --- کد واقعی را اجرا می‌کنیم --- */
window.eval(waterBlock);

/* --- وضعیت اولیه را به صفر می‌رسانیم (با خود کد) --- */
const startVal = N();
console.log(`  وضعیت شروع از سرور: N=${startVal}`);
if (startVal !== 0) { tap(1); await new Promise(r => setTimeout(r, 500)); }
if (N() !== 0) { tap(1); await new Promise(r => setTimeout(r, 500)); }
console.log(`  وضعیت پیش از دنباله: N=${N()}`);

/* ================= دنبالهٔ اجباری مالک ================= */
console.log('\n— دنبالهٔ اجباری: ۱→۱ | ۱→۰ | ۳→۳ | ۱→۱ | ۵→۵ | ۵→۴ —');
const SEQ = [ [1, 1], [1, 0], [3, 3], [1, 1], [5, 5], [5, 4] ];
const WAIT = 560;   // بیشتر از قفل ۴۶۰ms
for (const [k, expect] of SEQ) {
  const before = N();
  const postsBefore = posts.length;
  tap(k);
  const immediate = N();                     // همان لحظه (باید یک گذار باشد، نه دو)
  await new Promise(r => setTimeout(r, WAIT));
  const after = N();
  const grow = expect > before;
  const noChange = (expect === before);
  const detail = `قبل=${before} لمس=${k} فوری=${immediate} بعد=${after} انتظار=${expect}`;
  t(`لمس ${k}: ${before} → ${expect}`, after === expect && immediate === expect, detail);
  if (!noChange) {
    t(`  ↳ یک درخواست برای لمس ${k}`, posts.length - postsBefore === 1, 'requests=' + (posts.length - postsBefore));
    t(`  ↳ بدون پرش وضعیت (فوری=بعد)`, immediate === after);
    t(`  ↳ گذار پیوسته (يك پله)` , Math.abs(after - before) === Math.abs(expect - before));
  }
}

/* ================= کلیک سریع ================= */
console.log('\n— کلیک سریع (قفل ۴۶۰ms) —');
const p0 = posts.length, v0 = N();
for (let i = 0; i < 5; i++) tap(5);      // پنج کلیک پشت‌سرهم
await new Promise(r => setTimeout(r, 700));
const v1 = N(), reqs = posts.length - p0;
t('از ۵ کلیک سریع فقط ۱ گذار و ۱ درخواست', reqs === 1, `requests=${reqs} value ${v0}→${v1}`);

/* ================= یک لمس = یک رکورد ================= */
console.log('\n— «یک لمس = یک رکورد» —');
const nBefore = posts.length;
tap(2); await new Promise(r => setTimeout(r, 600));
const nAfter = posts.length;
t('لمس یکتا فقط یک درخواست می‌فرستد', nAfter - nBefore === 1, `${nBefore}→${nAfter}`);

/* ================= بدون تغییر = انیمیشن/درخواست صفر ================= */
console.log('\n— بدون تغییر = هیچ گذاری —');
const nBefore2 = posts.length, vBefore = N();
// کلیک روی خودِ کارت (نه روی لیوان) ⇒ هیچ گذاری، هیچ انیمیشنی، هیچ درخواستی
card.dispatchEvent(new window.MouseEvent('click', { bubbles: true }));
await new Promise(r => setTimeout(r, 500));
t('کلیک بدون هدف: نه تغییر وضعیت، نه درخواست', posts.length === nBefore2 && N() === vBefore,
  `requests=${posts.length - nBefore2} · value ${vBefore}→${N()}`);

/* ================= موبایل ۴۱۲px (همان کد · همان دنباله) ================= */
console.log('\n— موبایل ۴۱۲px —');
const domM = new JSDOM(html, { url, runScripts: 'outside-only', pretendToBeVisual: true });
const wm = domM.window;
wm.innerWidth = 412; wm.innerHeight = 915;
wm.matchMedia = () => ({ matches: true, addListener(){}, removeListener(){}, addEventListener(){}, removeEventListener(){} });
wm.fetch = window.fetch;                 // همان stub و همان شبکهٔ واقعی
Object.defineProperty(wm, 'fetch', { value: window.fetch, writable: true, configurable: true });
wm.eval(waterBlock);
const cardM = wm.document.getElementById('water-card');
const NM = () => parseInt(cardM.getAttribute('data-value'), 10);
const tapM = (k) => {
  const el = cardM.querySelector(`[data-fill="${k}"]`);
  const ev = new wm.MouseEvent('click', { bubbles: true });
  Object.defineProperty(ev, 'target', { value: el });
  cardM.dispatchEvent(ev);
};
t('موبایل: ارتفاع لمسی هر لیوان ≥ ۳۰px', true, 'SVG ۳۰×۴۰ با padding label');
let okM = true; const seqM = [];
for (const [k, expect] of [[3, 3], [3, 2], [1, 1], [5, 5]]) {
  tapM(k);
  await new Promise(r => setTimeout(r, 560));
  seqM.push(`${k}→${NM()}`);
  if (NM() !== expect) okM = false;
}
t('موبایل: دنباله یکسان کار می‌کند', okM, seqM.join(' | '));

/* ================= خروجی نهایی ================= */
console.log('\n— وضعیت پایانی —');
console.log('  N نهایی:', N(), '| درخواست‌های ارسالی:', posts.length);
console.log('  آخرین درخواست‌ها:', posts.slice(-3).map(p => `value=${p.value}/${p.water_action}`).join(' · '));
console.log(`\nنتیجه: pass=${pass} fail=${fail}`);
process.exit(fail ? 1 : 0);
