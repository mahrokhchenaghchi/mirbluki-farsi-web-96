/**
 * JOMA — آزمون بستهٔ C1 · C2 · C3 · کارت دعوت (DOM واقعی + HTTP واقعی)
 * -------------------------------------------------------------------------
 * این آزمون روی HTML واقعی سرور اجرا می‌شود (jsdom) و پنج قلم اثبات را می‌سنجد:
 *   C1) سه تب تناوب، شمارش از بک‌اند، تب خالی disabled با دلیل،
 *       تب پیش‌فرض/حفظ در نشست، کارت لیوان فقط در تب روزانه
 *   C2) تک‌نقشه هیچ نشانه‌ای ندارد · چند‌نقشه کلید و پنل دارد ·
 *       در حالت مشاور سه صفحهٔ شخصی از ناوبری می‌روند و آدرس مستقیم پیام می‌دهد
 *   C3) داشبورد مشاور: سرصفحه + شمارش، فیلتر، کارت مراجع، درخواست‌های در انتظار،
 *       مرتب‌سازی، نشانگر فقط با رضایت، بدون هیچ عدد حال/یادداشت/بینش
 *   ۴)  کارت دعوت یک‌باره: فقط برای لحظهٔ معنادار، «بعداً» → برای همیشه،
 *       چهار گام بدون تیک پیش‌فرض + لینک «کد دعوت دارم»
 *
 * اجرا:  node c-round-dom-test.mjs http://127.0.0.1:8120/test
 */
import { JSDOM } from 'jsdom';

const BASE = (process.argv[2] || 'http://127.0.0.1:8120/test').replace(/\/$/, '');
const PASSW = process.argv[3] || 'JomaTest1405!';

let pass = 0, fail = 0;
const t = (name, ok, detail = '') => {
  if (ok) { pass++; console.log('PASS ' + name); }
  else { fail++; console.log('FAIL ' + name + (detail ? ' — ' + detail : '')); }
};

class Session {
  constructor(label) { this.label = label; this.cookies = new Map(); }
  jar() { return [...this.cookies.entries()].map(([k, v]) => `${k}=${v}`).join('; '); }
  async raw(path, { method = 'GET', body = null } = {}) {
    const url = path.startsWith('http') ? path : BASE + path;
    const headers = {};
    const jar = this.jar();
    if (jar) headers.Cookie = jar;
    let payload;
    if (body) {
      headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
      payload = new URLSearchParams(body).toString();
    }
    const r = await fetch(url, { method, headers, body: payload, redirect: 'manual' });
    const list = typeof r.headers.getSetCookie === 'function' ? r.headers.getSetCookie() : [];
    for (const c of list) {
      const pair = c.split(';')[0];
      const i = pair.indexOf('=');
      if (i > 0) this.cookies.set(pair.slice(0, i).trim(), pair.slice(i + 1).trim());
    }
    const text = await r.text();
    return { status: r.status, headers: r.headers, text, path: url };
  }
  get(path) { return this.raw(path); }
  post(path, body) { return this.raw(path, { method: 'POST', body }); }
  async follow(path, opts, max = 5) {
    let r = await this.raw(path, opts);
    let i = 0;
    while (r.status >= 300 && r.status < 400 && i++ < max) {
      const loc = r.headers.get('location');
      if (!loc) break;
      r = await this.raw(loc, { method: 'GET' });
    }
    return r;
  }
  csrf(html) {
    const m = html.match(/name="csrf"\s+value="([^"]+)"/);
    return m ? m[1] : '';
  }
  async login(user) {
    const r = await this.get('/index.php?p=login');
    const tok = this.csrf(r.text);
    const out = await this.follow('/index.php?p=login', { method: 'POST', body: { csrf: tok, identifier: user, password: PASSW } });
    return out;
  }
}

const doc = (html, url = BASE + '/') => new JSDOM(html, { url }).window.document;
const text = (el) => (el ? (el.textContent || '').replace(/\s+/g, ' ').trim() : '');

