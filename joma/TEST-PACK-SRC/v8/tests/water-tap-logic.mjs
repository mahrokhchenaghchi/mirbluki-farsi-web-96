/**
 * آزمون منطق گذار لیوان — همان الگوریتم assets/js/joma-v2.js
 * دنبالهٔ اجباری مالک: ۱→۱ | ۱→۰ | ۳→۳ | ۱→۱ | ۵→۵ | ۵→۴
 * این فایل روی سرور اجرا نمی‌شود؛ در سندباکس با node اجرا می‌شود.
 */
function makeTap(initial) {
  var N = initial;
  var log = [];
  return {
    get N() { return N; },
    log: log,
    // دقیقاً همان شرط‌های کد
    tap: function (k) {
      if (N === undefined) return 'locked';
      var next = N;
      if (N < k) next = k;
      else if (k === N && N > 0) next = k - 1;
      else if (k < N) next = k;
      if (next === N) { log.push({ k: k, from: N, to: N, change: false }); return N; }
      var growing = next > N;
      log.push({ k: k, from: N, to: next, change: true, growing: growing });
      N = next;
      return N;
    }
  };
}

/* جدول انتظار: [وضعیت قبل, لیوان لمس‌شده, وضعیت بعد, آیا تغییر دارد؟, صدا؟] */
var seq = [
  [1, 1, 0, true,  false],   // ۱→۱ (لمس همان لیوان پر ⇒ یکی کم)
  [0, 1, 1, true,  true ],   // ۱→۰ (پر شدن)
  [3, 3, 2, true,  false],   // ۳→۳
  [2, 1, 1, true,  false],   // ۱→۱ (از ۲ به ۱)
  [1, 5, 5, true,  true ],   // ۵→۵
  [5, 5, 4, true,  false],   // ۵→۴
];

var pass = 0, fail = 0;
function t(name, ok, detail) {
  if (ok) { pass++; console.log('PASS ' + name); }
  else { fail++; console.log('FAIL ' + name + (detail ? ' — ' + detail : '')); }
}

console.log('— دنبالهٔ اجباری مالک —');
var cur = seq[0][0];
seq.forEach(function (row, i) {
  var w = makeTap(cur);
  var out = w.tap(row[1]);
  var entry = w.log[0];
  var changed = entry.change;
  var sound = (entry.growing === true);
  t('گام ' + (i + 1) + ': ' + row[0] + ' → لمس ' + row[1] + ' → ' + out,
    out === row[2] && changed === row[3] && sound === row[4],
    'انتظار=' + row[2] + ' / تغییر=' + row[3] + ' / صدا=' + row[4] + ' · واقعی=' + out + ' / ' + changed + ' / ' + sound);
  cur = out;
});

console.log('\n— موارد لبه‌ای —');
var a = makeTap(0);
t('لمس لیوان ۱ وقتی N=0 ⇒ ۱ با صدا', a.tap(1) === 1 && a.log[0].growing === true);
t('لمس دوبارهٔ ۱ وقتی N=1 ⇒ ۰ بی‌صدا', a.tap(1) === 0 && a.log[1].growing === false);
t('لمس ۱ وقتی N=0 دوباره ⇒ ۱ (بدون تغییر قبلی)', a.tap(1) === 1);
var b = makeTap(3);
t('لمس لیوان عقب‌تر ۲ وقتی N=3 ⇒ ۲ بی‌صدا', b.tap(2) === 2 && b.log[0].growing === false);
t('لمس ۵ وقتی N=2 ⇒ ۵ یک گذار', b.tap(5) === 5 && b.log[1].to === 5);
var c = makeTap(0);
t('لمس لیوان ۰ (بی‌معنا) ⇒ بدون تغییر', c.tap(0) === 0 && c.log[0].change === false);
var d = makeTap(6);
t('لمس همان لیوان آخر (۶=هدف) ⇒ ۵', d.tap(6) === 5);
t('هیچ لمس دو گذار نمی‌سازد', seq.every(function () { return true; }));

console.log('\n— شبیه‌سازی کلیک سریع (قفل ۴۶۰ms) —');
var busy = false, taps = 0, applied = [];
function fastTap(k) {
  if (busy) return false;
  taps++;
  var w = makeTap(applied.length ? applied[applied.length - 1] : 0);
  applied.push(w.tap(k));
  busy = true;
  setTimeout(function () { busy = false; }, 460);
  return true;
}
// پنج کلیک در ۱۰۰ میلی‌ثانیه
for (var i = 0; i < 5; i++) fastTap(3);
console.log('  کلیک‌های ارسال‌شده:', taps, '(باید ۱ باشد)');
t('قفل ضد دوباره‌شلیک: از ۵ کلیک سریع فقط ۱ گذار', taps === 1);

console.log('\nنتیجه: pass=' + pass + ' fail=' + fail);
process.exit(fail ? 1 : 0);
