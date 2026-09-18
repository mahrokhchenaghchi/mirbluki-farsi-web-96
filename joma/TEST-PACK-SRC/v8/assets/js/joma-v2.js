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
   آب (B6) — «هر لمس = یک گذار» (تصمیم مالک v8)
   --------------------------------------------------------------------------
   وضعیت = یک عدد N از ۰ تا هدف. هر لمس دقیقاً یک گذار می‌سازد:
     لمس k وقتی N<k          ⇒ N=k
     لمس همان لیوانِ پر      ⇒ N=k−1   (بی‌صدا)
     لمس لیوان عقب‌تر (k<N)  ⇒ N=k     (بی‌صدا)
     بدون تغییر (k=N=0)      ⇒ هیچ انیمیشنی
   · فقط یک رویداد (click)          · قفل تا پایان انیمیشن (~460ms)
   · انیمیشن transform از N قدیم    · SVG بازسازی نمی‌شود
   ========================================================================== */
(function () {
  var card = document.getElementById('water-card');
  if (!card) return;

  var glasses = Array.prototype.slice.call(card.querySelectorAll('[data-fill]'));
  var labelEl = card.querySelector('[data-water-label]');
  var badgeEl = card.querySelector('[data-water-badge]');
  var paId = card.getAttribute('data-pa');
  var date = card.getAttribute('data-date');
  var STEP_MS = 460;                         // طول انیمیشن یک لیوان
  var busy = false;                          // قفل ضد دوباره‌شلیک
  var N = parseInt(card.getAttribute('data-value'), 10) || 0;   // وضعیت فعلی

  /* ---------- صدا: پیش‌فرض خاموش ---------- */
  var soundOn = false;
  try { soundOn = (localStorage.getItem('joma_water_sound') === '1'); } catch (e) {}
  var sndBtn = card.querySelector('[data-water-sound]');
  function paintSound() {
    if (!sndBtn) return;
    sndBtn.classList.toggle('on', soundOn);
    sndBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
  }
  paintSound();
  if (sndBtn) sndBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    soundOn = !soundOn;
    try { localStorage.setItem('joma_water_sound', soundOn ? '1' : '0'); } catch (e2) {}
    paintSound();
    if (soundOn) tone(660, 0.08, 0.05);
  });

  var AC = window.AudioContext || window.webkitAudioContext;
  var ac = null;
  function tone(freq, dur, gain, type) {
    if (!soundOn || !AC) return;
    try {
      if (!ac) ac = new AC();
      var o = ac.createOscillator(), g = ac.createGain();
      o.type = type || 'sine'; o.frequency.value = freq; g.gain.value = gain || 0.06;
      o.connect(g); g.connect(ac.destination);
      var t = ac.currentTime;
      g.gain.setValueAtTime(g.gain.value, t);
      g.gain.exponentialRampToValueAtTime(0.0008, t + dur);
      o.start(t); o.stop(t + dur + 0.02);
    } catch (e) {}
  }
  function pour() {
    if (!soundOn || !AC) return;
    try {
      if (!ac) ac = new AC();
      var len = Math.floor(ac.sampleRate * 0.16);
      var buf = ac.createBuffer(1, len, ac.sampleRate), d = buf.getChannelData(0);
      for (var i = 0; i < len; i++) d[i] = (Math.random() * 2 - 1) * (1 - i / len) * 0.4;
      var src = ac.createBufferSource(); src.buffer = buf;
      var f = ac.createBiquadFilter(); f.type = 'bandpass'; f.frequency.value = 900; f.Q.value = 0.8;
      var g = ac.createGain(); g.gain.value = 0.08;
      src.connect(f); f.connect(g); g.connect(ac.destination); src.start();
    } catch (e) {}
  }

  /* ---------- نمایش: فقط transform روی گروه آب (بدون بازسازی SVG) ---------- */
  var GLASS_H = 20.0;                        // ارتفاع یک پله در واحد viewBox (۱۳٫۲ + ۶٫۸)
  function showWater(value) {
    glasses.forEach(function (el, idx) {
      var i = idx + 1;
      var on = (i <= value);
      var water = el.querySelector('.glass-water');
      if (water) {
        // در حالت پر: translateY(0) · در حالت خالی: یک لیوان پایین‌تر (کل آب بیرون)
        water.style.transform = on ? 'translateY(0)' : 'translateY(' + GLASS_H + 'px)';
        water.style.opacity = on ? '1' : '.55';
      }
      el.classList.toggle('on', on);
      el.setAttribute('aria-pressed', on ? 'true' : 'false');
      el.setAttribute('aria-label', i + (i === value ? ' لیوان — آخرین لیوان پر' : ' لیوان'));
    });
    N = value;
    card.setAttribute('data-value', String(value));
    var target = parseFloat(card.getAttribute('data-target')) || 0;
    if (labelEl) {
      labelEl.textContent = (value > 0)
        ? (faNum(value) + ' از هدف ' + faNum(target) + ' لیوان')
        : ('هنوز چیزی ثبت نشده — هدف امروز ' + faNum(target) + ' لیوان');
    }
  }
  function faNum(n) { return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); }

  /* ---------- ارسال به سرور ---------- */
  var csrf = (card.querySelector('input[name="csrf"]') || {}).value || '';
  function post(value, action, done) {
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
      if (!d || !d.state) return;
      var s = d.state;
      card.setAttribute('data-draft', s.is_draft ? '1' : '0');
      card.setAttribute('data-final', s.is_final ? '1' : '0');
      if (badgeEl) {
        badgeEl.textContent = s.is_final ? 'قطعی' : (s.is_draft ? 'پیش‌نویس' : 'ثبت نشده');
        badgeEl.className = 'chip ' + (s.is_final ? 'g' : (s.is_draft ? 'go' : 'n'));
      }
      var counter = document.querySelector('[data-done-count]');
      if (counter) counter.textContent = faNum(s.done_count) + ' از ' + faNum(s.total_count) + ' ثبت شده';
      done && done(s);
    }).catch(function () { /* بی‌صدا: وضعیت محلی درست است؛ دفعهٔ بعد سرور همگام می‌شود */ });
  }

  /* ---------- یک لمس = یک گذار ---------- */
  card.addEventListener('click', function (e) {
    var el = (e.target && e.target.closest) ? e.target.closest('[data-fill]') : null;
    if (!el) return;                                    // کلیک روی خود کارت: بدون گذار و بدون انیمیشن
    if (busy) return;                                   // قفل تا پایان انیمیشن
    if (card.getAttribute('data-final') === '1') { tone(300, 0.08, 0.05, 'triangle'); return; }

    var k = parseInt(el.getAttribute('data-fill'), 10);
    var next = N;
    if (N < k) next = k;                                // پر شدن تا k
    else if (k === N && N > 0) next = k - 1;            // لمس همان لیوانِ پر = یکی کم
    else if (k < N) next = k;                           // لیوان عقب‌تر = پایین آمدن
    if (next === N) return;                             // بدون تغییر ⇒ هیچ انیمیشنی

    var growing = (next > N);
    busy = true;
    showWater(next);                                    // انیمیشن از N قدیم به جدید
    el.classList.add('bump');
    setTimeout(function () { el.classList.remove('bump'); }, 320);
    if (growing) { pour(); tone(520 + next * 60, 0.09, 0.045); }   // فقط پر شدن صدا دارد
    post(next, 'draft_save');
    setTimeout(function () { busy = false; }, STEP_MS);            // آزادسازی قفل
  });

  /* ---------- ثبت نهایی: بدون پرش صفحه ---------- */
  var finForm = card.querySelector('[data-water-finalize]');
  if (finForm) finForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (busy) return;
    busy = true;
    tone(880, 0.14, 0.06);
    post(null, 'finalize', function () {
      var b = finForm.querySelector('button');
      if (b) { b.disabled = true; b.textContent = 'ثبت نهایی شد ✓'; b.className = 'btn soft'; }
      var note = finForm.querySelector('.tiny');
      if (!note) { note = document.createElement('p'); note.className = 'tiny'; note.style.marginTop = '6px'; finForm.appendChild(note); }
      note.textContent = 'این ثبت قطعی است و تغییر نمی‌کند.';
      glasses.forEach(function (el) { el.disabled = true; });
    });
    setTimeout(function () { busy = false; }, STEP_MS);
  });

  showWater(N);   // وضعیت اولیه از دادهٔ سرور
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

  var SENT = sheet.querySelector('#breath-sentence');
  var SENTENCES = {
    'in': 'از بینی، آرام دم بگیر.',
    'hold': 'نفس را نگه دار، شانه‌ها شل و رها.',
    'out': 'حالا آرام و بلند، از دهان بازدم.',
    'idle': 'دکمه را بزن؛ سه دور نفس آرام.'
  };
  function say(text, scale, ms, next, soundKey) {
    phaseEl.textContent = text;
    if (SENT && soundKey && SENTENCES[soundKey]) SENT.textContent = SENTENCES[soundKey];
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
    if (SENT) SENT.textContent = 'خوب بود. هر وقت خواستی دوباره.';
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
  if (!(cur >= 1 && cur <= 5)) cur = 1;
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
    if (n === 4) {
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
    if (count) count.textContent = 'قدم ' + faN(cur) + ' از ۵';
    if (cur === 5) summary();
    var first = sec(cur) && sec(cur).querySelector('input,select');
    if (first) { try { first.focus({ preventScroll: true }); } catch (e) {} }
  }
  function val(name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? (el.options ? el.options[el.selectedIndex].text : el.value).trim() : '';
  }
  function summary() {
    var map = { name: (val('first_name') + ' ' + val('last_name')).trim(), job: val('job'), username: val('username'),
                contact: (val('phone') + (val('email') ? ' · ' + val('email') : '')).trim() };
    Object.keys(map).forEach(function (k) {
      var el = form.querySelector('[data-sum="' + k + '"]');
      if (!el) return;
      var label = (k === 'name') ? 'نام' : (k === 'job' ? 'شغل' : (k === 'username' ? 'نام کاربری' : 'تماس'));
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
      if (cur < 5) go(cur + 1);
      return;
    }
    if (e.target.closest('[data-wback]')) { if (cur > 1) go(cur - 1); return; }
    var dot = e.target.closest('.wdot');
    if (dot) {
      var n = parseInt(dot.getAttribute('data-step'), 10);
      if (n <= Math.max(maxSeen, cur) || valid(cur)) { if (n <= cur || valid(cur)) go(n); }
    }
  });
  form.addEventListener('input', function () { if (cur === 5) summary(); });
  form.addEventListener('change', function () { if (cur === 5) summary(); });
  paint();
})();

