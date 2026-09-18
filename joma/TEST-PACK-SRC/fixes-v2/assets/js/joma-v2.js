/* JOMA v2 — تعامل‌های کوچک پوسته (بدون منطق دامنه) */
(function () {
  function ready(fn){ if(document.readyState!=='loading') fn(); else document.addEventListener('DOMContentLoaded',fn); }
  ready(function () {
    // نمایش/پنهان‌کردن رمز
    document.querySelectorAll('[data-pass-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var inp = document.getElementById(btn.getAttribute('data-pass-toggle'));
        if (!inp) return;
        var show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'پنهان‌کردن رمز' : 'نمایش رمز');
      });
    });
    // کپی شمارهٔ پشتیبانی
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var txt = btn.getAttribute('data-copy') || '';
        var done = function(){ var old=btn.textContent; btn.textContent='کپی شد'; setTimeout(function(){ btn.textContent=old; }, 1500); };
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done, done);
        else done();
      });
    });
    // برگهٔ «بیشتر» در موبایل
    var sheet = document.getElementById('mob-sheet');
    if (sheet) {
      document.querySelectorAll('[data-more]').forEach(function (a) {
        a.addEventListener('click', function (e) { e.preventDefault(); sheet.classList.add('open'); });
      });
      document.querySelectorAll('[data-more-close]').forEach(function (b) {
        b.addEventListener('click', function () { sheet.classList.remove('open'); });
      });
      sheet.addEventListener('click', function (e) { if (e.target === sheet) sheet.classList.remove('open'); });
    }
  });
})();

/* ---- هماهنگ‌کردن نمودارها با طرح تازه (فقط رنگ/فونت؛ هیچ داده‌ای عوض نمی‌شود) ---- */
(function () {
  function recolor() {
    if (typeof Chart === 'undefined' || !Chart.getChart) return;
    var ink = '#14382E', ink2 = '#5C7A6C', grid = 'rgba(20,56,46,.10)';
    var fam = '"Vazirmatn","IRANSans",Tahoma,system-ui,sans-serif';
    document.querySelectorAll('canvas').forEach(function (cv) {
      var ch = null;
      try { ch = Chart.getChart(cv); } catch (e) { ch = null; }
      if (!ch || !ch.options) return;
      try {
        var o = ch.options;
        if (o.plugins && o.plugins.legend && o.plugins.legend.labels) {
          o.plugins.legend.labels.color = ink2;
          o.plugins.legend.labels.font = { family: fam, size: 11 };
        }
        if (o.plugins && o.plugins.title) o.plugins.title.color = ink;
        if (o.plugins && o.plugins.tooltip) {
          o.plugins.tooltip.rtl = true;
          o.plugins.tooltip.bodyFont = { family: fam, size: 12 };
          o.plugins.tooltip.titleFont = { family: fam, size: 12 };
        }
        ['x', 'y'].forEach(function (ax) {
          if (o.scales && o.scales[ax]) {
            o.scales[ax].ticks = o.scales[ax].ticks || {};
            o.scales[ax].ticks.color = ink2;
            o.scales[ax].ticks.font = { family: fam, size: 10.5 };
            if (o.scales[ax].grid) o.scales[ax].grid.color = grid;
            if (o.scales[ax].title) o.scales[ax].title.color = ink2;
          }
        });
        if (typeof o.font === 'object' && o.font) o.font.family = fam;
        ch.update('none');
      } catch (e) { /* نمودار دست‌نخورده می‌ماند */ }
    });
  }
  if (document.readyState !== 'loading') setTimeout(recolor, 60);
  else document.addEventListener('DOMContentLoaded', function () { setTimeout(recolor, 60); });
})();

