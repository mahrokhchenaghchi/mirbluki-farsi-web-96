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