/* ==========================================================================
   جوجهٔ من — صدا (جیک · خرخر · خروپف · خمیازه) و تعامل بی‌پاداش (سند ۱۴)
   همهٔ صداها با WebAudio ساخته می‌شوند و هيچ سنجه‌ای را پر نمی‌کنند.
   ========================================================================== */
(function () {
  var stage = document.getElementById('pet-stage');
  if (!stage) return;
  var AC = window.AudioContext || window.webkitAudioContext;
  var ac = null, snoreTimer = null;
  var soundOn = false;                       // 🔴 پیش‌فرض خاموش
  try { soundOn = (localStorage.getItem('joma_pet_sound') === '1'); } catch (e) {}
  var sleeping = false;
  var touches = [];                          // petGate: پنجرهٔ یک‌دقیقه‌ای
  var phrases = ['چی چی!', 'خوشحالم که هستی.', 'امروز هم منتظرت بودم.'];
  var bubble = document.getElementById('pet-bubble');
  var soundBtn = document.getElementById('pet-sound');

  function ctx() { if (!AC) return null; if (!ac) { try { ac = new AC(); } catch (e) { return null; } } return ac; }
  var MASTER = 0.5;   // ≈ −۶dB (تصمیم مالک: بلندی ملایم)
  function env(g, t, attack, hold, release, peak) {
    peak = peak * MASTER;
    g.gain.setValueAtTime(0.0001, t);
    g.gain.exponentialRampToValueAtTime(peak, t + attack);
    g.gain.setValueAtTime(peak, t + attack + hold);
    g.gain.exponentialRampToValueAtTime(0.0001, t + attack + hold + release);
  }
  function tone(type, f0, f1, t, dur, peak, tremoloHz) {
    var c = ctx(); if (!c) return;
    var o = c.createOscillator(), g = c.createGain();
    o.type = type; o.frequency.setValueAtTime(f0, t);
    if (f1 && f1 !== f0) o.frequency.linearRampToValueAtTime(f1, t + dur);
    g.gain.value = 0;
    o.connect(g); g.connect(c.destination);
    env(g, t, 0.02, dur * 0.25, dur * 0.6, peak);
    if (tremoloHz) {
      var lfo = c.createOscillator(), lg = c.createGain();
      lfo.frequency.value = tremoloHz; lg.gain.value = peak * 0.45;
      lfo.connect(lg); lg.connect(g.gain);
      lfo.start(t); lfo.stop(t + dur + 0.05);
    }
    o.start(t); o.stop(t + dur + 0.05);
  }
  function noise(t, dur, freq, peak, q) {
    var c = ctx(); if (!c) return;
    var len = Math.max(1, Math.floor(c.sampleRate * dur));
    var buf = c.createBuffer(1, len, c.sampleRate), d = buf.getChannelData(0);
    for (var i = 0; i < len; i++) d[i] = (Math.random() * 2 - 1);
    var src = c.createBufferSource(); src.buffer = buf;
    var f = c.createBiquadFilter(); f.type = 'bandpass'; f.frequency.value = freq; f.Q.value = q || 0.8;
    var g = c.createGain(); g.gain.value = 0;
    src.connect(f); f.connect(g); g.connect(c.destination);
    env(g, t, 0.02, dur * 0.3, dur * 0.6, peak);
    src.start(t);
  }
  /* جیک دو نُتی: ۴۷۰→۷۸۰ و ۶۲۰→۹۰۰ با لرزش ۲۲Hz */
  function chirp() { if (!soundOn) return; var c = ctx(); if (!c) return; var t = c.currentTime + 0.01;
    tone('sine', 470, 780, t, 0.10, 0.16, 22);
    tone('sine', 620, 900, t + 0.10, 0.11, 0.14, 22);   // مجموع ≈ ۰٫۲۱s
  }
  /* خرخر نوازش: مثلثی ۱۱۲→۱۲۶ + ترمولوی ۲۶Hz + لایهٔ نفس + جیک ریز پایانی */
  function purr() { if (!soundOn) return; var c = ctx(); if (!c) return; var t = c.currentTime + 0.01;
    tone('triangle', 112, 126, t, 1.05, 0.22, 26);
    noise(t, 1.0, 900, 0.025, 0.7);
    tone('sine', 700, 880, t + 1.0, 0.10, 0.07, 22);
  }
  /* خروپف نرم: دم کوتاه ۰٫۶۲s و بازدم بلندتر و بم‌تر ۰٫۸۵s */
  function snoreOnce() { if (!soundOn) return; var c = ctx(); if (!c) return; var t = c.currentTime + 0.01;
    noise(t, 0.62, 520, 0.035, 0.9);
    tone('sine', 132, 118, t, 0.62, 0.035);
    noise(t + 0.68, 0.85, 320, 0.045, 0.9);
    tone('sine', 108, 92, t + 0.68, 0.85, 0.05);
  }
  /* خمیازه: سینوس نزولی ۳۴۰→۱۹۰ و بعد خروپف */
  function yawn() { if (!soundOn) return; var c = ctx(); if (!c) return; var t = c.currentTime + 0.01;
    tone('sine', 340, 190, t, 0.75, 0.11);
    tone('sine', 240, 150, t + 0.5, 0.55, 0.07);
    setTimeout(function () { snoreOnce(); }, 900);
  }
  function startSnoreLoop() {
    stopSnoreLoop();
    if (!soundOn || !sleeping) return;
    snoreOnce();
    snoreTimer = setInterval(function () { if (sleeping) snoreOnce(); }, 3200);
  }
  function stopSnoreLoop() { if (snoreTimer) { clearInterval(snoreTimer); snoreTimer = null; } }

  function paintSound() {
    if (!soundBtn) return;
    soundBtn.classList.toggle('on', soundOn);
    soundBtn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
    var label = soundBtn.querySelector('span');
    if (label) label.textContent = soundOn ? 'صدای جوجه روشن' : 'صدای جوجه';
  }
  paintSound();
  if (soundBtn) soundBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    soundOn = !soundOn;
    try { localStorage.setItem('joma_pet_sound', soundOn ? '1' : '0'); } catch (e2) {}
    paintSound();
    if (soundOn) { if (sleeping) startSnoreLoop(); else chirp(); }
    else stopSnoreLoop();
  });

  function say(text) {
    if (!bubble) return;
    bubble.textContent = text;
    bubble.classList.add('on');
    setTimeout(function () { bubble.classList.remove('on'); }, 2200);
  }

  /* ---------- لمس (بی‌پاداش) با petGate ---------- */
  stage.addEventListener('click', function (e) {
    if (e.target.closest('#pet-sound, [data-pet-sleep]')) return;
    var now = Date.now();
    touches = touches.filter(function (t) { return (now - t) < 60000; });
    touches.push(now);
    var n = touches.length;

    if (sleeping) { snoreOnce(); say('خواب است؛ فقط خروپف نرم 😴'); return; }

    if (n >= 8) {                                  // ۸ لمس: فقط یک پلک، بدون حباب
      blink();
      return;
    }
    if (n >= 4) {                                  // ۴ لمس: فقط خرخر آرام، بدون حباب
      purr();
      touchAnim();
      return;
    }
    chirp();                                       // جیک دو نُتی، زیر ۲۵۰ms
    touchAnim();
    say(phrases[Math.floor(Math.random() * phrases.length)]);
  });

  function touchAnim() {
    stage.classList.remove('pet-touch');
    void stage.offsetWidth;
    stage.classList.add('pet-touch');
    setTimeout(function () { stage.classList.remove('pet-touch'); }, 950);
  }
  function blink() {
    stage.classList.remove('pet-blink');
    void stage.offsetWidth;
    stage.classList.add('pet-blink');
    setTimeout(function () { stage.classList.remove('pet-blink'); }, 650);
  }

  /* ---------- دکمهٔ «نوازش»: خرخر ملایم + تکان کوچک (بی‌پاداش) ---------- */
  var petBtn = document.querySelector('[data-pet-pet]');
  if (petBtn) petBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    var now = Date.now();
    touches = touches.filter(function (t) { return (now - t) < 60000; });
    touches.push(now);
    if (sleeping) { snoreOnce(); say('خواب است؛ فقط خروپف نرم 😴'); return; }
    if (touches.length >= 8) { blink(); return; }          // petGate: فقط پلک
    if (touches.length >= 4) { purr(); touchAnim(); return; } // petGate: فقط خرخر
    purr(); touchAnim();
    say(phrases[Math.floor(Math.random() * phrases.length)]);
  });

  /* ---------- بخوابانش / بیدارش کن (کارکرد رابط؛ هیچ سنجه‌ای را پر نمی‌کند) ---------- */
  var sleepBtn = document.querySelector('[data-pet-sleep]');
  if (sleepBtn) sleepBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    sleeping = !sleeping;
    stage.classList.toggle('pet-sleeping', sleeping);
    sleepBtn.textContent = sleeping ? 'بیدارش کن' : 'بخوابانش';
    var label = document.querySelector('[data-pet-state-label]');
    if (label) label.textContent = sleeping ? 'خواب (تو خواباندی‌اش)' : (stage.getAttribute('data-state-label') || '');
    if (sleeping) { startSnoreLoop(); say('خوب بخوابی 🌙'); }
    else { stopSnoreLoop(); yawn(); say('صبح بخیر!'); }
  });

  /* پلک‌زدن خودکار در حالت آرام/شاد — هر چند ثانیه، بدون صدا */
  if (stage.getAttribute('data-state') === 'calm' || stage.getAttribute('data-state') === 'happy') {
    setInterval(function () { if (!sleeping && document.visibilityState === 'visible') blink(); }, 6500);
  }

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState !== 'visible') stopSnoreLoop();
    else if (sleeping && soundOn) startSnoreLoop();
  });
})();