/* ---- ناوبری تب‌های گزارش با کلیدهای چپ/راست ---- */
(function () {
  var nav = document.querySelector('.rtabs');
  if (!nav) return;
  var tabs = Array.prototype.slice.call(nav.querySelectorAll('.rtab'));
  if (!tabs.length) return;
  nav.addEventListener('keydown', function (e) {
    var i = tabs.indexOf(document.activeElement);
    if (i < 0) return;
    if (e.key === 'ArrowLeft') { tabs[Math.min(tabs.length - 1, i + 1)].focus(); e.preventDefault(); }
    if (e.key === 'ArrowRight') { tabs[Math.max(0, i - 1)].focus(); e.preventDefault(); }
    if (e.key === 'Home') { tabs[0].focus(); e.preventDefault(); }
    if (e.key === 'End') { tabs[tabs.length - 1].focus(); e.preventDefault(); }
  });
  // تب فعال در دید بیاید (موبایل)
  var on = nav.querySelector('.rtab.on');
  if (on && on.scrollIntoView) { try { on.scrollIntoView({ block: 'nearest', inline: 'center' }); } catch (e) {} }
})();

/* ==========================================================================
   آب (B6) — سرعت + صدا + خالی‌کردن لیوان، بدون بارگذاری صفحه
   ========================================================================== */