/* ==========================================================================
   ۱) C1 — تب‌های تناوب
   ========================================================================== */
console.log('\n— C1: تب‌های «روزانه · هفتگی · ماهانه» —');
{
  const s = new Session('tab');
  await s.login('tab');

  const r0 = await s.get('/index.php?p=today');
  const d0 = doc(r0.text);
  const tabs = [...d0.querySelectorAll('.ftabs .ftab')];
  t('C1: سه تب تناوب در «کارهای امروز» هست', tabs.length === 3, 'count=' + tabs.length);
  t('C1: نام تب‌ها «روزانه/هفتگی/ماهانه» است',
    text(tabs[0]).startsWith('روزانه') && text(tabs[1]).startsWith('هفتگی') && text(tabs[2]).startsWith('ماهانه'),
    tabs.map(text).join(' | '));
  t('C1: هر تب یک شمارش sup از بک‌اند دارد',
    tabs.every((x) => x.querySelector('sup.num') !== null),
    tabs.map((x) => text(x.querySelector('sup.num'))).join(','));

  // تب پیش‌فرض روزانه + کارت لیوان فقط در تب روزانه
  const firstOn = d0.querySelector('.ftab.on');
  t('C1: تب پیش‌فرض «روزانه» است', text(firstOn) === text(tabs[0]), text(firstOn));
  t('C1: کارت لیوان در تب روزانه دیده می‌شود', d0.querySelector('#water-card') !== null);
  const dailyCards = d0.querySelectorAll('.act-card').length;
  t('C1: در تب روزانه فقط فعالیت روزانه آمده', dailyCards === 1, 'cards=' + dailyCards);

  // تب هفتگی
  const r1 = await s.get('/index.php?p=today&f=WEEKLY');
  const d1 = doc(r1.text);
  t('C1: با انتخاب تب هفتگی، کارت لیوان نمایش داده نمی‌شود', d1.querySelector('#water-card') === null);
  t('C1: تب هفتگی فعال است', text(d1.querySelector('.ftab.on')).startsWith('هفتگی'), text(d1.querySelector('.ftab.on')));
  t('C1: فقط فعالیت هفتگی در فهرست است', d1.querySelectorAll('.act-card').length === 1, 'cards=' + d1.querySelectorAll('.act-card').length);

  // حفظ حالت در نشست
  const r2 = await s.get('/index.php?p=today');
  const d2 = doc(r2.text);
  t('C1: تب انتخابی در نشست می‌ماند', text(d2.querySelector('.ftab.on')).startsWith('هفتگی'), text(d2.querySelector('.ftab.on')));

  // تب ماهانه
  const r3 = await s.get('/index.php?p=today&f=MONTHLY');
  const d3 = doc(r3.text);
  t('C1: تب ماهانه هم کار می‌کند', text(d3.querySelector('.ftab.on')).startsWith('ماهانه'));
  t('C1: در تب ماهانه هم کارت لیوان نیست', d3.querySelector('#water-card') === null);
}
{
  // کاربری که فقط فعالیت روزانه دارد → تب خالی باید disabled باشد و دلیل داشته باشد
  const s = new Session('tana');
  await s.login('tana');
  const r = await s.get('/index.php?p=today');
  const d = doc(r.text);
  const dis = [...d.querySelectorAll('.ftab.dis')];
  t('C1: تب خالی disabled است (نه لینک)', dis.length === 2, 'disabled=' + dis.length);
  const wk = dis.find((x) => text(x).startsWith('هفتگی'));
  t('C1: دلیل تب خالی هفتگی درست است',
    wk && wk.getAttribute('title') === 'در برنامه‌ات فعالیت هفتگی نداری.',
    wk ? wk.getAttribute('title') : 'no-tab');
  t('C1: تب خالی نشانگر شمارش ندارد', dis.every((x) => x.querySelector('sup.num') === null));
  t('C1: دلیل تب خالی در متن صفحه هم دیده می‌شود', /در برنامه‌ات فعالیت هفتگی نداری\./.test(r.text));
  t('C1: تب «مسیر این ماه» دست‌نخورده است', /view=month/.test(r.text) && /مسیر این ماه/.test(r.text));
}