/* ==========================================================================
   تقویم «مسیر این ماه» — زدن روی روز، خلاصه‌اش را زیر تقویم نشان می‌دهد
   ========================================================================== */
(function () {
  var grid = document.querySelector('.cal-grid');
  if (!grid) return;
  var bar = document.getElementById('cal-bar');
  if (!bar) return;
  var tiles = grid.querySelectorAll('[data-detail]');

  tiles.forEach(function (t) {
    t.addEventListener('click', function (e) {
      var state = t.getAttribute('data-state');
      var label = t.getAttribute('aria-label') || '';
      var date = t.getAttribute('data-day');
      var detail = t.getAttribute('data-detail') || '';
      // اگر همان روز دوباره زده شد، به صفحهٔ ثبت همان روز برو (تایل لینک است)
      if (t.getAttribute('data-picked') === '1') return;
      if (state === 'future') {
        e.preventDefault();
        bar.textContent = 'ثبت برای روزهای آینده ممکن نیست — از ابتدای ماه تا امروز می‌توانی ثبت کنی.';
        return;
      }
      // اولین زدن: خلاصه را نشان بده و انتخاب را نگه دار
      e.preventDefault();
      tiles.forEach(function (x) { x.removeAttribute('data-picked'); });
      t.setAttribute('data-picked', '1');
      var name = label.split(' — ')[0] || date;
      bar.innerHTML = '<b>' + name + '</b> — ' + detail + ' <span class="tiny">(برای ثبت در این روز دوباره بزن)</span>';
    });
  });
})();

