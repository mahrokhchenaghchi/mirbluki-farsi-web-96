/**
 * JOMA — جاروب همهٔ صفحه‌ها × سه نقش (آزمون v10)
 * -------------------------------------------------------------------------
 * سه نقش: کاربری (s1) · مشاور (ostad) · مدیر (modir)
 * و دو حساب آزمون دیگر (tab · cgood) برای پوشش صفحه‌های آب و هم‌مسیر.
 * معیار قبولی: هیچ Fatal / Warning / Notice / HTTP 5xx در هیچ صفحه‌ای نباشد.
 * اجرا: node sweep-roles.mjs http://127.0.0.1:8140/test
 */
const BASE = (process.argv[2] || 'http://127.0.0.1:8140/test').replace(/\/$/, '');
const PASSW = process.argv[3] || 'JomaTest1405!';
const users = ['s1', 'ostad', 'modir', 'tab', 'cgood'];
const routes = ['home', 'login', 'register', 'forgot', 'dashboard', 'today', 'mood', 'jooje', 'plan',
  'reports', 'journal', 'library', 'learn', 'periods', 'period', 'profile', 'settings', 'about',
  'support', 'rights', 'admin_console', 'admin_recovery', 'hammasir', 'hammasir_providers',
  'hammasir_admins', 'hammasir_client', 'hammasir_provider', 'hammasir_chat'];

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

let checked = 0, bad = 0;
const problems = [];
for (const u of users) {
  const s = new S();
  const lr = await s.login(u);
  if (!/خروج|داشبورد|خانه/.test(lr.text)) problems.push(`${u}: ورود موفق نبود`);
  for (const p of routes) {
    const r = await s.follow('/index.php?p=' + p);
    checked++;
    const issues = [];
    if (/Fatal error|Parse error|Uncaught/.test(r.text)) issues.push('FATAL');
    if (/Warning:|Notice:|Deprecated:/.test(r.text)) issues.push('PHPMSG');
    if (r.status >= 500) issues.push('HTTP' + r.status);
    if (issues.length) { bad++; problems.push(`${u} → ${p}: ${issues.join(',')}`); }
  }
}
for (const p of problems) console.log('  ✗ ' + p);
console.log(`checked=${checked}  bad=${bad}`);
process.exit(bad ? 1 : 0);