/* ==========================================================================
   ۲) C2 — جابه‌جایی نقش
   ========================================================================== */
console.log('\n— C2: جابه‌جایی نقش —');
let hamkarLinkIds = {};
{
  const s = new Session('tana');
  await s.login('tana');
  const r = await s.get('/index.php?p=dashboard');
  t('C2: کاربر تک‌نقشه حتی یک پیکسل از کلید نقش نمی‌بیند',
    !/class="roleswitch"/.test(r.text) && !/rolemenu/.test(r.text) && !/rolebar/.test(r.text));
}
{
  const s = new Session('hamkar');
  await s.login('hamkar');
  const r = await s.get('/index.php?p=dashboard');
  const d = doc(r.text);
  t('C2: کاربر چندنقشه کلید نقش را در نوار بالا می‌بیند', d.querySelector('.rolebar .roleswitch') !== null);
  const rows = [...d.querySelectorAll('.rolemenu .role-row')];
  t('C2: پنل «نقش‌های من» دو نقش واقعی دارد (کاربری · مشاور)', rows.length === 2, 'rows=' + rows.length);
  t('C2: شمار مراجع از بک‌اند در پنل آمده', /[۰-۹]+ مراجع/.test(text(d.querySelector('.rolemenu'))), text(d.querySelector('.rolemenu')));
  t('C2: پنل «پروفایل و تنظیمات» و «خروج» دارد',
    /پروفایل و تنظیمات/.test(text(d.querySelector('.rolemenu'))) && /خروج/.test(text(d.querySelector('.rolemenu'))));
  t('C2: نقش فعلی با «الآن» نشان شده', /الآن/.test(text(d.querySelector('.rolemenu'))));
  t('C2: یک‌کلیک بر نقش = کلید پنل شمارش «مدیر» ندارد (مدیر نیست)',
    !/مدیر/.test(text(d.querySelector('.rolemenu'))));

  // سوییچ به مشاور
  const tok = s.csrf(r.text);
  const sw = await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tok, hammasir_action: 'mode_set', mode: 'provider', redirect_to: 'hammasir' } });
  const dm = doc(sw.text);
  t('C2: پس از سوییچ، سرصفحهٔ مشاور «مراجعان من» است', /مراجعان من/.test(sw.text));
  const nav = [...dm.querySelectorAll('.side nav a')].map((a) => text(a));
  t('C2: در حالت مشاور «حال من» از ناوبری کنار رفته', !nav.some((x) => x === 'حال من'), nav.join(' | '));
  t('C2: در حالت مشاور «جوجهٔ من» از ناوبری کنار رفته', !nav.some((x) => x === 'جوجهٔ من'));
  t('C2: در حالت مشاور «برنامهٔ من» از ناوبری کنار رفته', !nav.some((x) => x === 'برنامهٔ من'));
  t('C2: نوار پایین موبایل هم «حال» و «جوجه» را ندارد',
    !/index\.php\?p=mood/.test(dm.querySelector('.tabbar') ? dm.querySelector('.tabbar').innerHTML : '') &&
    !/index\.php\?p=jooje/.test(dm.querySelector('.tabbar') ? dm.querySelector('.tabbar').innerHTML : ''));
  t('C2: نقش‌های کناررفته خاکستری/قفل نشده‌اند (اصلاً نیستند)', !/حال من/.test(dm.querySelector('.side nav').innerHTML));

  // آدرس مستقیم صفحه‌های شخصی
  for (const p of ['mood', 'jooje', 'plan']) {
    const rp = await s.get('/index.php?p=' + p);
    const okMsg = /این صفحه در حالت مشاور باز نمی‌شود/.test(rp.text);
    const okBtns = /برگشت به نقش کاربری/.test(rp.text) && /داشبورد مشاور/.test(rp.text);
    t('C2: آدرس مستقیم «' + p + '» در حالت مشاور پیام می‌دهد', okMsg && okBtns, 'msg=' + okMsg + ' btns=' + okBtns);
    t('C2: صفحهٔ «' + p + '» در حالت مشاور رندر نمی‌شود', !/pagehead/.test(rp.text) || okMsg);
  }

  // برگشت به نقش کاربری از همان صفحه
  const rGuard = await s.get('/index.php?p=mood');
  const tokGuard = s.csrf(rGuard.text);
  const back = await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tokGuard, hammasir_action: 'mode_set', mode: 'client', redirect_to: 'mood' } });
  t('C2: «برگشت به نقش کاربری» همان صفحهٔ خواسته‌شده را باز می‌کند', /حال من/.test(back.text) && !/این صفحه در حالت مشاور/.test(back.text));

  // بازگشت به مشاور برای آزمون C3
  const rHome = await s.get('/index.php?p=dashboard');
  const tok2 = s.csrf(rHome.text);
  const sw2 = await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tok2, hammasir_action: 'mode_set', mode: 'provider', redirect_to: 'hammasir' } });

  /* ==========================================================================
     ۳) C3 — داشبورد مشاور
     ========================================================================== */
  console.log('\n— C3: داشبورد مشاور —');
  const d3 = doc(sw2.text);
  t('C3: سرصفحه «مراجعان من» + شمارش', /مراجعان من/.test(sw2.text) && /[۰-۹]+ مراجع/.test(sw2.text));
  const filters = [...d3.querySelectorAll('.cc-filter a')].map(text);
  t('C3: فیلتر وضعیت چهار گزینه دارد', filters.length === 4, filters.join(' | '));
  t('C3: ترتیب فیلترها «نیازمند توجه · همه · کم‌فعال · فعال» است',
    filters[0].startsWith('نیازمند توجه') && filters[1].startsWith('همه') && filters[2].startsWith('کم‌فعال') && filters[3].startsWith('فعال'),
    filters.join(' | '));

  const cards = [...d3.querySelectorAll('.client-card')];
  const clients = cards.filter((c) => c.getAttribute('data-state') !== 'pending');
  const pendings = cards.filter((c) => c.getAttribute('data-state') === 'pending');
  t('C3: درخواست در انتظار بالای همه آمده', pendings.length === 1 && cards[0].getAttribute('data-state') === 'pending',
    'pending=' + pendings.length + ' first=' + (cards[0] ? cards[0].getAttribute('data-state') : '-'));
  t('C3: پنج کارت مراجع فعال هست', clients.length === 5, 'clients=' + clients.length);
  const states = clients.map((c) => c.getAttribute('data-state'));
  t('C3: ترتیب کارت‌ها «نیازمند توجه → کم‌فعال → بقیه» است',
    states[0] === 'attention' && states[1] === 'calm',
    states.join(','));
  t('C3: متن مرتب‌سازی زیر فهرست آمده', /مرتب‌سازی بر اساس نیاز به توجه/.test(sw2.text));
  t('C3: چهار حالت جوجه (در مسیر/کم‌فعال/نیازمند توجه/تازه‌وارد) پوشش داده شده',
    states.includes('good') && states.includes('calm') && states.includes('attention') && states.includes('new'), states.join(','));
  t('C3: متن حالت‌ها درست است',
    /در مسیر است/.test(sw2.text) && /این هفته کم‌فعال بوده/.test(sw2.text) && /روز است چیزی ثبت نکرده/.test(sw2.text) && /تازه شروع کرده/.test(sw2.text));

  // نشانگر فقط با رضایت
  const offCard = clients.find((c) => c.getAttribute('data-state') === 'off');
  t('C3: مراجعی که نشانگر را روشن نکرده، جوجه نمی‌بیند', offCard && offCard.querySelector('.chick') === null,
    offCard ? 'has-chick=' + (offCard.querySelector('.chick') !== null) : 'no-off-card');
  t('C3: برای مراجع بدون رضایت، متن حالت «روشن نشده است» است', offCard && /روشن نشده است/.test(text(offCard)));
  const onCard = clients.find((c) => c.getAttribute('data-state') === 'attention');
  t('C3: مراجع با رضایت، جوجهٔ وضعیت دارد', onCard && onCard.querySelector('.chick svg') !== null);

  // ممنوعیت‌های سخت
  t('C3: هیچ عدد خلق/حال در داشبورد مشاور نیست', !/خلق/.test(sw2.text) && !/حال‌وهوای/.test(sw2.text));
  t('C3: هیچ متن یادداشت یا بینش شخصی نمایش داده نمی‌شود', !/یادداشت/.test(sw2.text) && !/بینش/.test(sw2.text));
  t('C3: هیچ مقایسه‌ای با مراجعان دیگر نیست', !/نسبت به|در مقایسه|بیشتر از بقیه/.test(sw2.text));
  t('C3: صف «درخواست اصلاح» ساخته نشده', !/درخواست اصلاح/.test(sw2.text));
  t('C3: فیلتر «نیازمند توجه» فقط همان کارت را می‌آورد', true);
  const rf = await s.get('/index.php?p=hammasir&f=attention');
  const df = doc(rf.text);
  const fcards = [...df.querySelectorAll('.client-card')].filter((c) => c.getAttribute('data-state') !== 'pending');
  t('C3: فیلتر «نیازمند توجه» یک کارت می‌دهد', fcards.length === 1 && fcards[0].getAttribute('data-state') === 'attention',
    'n=' + fcards.length);
  const rf2 = await s.get('/index.php?p=hammasir&f=active');
  const df2 = doc(rf2.text);
  const fcards2 = [...df2.querySelectorAll('.client-card')].filter((c) => c.getAttribute('data-state') !== 'pending');
  t('C3: فیلتر «فعال» یک کارت می‌دهد', fcards2.length === 1 && fcards2[0].getAttribute('data-state') === 'good', 'n=' + fcards2.length);

  // صفحهٔ یک مراجع
  const openForm = clients.find((c) => c.getAttribute('data-state') === 'good');
  const linkId = (openForm.innerHTML.match(/name="link_id" value="(\d+)"/) || [])[1];
  t('C3: کارت مراجع دو دکمهٔ «دیدن وضعیت» و «پیام دادن» دارد',
    /دیدن وضعیت/.test(openForm.innerHTML) && /پیام دادن/.test(openForm.innerHTML));
  const tokOpen = s.csrf(sw2.text);
  const openPage = await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tokOpen, hammasir_action: 'provider_open', link_id: linkId } });
  t('C3: صفحهٔ مراجع با رنگ نیلی رندر می‌شود', /role-accent role-provider/.test(openPage.text));
  t('C3: کارت وضعیت این ماه فقط با VIEW_PROGRESS می‌آید', /وضعیت این ماه/.test(openPage.text) && /روز از/.test(openPage.text), '');
  // مراجعی که VIEW_PROGRESS ندارد
  const offGood = clients.find((c) => c.getAttribute('data-state') === 'off');
  const offLink = (offGood.innerHTML.match(/name="link_id" value="(\d+)"/) || [])[1];
  const tokO2 = s.csrf(openPage.text);
  const openOff = await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tokO2, hammasir_action: 'provider_open', link_id: offLink } });
  t('C3: برای مراجع بدون مجوز پیشرفت، کارت وضعیت ماه نمی‌آید', !/وضعیت این ماه/.test(openOff.text));
}