/* ==========================================================================
   انتخاب تم (کلاسیک / شیشه) — ذخیره روی همین دستگاه + اعمال فوری
   ========================================================================== */
(function () {
  var pick = document.getElementById('theme-pick');
  if (!pick) return;
  var opts = pick.querySelectorAll('.theme-opt');
  function current() {
    try { var t = localStorage.getItem('joma_theme'); return (t === 'glass') ? 'glass' : 'classic'; }
    catch (e) { return 'classic'; }
  }
  function paint() {
    var cur = current();
    opts.forEach(function (o) {
      var on = (o.getAttribute('data-theme') === cur);
      o.classList.toggle('on', on);
      var inp = o.querySelector('input');
      if (inp) inp.checked = on;
    });
  }
  opts.forEach(function (o) {
    o.addEventListener('click', function () {
      var v = o.getAttribute('data-theme') === 'glass' ? 'glass' : 'classic';
      try { localStorage.setItem('joma_theme', v); } catch (e) {}
      document.documentElement.setAttribute('data-theme', v);
      paint();
    });
  });
  paint();
})();

/* ==========================================================================
   جغد راهنما: کلیک = تکان کوتاه (بی‌پاداش، بدون تغییر داده)
   ========================================================================== */
(function () {
  var box = document.getElementById('joma-msg');
  if (!box) return;
  function wiggle() {
    box.classList.remove('wiggle');
    void box.offsetWidth;
    box.classList.add('wiggle');
    setTimeout(function () { box.classList.remove('wiggle'); }, 950);
  }
  box.addEventListener('click', wiggle);
  box.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); wiggle(); } });
})();