(function () {
  var card = document.getElementById('water-card');
  if (!card) return;
  var glasses = card.querySelectorAll('[data-fill]');
  var labelEl = card.querySelector('[data-water-label]');
  var badgeEl = card.querySelector('[data-water-badge]');
  var paId = card.getAttribute('data-pa'), date = card.getAttribute('data-date');
  var busy = false;

  /* --- صدا: پیش‌فرض روشن (با کلیک کاربر مجاز است)، قابل خاموش‌کردن --- */
  var soundOn = true;
  try { soundOn = (localStorage.getItem('joma_water_sound') !== '0'); } catch (e) {}
  var sndBtn = card.querySelector('[data-water-sound]');
  function paintSound() {
    if (!sndBtn) return;
    sndBtn.classList.toggle('on', soundOn);
    sndBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
  }
  paintSound();
  if (sndBtn) sndBtn.addEventListener('click', function () {
    soundOn = !soundOn;
    try { localStorage.setItem('joma_water_sound', soundOn ? '1' : '0'); } catch (e) {}
    paintSound();
    if (soundOn) tone(660, 0.09, 0.05);
  });

  var AC = window.AudioContext || window.webkitAudioContext;
  var ac = null;
  function tone(freq, dur, gain, type) {
    if (!soundOn || !AC) return;
    try {
      if (!ac) ac = new AC();
      var o = ac.createOscillator(), g = ac.createGain();
      o.type = type || 'sine';
      o.frequency.value = freq;
      g.gain.value = gain || 0.06;
      o.connect(g); g.connect(ac.destination);
      var t = ac.currentTime;
      g.gain.setValueAtTime(g.gain.value, t);
      g.gain.exponentialRampToValueAtTime(0.0008, t + dur);
      o.start(t); o.stop(t + dur + 0.02);
    } catch (e) {}
  }
  /* صدای آب ریختن: نویز کوتاه */
  function pour() {
    if (!soundOn || !AC) return;
    try {
      if (!ac) ac = new AC();
      var len = Math.floor(ac.sampleRate * 0.18);
      var buf = ac.createBuffer(1, len, ac.sampleRate);
      var d = buf.getChannelData(0);
      for (var i = 0; i < len; i++) d[i] = (Math.random() * 2 - 1) * (1 - i / len) * 0.5;
      var src = ac.createBufferSource(); src.buffer = buf;
      var f = ac.createBiquadFilter(); f.type = 'bandpass'; f.frequency.value = 900; f.Q.value = 0.8;
      var g = ac.createGain(); g.gain.value = 0.1;
      src.connect(f); f.connect(g); g.connect(ac.destination); src.start();
    } catch (e) {}
  }

  /* --- نمایش --- */
  function paint(value, isDraft, isFinal) {
    var n = 0;
    glasses.forEach(function (el) {
      n++;
      el.classList.toggle('on', n <= value);
      var holder = el.tagName === 'BUTTON' ? el : el;
      if (!holder.getAttribute('data-svg')) return;
    });
    // بازسازی SVG لیوان‌ها از همان قالب سرور (بدون رفت‌وبرگشت)
    glasses.forEach(function (el, idx) {
      var i = idx + 1;
      var wrap = document.createElement('span');
      wrap.innerHTML = TEMPLATE(i <= value);
      var svg = wrap.querySelector('svg');
      var old = el.querySelector('svg');
      if (svg && old) old.parentNode.replaceChild(svg, old);
    });
    if (badgeEl) {
      badgeEl.textContent = isFinal ? 'قطعی' : (isDraft ? 'پیش‌نویس' : 'ثبت نشده');
      badgeEl.className = 'chip ' + (isFinal ? 'g' : (isDraft ? 'go' : 'n'));
    }
  }
  function TEMPLATE(filled) {
    var id = 'x' + Math.floor(Math.random() * 1e6);
    var body = '<svg width="30" height="40" viewBox="0 0 44 58" aria-hidden="true" style="display:block">'
      + '<defs><linearGradient id="wg' + id + '" x1="0" y1="0" x2="0" y2="1">'
      + '<stop offset="0%" stop-color="#A8DCF4"/><stop offset="55%" stop-color="#3DA8E0"/><stop offset="100%" stop-color="#1B6A9B"/>'
      + '</linearGradient><clipPath id="wc' + id + '">'
      + '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z"/></clipPath></defs>'
      + '<ellipse cx="22" cy="55.6" rx="14.5" ry="2.6" fill="rgba(20,56,46,.10)"/>'
      + (filled ? '<g clip-path="url(#wc' + id + ')"><rect x="0" y="18.6" width="44" height="42" fill="url(#wg' + id + ')"/>'
          + '<g class="wg-waves"><path class="wg-w1" d="M-16 20 q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 L84 40 L-16 40 Z" fill="#CDEBFA" opacity=".75"/>'
          + '<path class="wg-w2" d="M-16 21.4 q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 L84 42 L-16 42 Z" fill="#8FD0EF" opacity=".45"/></g></g>' : '')
      + '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z" fill="none" stroke="#BBD8E8" stroke-width=".95"/>'
      + '<ellipse cx="22" cy="50.8" rx="11.6" ry="2.5" fill="#DCEBF4" opacity=".75"/>'
      + '<ellipse cx="22" cy="5.4" rx="16.6" ry="3.5" fill="none" stroke="#9CC2DA" stroke-width="1.05"/>'
      + '<ellipse cx="22" cy="5.4" rx="14.9" ry="2.9" fill="none" stroke="#BBD8E8" stroke-width=".9"/>'
      + '<ellipse cx="15" cy="4.6" rx="4.6" ry="1.3" fill="#FFFFFF" opacity=".85"/>'
      + '<path d="M12.4 12 Q11.6 30 12.6 44" fill="none" stroke="#FFFFFF" stroke-width="1.7" opacity=".7"/>'
      + '</svg>';
    return body;
  }

  var csrf = (card.querySelector('input[name="csrf"]') || {}).value || '';

  function post(value, action, done) {
    if (busy) return;
    busy = true;
    var body = new URLSearchParams();
    body.set('csrf', csrf);
    body.set('ajax', '1');
    body.set('pa_id', paId);
    body.set('date', date);
    body.set('water_action', action);
    if (value !== null) body.set('value', String(value));
    fetch(location.pathname + '?p=today&date=' + encodeURIComponent(date), {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (d) {
      busy = false;
      if (!d || !d.state) { location.reload(); return; }
      var s = d.state;
      card.setAttribute('data-value', String(Math.round(s.value)));
      card.setAttribute('data-draft', s.is_draft ? '1' : '0');
      card.setAttribute('data-final', s.is_final ? '1' : '0');
      if (labelEl) labelEl.textContent = s.label;
      paint(Math.round(s.value), s.is_draft, s.is_final);
      var counter = document.querySelector('[data-done-count]');
      if (counter) counter.textContent = faNum(s.done_count) + ' از ' + faNum(s.total_count) + ' ثبت شده';
      done && done(s);
    }).catch(function () { busy = false; location.reload(); });
  }

  function faNum(n) { return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); }

  card.addEventListener('click', function (e) {
    var el = e.target.closest('[data-fill]');
    if (!el) return;
    var isFinal = card.getAttribute('data-final') === '1';
    if (isFinal) { tone(300, 0.08, 0.05, 'triangle'); return; }
    var i = parseInt(el.getAttribute('data-fill'), 10);
    var cur = parseInt(card.getAttribute('data-value'), 10) || 0;
    var next = (i === cur) ? (i - 1) : i;   // زدنِ آخرین لیوانِ پر = یکی کم شود
    var growing = next > cur;
    el.classList.add('bump');
    setTimeout(function () { el.classList.remove('bump'); }, 320);
    if (growing) { pour(); tone(520 + next * 70, 0.1, 0.05); }
    else { tone(280, 0.12, 0.05, 'triangle'); }
    paint(next, true, false); // نمایش فوری (خوش‌بینانه) تا پاسخ سرور
    post(next, 'draft_save');
  });

  var finForm = card.querySelector('[data-water-finalize]');
  if (finForm) finForm.addEventListener('submit', function (e) {
    e.preventDefault();
    tone(880, 0.14, 0.06);
    post(null, 'finalize', function () {
      var f = card.querySelector('[data-water-finalize]');
      if (f) f.parentNode.removeChild(f);
    });
  });
})();