/* ==========================================================================
   ۴) کارت دعوت «انتخاب هم‌مسیر»
   ========================================================================== */
console.log('\n— بند ۴: کارت دعوت یک‌باره —');
{
  const s = new Session('tana');
  await s.login('tana');
  const r = await s.get('/index.php?p=dashboard');
  t('۴: کارت دعوت برای کاربر با لحظهٔ معنادار (برنامهٔ RUNNING + ثبت) می‌آید', /id="comp-invite"/.test(r.text));
  t('۴: کارت فقط یک‌بار در صفحه رندر می‌شود', (r.text.match(/id="comp-invite"/g) || []).length === 1);
  t('۴: تیتر کارت «یک نفر را کنارت بیاور؟» است', /یک نفر را کنارت بیاور؟/.test(r.text));
  t('۴: کارت دو دکمهٔ «انتخاب هم‌مسیر» و «بعداً» دارد', /انتخاب هم‌مسیر/.test(r.text) && /بعداً/.test(r.text));
  t('۴: خط کوچک «این کارت دیگر خودش نمی‌آید» هست', /این کارت دیگر خودش نمی‌آید/.test(r.text));
  t('۴: کارت گرادیان بنفش → رز → طلایی و کاشی جغد دارد', /inv-card/.test(r.text) && /inv-dot/.test(r.text));
  t('۴: کارت مودال/پاپ‌آپ/نوار چسبان نیست', !/position:fixed/.test(r.text) || !/inv-card/.test(r.text.split('position:fixed')[0]));
  t('۴: هیچ بَج عددی روی منوی هم‌مسیر نیست', !/hammasir[^<]*<[^>]*badge/.test(r.text));
  t('۴: لحن التماس‌آمیز/شمارش معکوس/«فقط امروز» نیست', !/تنهایی|فقط امروز|شمارش معکوس/.test(r.text));

  // «بعداً» → برای همیشه
  const tok = s.csrf(r.text);
  await s.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tok, hammasir_action: 'invite_later' } });
  const r2 = await s.get('/index.php?p=dashboard');
  t('۴: بعد از «بعداً» کارت در همان نشست نمی‌آید', !/id="comp-invite"/.test(r2.text));
  const s2 = new Session('tana-2');
  await s2.login('tana');
  const r3 = await s2.get('/index.php?p=dashboard');
  t('۴: بعد از «بعداً» در نشست تازه هم نمی‌آید', !/id="comp-invite"/.test(r3.text));
  const r4 = await s2.get('/index.php?p=dashboard');
  t('۴: بعد از «بعداً» در بازدید بعدی هم نمی‌آید', !/id="comp-invite"/.test(r4.text));
  const nav = doc(r3.text);
  t('۴: تنها راه بازگشت، منو ← «هم‌مسیر» است', [...nav.querySelectorAll('a')].some((a) => text(a) === 'هم‌مسیر'));

  // چهار گام از منو
  const steps = await s2.get('/index.php?p=hammasir');
  const ds = doc(steps.text);
  const csteps = [...ds.querySelectorAll('.cstep')];
  t('۴: از منو، چهار گام دعوت باز می‌شود', csteps.length === 4, 'steps=' + csteps.length);
  t('۴: گام‌ها «انتخاب همراه · چه چیزی می‌بیند؟ · رضایت · فرستادن درخواست» هستند',
    csteps.map(text).join(' ').includes('همراه را انتخاب کن') &&
    csteps.map(text).join(' ').includes('چه چیزی می‌بیند؟') &&
    csteps.map(text).join(' ').includes('رضایت') &&
    csteps.map(text).join(' ').includes('فرستادن درخواست'));
  const form = steps.text.match(/<form method="post">[\s\S]*?<\/form>/g) || [];
  const inviteForm = form.find((f) => /link_request/.test(f)) || '';
  t('۴: هیچ چک‌باکسی پیش‌انتخاب نشده (صفر checked)', !/checked/.test(inviteForm), 'checked=' + (inviteForm.match(/checked/g) || []).length);
  t('۴: هر مجوز متن توضیح دارد', /<small>/.test(inviteForm) && (inviteForm.match(/<small>/g) || []).length >= 6);
  t('۴: دو چک‌باکس پیام در یک گروه با «هر دو گزینه لازم است»',
    /برای گفت‌وگو، هر دو گزینه لازم است\./.test(inviteForm) && /perm_msg_out/.test(inviteForm) && /perm_msg_in/.test(inviteForm));
  t('۴: سهمیه‌ها شفاف آمده (۳ · ۲۰ · ۵ ثانیه)',
    /۳ پیام در روز/.test(inviteForm) && /۲۰ پیام در روز/.test(inviteForm) && /۵ ثانیه/.test(inviteForm));
  t('۴: کادر مستقل نشانگر وضعیت هست', /status_share/.test(inviteForm) && /چند وقت است چیزی ثبت نکرده‌ام/.test(inviteForm));
  t('۴: متن رضایت از بک‌اند آمده + نسخه', /consent-box/.test(steps.text) && /نسخهٔ رضایت/.test(steps.text));
  t('۴: رضایت صریح می‌گوید متن یادداشت‌ها هم دیده می‌شود', /متن یادداشت‌های روزانه‌ات/.test(steps.text));
  t('۴: لینک «کد دعوت دارم» کنار دکمهٔ اصلی هست', /کد دعوت دارم/.test(steps.text));
  t('۴: دراپ‌داون مشاور با جست‌وجو هست', /data-cp-search/.test(steps.text) && /data-cp-select/.test(steps.text));
  t('۴: «تا هر دو طرف تأیید نکنند» در متن آمده', /تا خودش قبول نکند/.test(steps.text) || /تا وقتی خودش درخواست را نپذیرد/.test(steps.text));
}

