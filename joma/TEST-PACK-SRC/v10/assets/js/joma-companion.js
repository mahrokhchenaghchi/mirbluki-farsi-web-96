/* ==========================================================================
   JOMA — رفتار سبک بستهٔ C1/C2/C3 (افزودنی، بدون هیچ وابستگی)
   بدون این فایل همه‌چیز کار می‌کند؛ این فقط دو نرمی کوچک اضافه می‌کند:
     ۱) جست‌وجوی نام مشاور در فهرست انتخاب (گام ۱ دعوت)
     ۲) بستن پنل «نقش‌های من» با کلیک بیرون
   ========================================================================== */
(function () {
  "use strict";

  /* ۱) جست‌وجوی فهرست مشاوران — روی select بومی کار می‌کند (بدون JS هم باز است) */
  function bindCounselorSearch() {
    var input = document.querySelector("[data-cp-search]");
    var select = document.querySelector("[data-cp-select]");
    if (!input || !select) return;
    var options = [];
    for (var i = 0; i < select.options.length; i++) {
      var o = select.options[i];
      options.push({ el: o, text: (o.textContent || "").replace(/^\s+|\s+$/g, ""), keep: o.disabled });
    }
    input.addEventListener("input", function () {
      var q = (input.value || "").replace(/^\s+|\s+$/g, "");
      var shown = 0;
      for (var j = 0; j < options.length; j++) {
        var item = options[j];
        if (item.keep) continue; /* گزینهٔ راهنما همیشه می‌ماند */
        var match = (q === "") || (item.text.indexOf(q) !== -1);
        item.el.hidden = !match;
        if (match) shown++;
      }
      var none = document.querySelector("[data-cp-none]");
      if (none) none.hidden = (shown > 0);
    });
  }

  /* ۲) بستن پنل نقش‌ها با کلیک بیرون یا Esc */
  function bindRoleMenu() {
    var wraps = document.querySelectorAll(".rs-wrap");
    if (!wraps.length) return;
    document.addEventListener("click", function (ev) {
      for (var i = 0; i < wraps.length; i++) {
        if (!wraps[i].contains(ev.target)) wraps[i].removeAttribute("open");
      }
    });
    document.addEventListener("keydown", function (ev) {
      if (ev.key !== "Escape") return;
      for (var i = 0; i < wraps.length; i++) wraps[i].removeAttribute("open");
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { bindCounselorSearch(); bindRoleMenu(); });
  } else {
    bindCounselorSearch();
    bindRoleMenu();
  }
})();

/* --------------------------------------------------------------------------
   (F3 — v10) نمونهٔ زندهٔ «چگالی نمایش» در تنظیمات
   تغییر همان‌لحظه روی صفحه اعمال می‌شود (همان کلاس body.compact که سرور می‌گذارد)
   و انتخاب داخل فرم ذخیره می‌شود. بدون JS هم فرم کار می‌کند.
   -------------------------------------------------------------------------- */
(function () {
  "use strict";
  function bindDensity() {
    var pick = document.getElementById("dens-pick");
    if (!pick) return;
    var opts = pick.querySelectorAll(".dens-opt");
    function apply(mode) {
      var compact = (mode === "compact");
      document.body.classList.toggle("compact", compact);
      for (var i = 0; i < opts.length; i++) {
        var on = (opts[i].getAttribute("data-dens") === mode);
        opts[i].classList.toggle("on", on);
        var inp = opts[i].querySelector("input");
        if (inp) inp.checked = on;
      }
    }
    for (var i = 0; i < opts.length; i++) {
      opts[i].addEventListener("click", function () {
        apply(this.getAttribute("data-dens"));
      });
    }
  }
  /* اگر عنصر همین حالا در صفحه هست، همان‌جا وصل می‌شویم (اسکریپت در پایان body است)؛
     اگر نبود، یک بار پس از آماده‌شدن DOM. وصل‌شدن دوباره با پرچم جلوگیری می‌شود. */
  function bootDensity() {
    var pick = document.getElementById("dens-pick");
    if (pick && pick.getAttribute("data-dens-bound") === "1") return;
    bindDensity();
    var p2 = document.getElementById("dens-pick");
    if (p2) p2.setAttribute("data-dens-bound", "1");
  }
  bootDensity();
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", bootDensity);
})();