/* ==========================================================================
   تمرین تنفس ۴-۷-۸ — انیمیشن + راهنمای صوتی (سه فایل صدا)
   ========================================================================== */
(function () {
  var openers = document.querySelectorAll('[data-breath-open]');
  var sheet = document.getElementById('breath-sheet');
  if (!openers.length || !sheet) return;
  var circle = document.getElementById('breath-circle');
  var phaseEl = document.getElementById('breath-phase');
  var hintEl = document.getElementById('breath-hint');
  var startBtn = document.getElementById('breath-start');
  var roundEl = document.getElementById('breath-round');
  var sndBtn = sheet.querySelector('[data-breath-sound]');
  var timer = null, round = 0, running = false;

  var soundOn = false;
  try { soundOn = (localStorage.getItem('joma_breath_sound') === '1'); } catch (e) {}
  var audio = { in: null, hold: null, out: null };
  function loadAudio() {
    if (audio.in) return;
    ['in', 'hold', 'out'].forEach(function (k) {
      var a = new Audio('assets/audio/br-' + k + '.mp3');
      a.preload = 'auto';
      audio[k] = a;
    });
  }
  function play(k) {
    if (!soundOn) return;
    loadAudio();
    try { if (audio[k]) { audio[k].currentTime = 0; audio[k].play().catch(function () {}); } } catch (e) {}
  }
  function paintSound() {
    if (!sndBtn) return;
    sndBtn.classList.toggle('on', soundOn);
    sndBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
  }
  paintSound();
  if (sndBtn) sndBtn.addEventListener('click', function () {
    soundOn = !soundOn;
    try { localStorage.setItem('joma_breath_sound', soundOn ? '1' : '0'); } catch (e) {}
    paintSound();
    if (soundOn) { loadAudio(); play('in'); }
  });

  function say(text, scale, ms, next, soundKey) {
    phaseEl.textContent = text;
    if (circle) {
      circle.style.transitionDuration = (ms / 1000) + 's';
      circle.style.transform = 'scale(' + scale + ')';
    }
    play(soundKey);
    timer = setTimeout(next, ms);
  }
  function finish() {
    running = false;
    clearTimeout(timer);
    if (phaseEl) phaseEl.textContent = 'خوب بود';
    if (hintEl) hintEl.textContent = 'تمرین تمام شد. هر وقت خواستی دوباره.';
    if (roundEl) roundEl.textContent = 'دور ۳ از ۳';
    if (circle) { circle.style.transitionDuration = '0.6s'; circle.style.transform = 'scale(1)'; }
    if (startBtn) startBtn.textContent = 'دوباره';
  }
  function oneRound() {
    if (round >= 3) { finish(); return; }
    round++;
    if (roundEl) roundEl.textContent = 'دور ' + round + ' از ۳';
    say('دم بگیر (۴)', 1.30, 4000, function () {
      say('نگه دار (۷)', 1.30, 7000, function () {
        say('بازدم آرام (۸)', 0.84, 8000, oneRound, 'out');
      }, 'hold');
    }, 'in');
  }
  openers.forEach(function (b) {
    b.addEventListener('click', function () { sheet.classList.add('open'); loadAudio(); });
  });
  sheet.querySelectorAll('[data-breath-close]').forEach(function (b) {
    b.addEventListener('click', function () { clearTimeout(timer); running = false; sheet.classList.remove('open'); });
  });
  sheet.addEventListener('click', function (e) {
    if (e.target === sheet) { clearTimeout(timer); running = false; sheet.classList.remove('open'); }
  });
  if (startBtn) startBtn.addEventListener('click', function () {
    if (running) return;
    running = true; round = 0; oneRound();
  });
})();