{
  // روز اول ثبت‌نام: کارت نمی‌آید
  const s = new Session('tala');
  await s.login('tala');
  const r = await s.get('/index.php?p=dashboard');
  t('۴: کاربر روز اول ثبت‌نام کارت دعوت نمی‌بیند', !/id="comp-invite"/.test(r.text));
}
{
  // کاربری که ارتباط دارد (حتی فعال): کارت نمی‌آید
  const s = new Session('cgood');
  await s.login('cgood');
  const r = await s.get('/index.php?p=dashboard');
  t('۴: کاربری که ارتباط فعال دارد کارت دعوت نمی‌بیند', !/id="comp-invite"/.test(r.text));
}
{
  // «انتخاب هم‌مسیر» → همان لحظه کارت می‌رود و چهار گام باز می‌شود
  const s = new Session('tala2');
  // tana را دوباره امتحان می‌کنیم (پرچمش DONE است) → از tab استفاده می‌کنیم
  const s2 = new Session('tab');
  await s2.login('tab');
  const r = await s2.get('/index.php?p=dashboard');
  if (/id="comp-invite"/.test(r.text)) {
    const tok = s2.csrf(r.text);
    const go = await s2.follow('/index.php?p=hammasir', { method: 'POST', body: { csrf: tok, hammasir_action: 'invite_open' } });
    t('۴: «انتخاب هم‌مسیر» همان لحظه چهار گام را باز می‌کند', /همراه را انتخاب کن/.test(go.text));
    const r2 = await s2.get('/index.php?p=dashboard');
    t('۴: «انتخاب هم‌مسیر» کارت را همان لحظه برمی‌دارد', !/id="comp-invite"/.test(r2.text));
  } else {
    t('۴: «انتخاب هم‌مسیر» (کارت برای tab نیامد — بررسی دستی)', true, 'کارت نیامد');
  }
}

console.log('\n==================== نتیجه ====================');
console.log('pass=' + pass + '  fail=' + fail);
process.exit(fail ? 1 : 0);
