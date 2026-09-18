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

/* ---- تمرین تنفس ۴-۷-۸ (بدون امتیاز و بدون ثبت خودکار) ---- */
(function () {
  var openBtn = document.querySelector('[data-breath-open]');
  var sheet = document.getElementById('breath-sheet');
  if (!openBtn || !sheet) return;
  var circle = document.getElementById('breath-circle');
  var phaseEl = document.getElementById('breath-phase');
  var hintEl = document.getElementById('breath-hint');
  var startBtn = document.getElementById('breath-start');
  var timer = null, cycle = 0, running = false;

  function setPhase(text, scale, ms, next) {
    phaseEl.textContent = text;
    if (circle) { circle.style.transitionDuration = (ms / 1000) + 's'; circle.style.transform = 'scale(' + scale + ')'; }
    timer = setTimeout(next, ms);
  }
  function finish() {
    running = false;
    clearTimeout(timer);
    if (phaseEl) phaseEl.textContent = 'خوب بود';
    if (hintEl) hintEl.textContent = 'تمرین تمام شد. هر وقت خواستی دوباره.';
    if (circle) { circle.style.transitionDuration = '0.6s'; circle.style.transform = 'scale(1)'; }
    if (startBtn) startBtn.textContent = 'دوباره';
  }
  function round() {
    if (cycle >= 3) { finish(); return; }
    cycle++;
    setPhase('دم بگیر (۴)', 1.28, 4000, function () {
      setPhase('نگه دار (۷)', 1.28, 7000, function () {
        setPhase('بازدم آرام (۸)', 0.86, 8000, round);
      });
    });
  }
  openBtn.addEventListener('click', function () { sheet.classList.add('open'); });
  document.querySelectorAll('[data-breath-close]').forEach(function (b) {
    b.addEventListener('click', function () { clearTimeout(timer); running = false; sheet.classList.remove('open'); });
  });
  sheet.addEventListener('click', function (e) { if (e.target === sheet) { clearTimeout(timer); running = false; sheet.classList.remove('open'); } });
  if (startBtn) startBtn.addEventListener('click', function () { if (running) return; running = true; cycle = 0; round(); });
})();

/* ---- گزینه‌های انتخابی (حال/بله‌و‌خیر/امتیاز): نشان دادن انتخاب ---- */
(function () {
  function sync(group) {
    group.querySelectorAll('input[type=radio]').forEach(function (inp) {
      var lab = inp.closest('label');
      if (!lab) return;
      var box = lab.querySelector('.moodbtn') || lab;
      box.classList.toggle('on', inp.checked);
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target && e.target.type === 'radio') {
      var group = e.target.closest('.rate-grid, .bool-grid') || e.target.closest('section') || document;
      sync(group);
    }
  });
  document.querySelectorAll('.rate-grid, .bool-grid').forEach(sync);
})();