/* ==========================================================================
   ویزارد «حال من» — پنج قدم + قدم ششم، با نوار مراحل
   ========================================================================== */
(function () {
  var form = document.getElementById('mood-wizard');
  if (!form) return;
  var steps = form.querySelectorAll('.wstep');
  var dots = form.querySelectorAll('.wdot');
  var count = form.querySelector('[data-wcount]');
  var total = 5;                        // پنج قدم پرسش؛ قدم ششم اختیاری است
  var cur = parseInt(form.getAttribute('data-start') || '1', 10);
  if (!(cur >= 1 && cur <= 6)) cur = 1;
  var maxSeen = cur;
  var faN = function (n) { return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); };

  function answered(step) {
    var sec = form.querySelector('.wstep[data-step="' + step + '"]');
    if (!sec) return false;
    var inputs = sec.querySelectorAll('input[type=radio]');
    if (!inputs.length) return true; // قدم ششم (یادداشت) همیشه «اوکی»
    for (var i = 0; i < inputs.length; i++) if (inputs[i].checked) return true;
    return false;
  }

  function paint() {
    steps.forEach(function (s) {
      s.classList.toggle('active', parseInt(s.getAttribute('data-step'), 10) === cur);
    });
    dots.forEach(function (d) {
      var n = parseInt(d.getAttribute('data-step'), 10);
      d.classList.toggle('on', n === cur);
      d.classList.toggle('done', n < cur && (n === 6 ? true : answered(n)));
      var reachable = (n <= Math.max(maxSeen, cur));
      d.classList.toggle('locked', !reachable);
      d.setAttribute('aria-selected', n === cur ? 'true' : 'false');
    });
    if (count) {
      count.textContent = (cur === 6) ? 'قدم ششم — جملهٔ اختیاری' : ('قدم ' + faN(cur) + ' از ' + faN(total));
    }
    // فقط بخش فعال اجازهٔ ارسال با Enter دارد
    var active = form.querySelector('.wstep.active textarea, .wstep.active input[type=radio]:checked');
    if (active && window.innerWidth > 640) { try { active.focus({ preventScroll: true }); } catch (e) {} }
  }

  function go(step) {
    cur = step;
    if (cur > maxSeen) maxSeen = cur;
    paint();
    var sec = form.querySelector('.wstep[data-step="' + cur + '"]');
    if (sec && sec.scrollIntoView) { try { sec.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); } catch (e) {} }
  }

  form.addEventListener('click', function (e) {
    if (e.target.closest('[data-wnext]')) {
      if (cur < 6) {
        if (cur <= 5 && !answered(cur)) {
          var sec = form.querySelector('.wstep[data-step="' + cur + '"]');
          if (sec) sec.classList.add('shake');
          setTimeout(function () { if (sec) sec.classList.remove('shake'); }, 400);
          return;
        }
        go(cur + 1);
      }
      return;
    }
    if (e.target.closest('[data-wback]')) { if (cur > 1) go(cur - 1); return; }
    var dot = e.target.closest('.wdot');
    if (dot) {
      var n = parseInt(dot.getAttribute('data-step'), 10);
      if (n <= Math.max(maxSeen, cur)) go(n);
      return;
    }
    var opt = e.target.closest('.wopt');
    if (opt) {
      var group = opt.parentNode;
      group.querySelectorAll('.wopt').forEach(function (o) { o.classList.remove('on'); });
      opt.classList.add('on');
      var secStep = parseInt(opt.closest('.wstep').getAttribute('data-step'), 10);
      // کمی مکث تا کاربر انتخابش را ببیند، بعد خودکار برو قدم بعد
      setTimeout(function () { if (answered(secStep) && cur === secStep && cur < 6) go(cur + 1); }, 260);
    }
  });

  // اگر کاربر با کلید انتخابی را عوض کرد
  form.addEventListener('change', function (e) {
    if (e.target.type !== 'radio') return;
    var opt = e.target.closest('.wopt');
    if (!opt) return;
    opt.parentNode.querySelectorAll('.wopt').forEach(function (o) { o.classList.remove('on'); });
    opt.classList.add('on');
    paint();
  });

  paint();
})();

/* ==========================================================================
   ویزارد «ثبت‌نام» — چهار قدم با خلاصهٔ پایانی
   ========================================================================== */
(function () {
  var form = document.getElementById('reg-wizard');
  if (!form) return;
  var steps = form.querySelectorAll('.wstep');
  var dots = form.querySelectorAll('.wdot');
  var count = form.querySelector('[data-wcount]');
  var cur = parseInt(form.getAttribute('data-start') || '1', 10);
  if (!(cur >= 1 && cur <= 4)) cur = 1;
  var maxSeen = cur;
  var faN = function (n) { return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); };
  function sec(n) { return form.querySelector('.wstep[data-step="' + n + '"]'); }

  function valid(n) {
    var s = sec(n);
    if (!s) return true;
    var ok = true;
    s.querySelectorAll('input,select').forEach(function (el) {
      if (el.type === 'checkbox') return;
      if (el.closest('[data-reg-gate]')) return;          // کد امنیتی در سرور بررسی می‌شود
      if (!el.checkValidity()) ok = false;
    });
    // تطابق دو رمز
    if (n === 3) {
      var p = form.querySelector('input[name=password]'), c = form.querySelector('input[name=confirm]');
      if (p && c && p.value !== c.value) ok = false;
    }
    return ok;
  }
  function markInvalid(n) {
    var s = sec(n);
    if (!s) return;
    s.classList.add('shake');
    setTimeout(function () { s.classList.remove('shake'); }, 420);
    var bad = null;
    s.querySelectorAll('input,select').forEach(function (el) { if (!bad && el.checkValidity && !el.checkValidity()) bad = el; });
    if (bad) { try { bad.focus(); } catch (e) {} }
  }
  function paint() {
    steps.forEach(function (s) { s.classList.toggle('active', parseInt(s.getAttribute('data-step'), 10) === cur); });
    dots.forEach(function (d) {
      var n = parseInt(d.getAttribute('data-step'), 10);
      d.classList.toggle('on', n === cur);
      d.classList.toggle('done', n < cur);
      d.classList.toggle('locked', n > Math.max(maxSeen, cur));
    });
    if (count) count.textContent = 'قدم ' + faN(cur) + ' از ۴';
    if (cur === 4) summary();
    var first = sec(cur) && sec(cur).querySelector('input,select');
    if (first) { try { first.focus({ preventScroll: true }); } catch (e) {} }
  }
  function val(name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? (el.options ? el.options[el.selectedIndex].text : el.value).trim() : '';
  }
  function summary() {
    var map = { name: (val('first_name') + ' ' + val('last_name')).trim(), job: val('job'), username: val('username') };
    Object.keys(map).forEach(function (k) {
      var el = form.querySelector('[data-sum="' + k + '"]');
      if (!el) return;
      var label = (k === 'name') ? 'نام' : (k === 'job' ? 'شغل' : 'نام کاربری');
      el.textContent = label + ': ' + (map[k] || '—');
      el.className = 'chip ' + (map[k] ? 'g' : 'n');
    });
  }
  function go(n) {
    cur = n;
    if (cur > maxSeen) maxSeen = cur;
    paint();
  }
  form.addEventListener('click', function (e) {
    if (e.target.closest('[data-wnext]')) {
      if (!valid(cur)) { markInvalid(cur); return; }
      if (cur < 4) go(cur + 1);
      return;
    }
    if (e.target.closest('[data-wback]')) { if (cur > 1) go(cur - 1); return; }
    var dot = e.target.closest('.wdot');
    if (dot) {
      var n = parseInt(dot.getAttribute('data-step'), 10);
      if (n <= Math.max(maxSeen, cur) || valid(cur)) { if (n <= cur || valid(cur)) go(n); }
    }
  });
  form.addEventListener('input', function () { if (cur === 4) summary(); });
  form.addEventListener('change', function () { if (cur === 4) summary(); });
  paint();
})();
