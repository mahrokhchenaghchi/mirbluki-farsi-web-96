<?php
/*
 * ماژول «هم‌مسیر» — partial جریان مراجع (Phase 2/4)
 * ------------------------------------------------------------------
 * فقط از pages/hammasir.php include می‌شود؛ route عمومی ندارد (D29/D30).
 * متغیر مورد انتظار: $hammasir_me (user_id کاربر جاری)
 *
 * بدون لینک باز: سؤال رسمی + فهرست همراهان ACTIVE + فرم رضایت (متن مصوب)
 * با لینک باز: وضعیت + ویرایش مجوزهای مشاهده (فقط مراجع — D19) +
 *   توگل واحد پیام (D37) + گفتگو + لغو/قطع (D20)
 */

// گارد مستقیم — partial هرگز مستقیماً از وب اجرا نمی‌شود → 403 خشک (AC6.3)
if (!defined('JOMA_IN_APP')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// خواندها با مهار خطای PHP 8.1 (D-1): خطا → حالت خالی/امن
$hammasir_c_open = null;
$hammasir_c_pick = array();
$hammasir_c_prov = null;
$hammasir_c_perms = null;
$hammasir_c_msg_on = false;
// مدل state واحد Onboarding (حکم PO — بخش ۶)؛ پیش‌فرض امن fail-closed:
// seen=1 و intent=0 → هیچ کارت دعوت/فرمی خودکار رندر نمی‌شود
$hammasir_c_state = array('seen' => 1, 'intent' => 0);
$hammasir_c_latest = null;   // آخرین لینک با هر وضعیت (بخش ۴)
try {
    $hammasir_c_open = hammasir_link_open_by_client($hammasir_me);
    $hammasir_c_pick = hammasir_provider_list(true);
    if (!is_array($hammasir_c_pick)) $hammasir_c_pick = array();
    if (!$hammasir_c_open) {
        $hammasir_c_state = hammasir_onboarding_state($hammasir_me);
        $hammasir_c_latest = hammasir_latest_link_by_client($hammasir_me);
    }
} catch (Throwable $e) {
    $hammasir_c_open = null;
    $hammasir_c_pick = array();
    $hammasir_c_state = array('seen' => 1, 'intent' => 0);
    $hammasir_c_latest = null;
}

if (!$hammasir_c_open) {
    // ----- رندر بدون لینک باز — ترتیب دقیق حکم PO (بخش ۶)؛ هیچ شرط دیگری فرم را باز نمی‌کند -----
    if ($hammasir_c_latest && $hammasir_c_state['intent'] === 0) {
        // قاعده ۲: لینک بسته + intent=0 → فقط وضعیت قبلی + دکمه‌ی آگاهانه‌ی «درخواست همراهی جدید»
        $hammasir_c_sl = hammasir_link_status_labels();
        $hammasir_c_status = isset($hammasir_c_sl[$hammasir_c_latest['status']]) ? $hammasir_c_sl[$hammasir_c_latest['status']] : $hammasir_c_latest['status'];
        $hammasir_c_lprov = hammasir_provider_by_user_id((int) $hammasir_c_latest['provider_user_id']);
        $hammasir_c_ltitle = $hammasir_c_lprov ? $hammasir_c_lprov['title'] : ('همراه #' . (int) $hammasir_c_latest['provider_user_id']);
        ?>
        <section class="card">
          <h2>همراه شما در این مسیر</h2>
          <div class="btn-row">
            <span class="chip"><?php echo e($hammasir_c_ltitle); ?></span>
            <span class="chip"><?php echo e($hammasir_c_status); ?></span>
          </div>
          <?php if ($hammasir_c_latest['status'] === 'DECLINED') { ?>
            <p>درخواست شما از سوی همراه رد شده است.</p>
          <?php } else { ?>
            <p>این ارتباط قبلاً قطع شده است.</p>
          <?php } ?>
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hammasir_action" value="client_request_new">
            <div class="btn-row">
              <button class="btn sec" type="submit">درخواست همراهی جدید</button>
            </div>
          </form>
        </section>
        <?php
    } else {
        // ================================================================
        // (بند ۴) چهار گام دعوت — کارت‌های پشت‌سرهم، نه ویزارد
        //   گام ۱ انتخاب همراه (دراپ‌داون + جست‌وجو، بدون پیش‌انتخاب)
        //   گام ۲ چه چیزی می‌بیند؟ (هیچ تیکی پیش‌فرض نیست؛ هر مجوز متن توضیح دارد)
        //   گام ۳ متن رضایت (از بک‌اند، نسخه‌دار)
        //   گام ۴ فرستادن درخواست + «کد دعوت دارم»
        // رابطه یک‌طرفه ساخته نمی‌شود: تا هر دو طرف تأیید نکنند ACTIVE نمی‌شود.
        // ================================================================
        $hammasir_c_rows = array();
        if (function_exists('joma_companion_perm_rows')) $hammasir_c_rows = joma_companion_perm_rows();
        $hammasir_c_quota = function_exists('joma_companion_quota_line') ? joma_companion_quota_line() : '';
        $hammasir_c_consent = function_exists('joma_companion_consent_text') ? joma_companion_consent_text() : '';
        $hammasir_c_consent_v = function_exists('joma_companion_consent_version') ? joma_companion_consent_version() : '';
        ?>
        <section class="card guide">
          <div class="row" style="gap:12px;align-items:center">
            <span style="flex:none;width:46px;height:46px;border-radius:50%;background:var(--brand-softer);display:grid;place-items:center">
              <?php echo joma_v2_icon('owl-hi', 'ic lg'); ?>
            </span>
            <div>
              <b style="font-size:13.5px">همراه کیست؟</b>
              <p class="lede">همراه کسی است که در این مسیر راهنمایی‌ات می‌کند — مشاور، کوچ یا مربی. اینجا دوست و آشنا اضافه نمی‌شود. همراه به هیچ داده‌ای دسترسی ندارد، مگر خودت اجازه بدهی.</p>
            </div>
          </div>
        </section>

        <form method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="hammasir_action" value="link_request">

          <section class="card">
            <div class="cstep">
              <span class="inum">۱</span>
              <div class="cstep-body">
                <b>همراه را انتخاب کن</b>
                <?php if (count($hammasir_c_pick) === 0) { ?>
                  <p>همین حالا مشاوری در دسترس نیست. می‌توانی بعداً امتحان کنی یا با پشتیبانی تماس بگیری.</p>
                <?php } else { ?>
                  <p>یک مشاور انتخاب کن. به او خبر می‌دهیم و خودش تصمیم می‌گیرد.</p>
                  <label class="lbl" for="cp-search">جست‌وجوی نام</label>
                  <input class="inp cp-search" type="search" id="cp-search" data-cp-search placeholder="بخشی از نام مشاور…" autocomplete="off">
                  <!-- فهرست زندهٔ همراهان فعال — هیچ پیش‌انتخابی وجود ندارد -->
                  <select name="provider_user_id" id="cp-select" class="inp sel" data-cp-select required>
                    <option value="" disabled selected>انتخاب کنید</option>
                    <?php foreach ($hammasir_c_pick as $hammasir_c_p) { ?>
                      <?php if ((int) $hammasir_c_p['user_id'] === $hammasir_me) continue; // self-link ممنوع ?>
                      <option value="<?php echo (int) $hammasir_c_p['user_id']; ?>"><?php echo e($hammasir_c_p['title']); ?></option>
                    <?php } ?>
                  </select>
                  <p class="tiny" data-cp-none hidden>با این نام کسی پیدا نشد.</p>
                <?php } ?>
              </div>
            </div>
          </section>

          <section class="card">
            <div class="cstep">
              <span class="inum">۲</span>
              <div class="cstep-body">
                <b>چه چیزی می‌بیند؟</b>
                <p>هیچ‌چیز پیش‌فرض روشن نیست؛ فقط چیزی که خودت تیک بزنی.</p>
                <div class="prow">
                  <span class="chip g" style="margin-top:2px">پایه</span>
                  <div><b>خلاصهٔ وضعیت و پیشرفت کلی</b>
                    <small>شمار روزهای ثبت‌شده، وضعیت فعالیت‌ها — این مورد پایه است و همیشه هست.</small></div>
                </div>
                <div class="prow">
                  <input type="checkbox" name="perm_VIEW_PROGRESS" value="1" id="perm-progress">
                  <label for="perm-progress"><b>پیشرفت و پوشش تجمیعی</b>
                    <small>درصدها و نمودارهای دوره</small></label>
                </div>
                <div class="prow">
                  <input type="checkbox" name="perm_VIEW_ACTIVITY_DETAILS" value="1" id="perm-activities">
                  <label for="perm-activities"><b>جزئیات فعالیت‌ها</b>
                    <small>کدام فعالیت‌ها را چقدر انجام داده‌ای</small></label>
                </div>
                <div class="prow">
                  <input type="checkbox" name="perm_VIEW_MOOD" value="1" id="perm-mood">
                  <label for="perm-mood"><b>شاخص‌های حال من</b>
                    <small>عددها و نمودارهای حال</small></label>
                </div>
                <div class="mood-note-warn">اگر «شاخص‌های حال من» را روشن کنی، <b>متن یادداشت‌های روزانه‌ات</b> هم دیده می‌شود.</div>

                <div class="pgroup">
                  <b>گفت‌وگو</b>
                  <div class="prow">
                    <input type="checkbox" name="perm_msg_out" value="1" id="perm-msg-out">
                    <label for="perm-msg-out"><b>من می‌توانم به او پیام بدهم</b>
                      <small>سقف روزانه از سرور</small></label>
                  </div>
                  <div class="prow">
                    <input type="checkbox" name="perm_msg_in" value="1" id="perm-msg-in">
                    <label for="perm-msg-in"><b>او می‌تواند به من پیام بدهد</b>
                      <small>سقف روزانه از سرور</small></label>
                  </div>
                  <p class="tiny" style="margin-top:6px">برای گفت‌وگو، هر دو گزینه لازم است.</p>
                  <?php if ($hammasir_c_quota !== '') { ?>
                    <div class="quota"><?php echo e($hammasir_c_quota); ?></div>
                  <?php } ?>
                </div>

                <?php /* کادر مستقل نشانگر وضعیت — هرگز حال/خلق/یادداشت (سند ۲۰ §۳٫۴) */ ?>
                <div class="share-box">
                  <div class="prow" style="border-bottom:none">
                    <input type="checkbox" name="status_share" value="1" id="status-share">
                    <label for="status-share"><b>می‌خواهم همراه بداند چند وقت است چیزی ثبت نکرده‌ام</b>
                      <small>اگر این را روشن کنی، همراهت روی صفحه‌اش یک نشانگر می‌بیند که می‌گوید چند روز است چیزی ثبت نکرده‌ای.
                      این نشانگر هیچ‌وقت حال، خلق یا یادداشتت را نشان نمی‌دهد — فقط وضعیت ثبت را. هر وقت خواستی می‌توانی خاموشش کنی.</small></label>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="card">
            <div class="cstep">
              <span class="inum">۳</span>
              <div class="cstep-body">
                <b>رضایت</b>
                <div class="consent-box"><?php echo e($hammasir_c_consent); ?>
                  <?php if ($hammasir_c_consent_v !== '') { ?><div class="tiny" style="margin-top:6px">نسخهٔ رضایت: <?php echo e($hammasir_c_consent_v); ?></div><?php } ?>
                </div>
              </div>
            </div>
          </section>

          <section class="card">
            <div class="cstep">
              <span class="inum">۴</span>
              <div class="cstep-body">
                <b>فرستادن درخواست</b>
                <p>تا وقتی خودش درخواست را نپذیرد، او هیچ داده‌ای از تو نمی‌بیند.</p>
                <div class="btn-row" style="margin-top:8px">
                  <button class="btn" type="submit">فرستادن درخواست</button>
                </div>
                <p class="tiny" style="margin-top:10px">
                  کد دعوت داری؟ <a href="#invite-code">کد دعوت دارم</a>
                </p>
              </div>
            </div>
          </section>
        </form>

        <?php /* (v10) پذیرش با کد دعوت — همان چک‌باکس‌های خالی، بدون هیچ تیک پیش‌فرض.
                 فرم جداست چون فرم تودرتو در HTML مجاز نیست. */ ?>
        <section class="card" id="invite-code">
          <h3><?php echo joma_v2_icon('i-lock'); ?>کد دعوت دارم</h3>
          <p class="tiny">کد را همراهت برایت فرستاده است. با وارد کردن کد، همان صفحهٔ مجوزها می‌آید و
            تا خودت تیک نزنی هیچ‌چیزی دیده نمی‌شود. کد یک‌بار مصرف است و ۷ روز اعتبار دارد.</p>
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hammasir_action" value="invite_code_redeem">
            <label class="lbl" for="invite-code-input">کد دعوت</label>
            <input class="inp" id="invite-code-input" name="invite_code" dir="ltr" inputmode="latin"
                   autocomplete="off" placeholder="مثلاً 7QF2MP" aria-label="کد دعوت">

            <div class="prow" style="margin-top:8px">
              <span class="chip g" style="margin-top:2px">پایه</span>
              <div><b>خلاصهٔ وضعیت و پیشرفت کلی</b>
                <small>شمار روزهای ثبت‌شده، وضعیت فعالیت‌ها — این مورد پایه است و همیشه هست.</small></div>
            </div>
            <div class="prow">
              <input type="checkbox" name="perm_VIEW_PROGRESS" value="1" id="code-perm-progress">
              <label for="code-perm-progress"><b>پیشرفت و پوشش تجمیعی</b><small>درصدها و نمودارهای دوره</small></label>
            </div>
            <div class="prow">
              <input type="checkbox" name="perm_VIEW_ACTIVITY_DETAILS" value="1" id="code-perm-activities">
              <label for="code-perm-activities"><b>جزئیات فعالیت‌ها</b><small>کدام فعالیت‌ها را چقدر انجام داده‌ای</small></label>
            </div>
            <div class="prow">
              <input type="checkbox" name="perm_VIEW_MOOD" value="1" id="code-perm-mood">
              <label for="code-perm-mood"><b>شاخص‌های حال من</b><small>عددها و نمودارهای حال</small></label>
            </div>
            <div class="mood-note-warn">اگر «شاخص‌های حال من» را روشن کنی، <b>متن یادداشت‌های روزانه‌ات</b> هم دیده می‌شود.</div>

            <div class="pgroup">
              <b>گفت‌وگو</b>
              <div class="prow">
                <input type="checkbox" name="perm_msg_out" value="1" id="code-msg-out">
                <label for="code-msg-out"><b>من می‌توانم به او پیام بدهم</b><small>سقف روزانه از سرور</small></label>
              </div>
              <div class="prow">
                <input type="checkbox" name="perm_msg_in" value="1" id="code-msg-in">
                <label for="code-msg-in"><b>او می‌تواند به من پیام بدهد</b><small>سقف روزانه از سرور</small></label>
              </div>
              <p class="tiny" style="margin-top:6px">برای گفت‌وگو، هر دو گزینه لازم است.</p>
              <?php if ($hammasir_c_quota !== '') { ?><div class="quota"><?php echo e($hammasir_c_quota); ?></div><?php } ?>
            </div>

            <div class="share-box">
              <div class="prow" style="border-bottom:none">
                <input type="checkbox" name="status_share" value="1" id="code-status-share">
                <label for="code-status-share"><b>می‌خواهم همراه بداند چند وقت است چیزی ثبت نکرده‌ام</b>
                  <small>این نشانگر هیچ‌وقت حال، خلق یا یادداشتت را نشان نمی‌دهد — فقط وضعیت ثبت را.</small></label>
              </div>
            </div>

            <div class="btn-row" style="margin-top:8px">
              <button class="btn" type="submit">فعال‌کردن با کد</button>
            </div>
            <p class="tiny" style="margin-top:6px">تا وقتی کد را نفرستاده‌ای، می‌توانی از فهرست بالا هم همراه انتخاب کنی.</p>
          </form>
        </section>

        <?php if ($hammasir_c_latest) { ?>
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hammasir_action" value="client_request_cancel">
            <div class="btn-row"><button class="btn sec" type="submit">انصراف</button></div>
          </form>
        <?php } ?>
        <?php
    }
} else {
    // ----- حالت ۲: لینک باز — وضعیت + مجوزها + پیام + اقدام‌ها -----
    $hammasir_c_sl = hammasir_link_status_labels(); // برچسب وضعیت از یک نقطه (D4)
    $hammasir_c_status = isset($hammasir_c_sl[$hammasir_c_open['status']]) ? $hammasir_c_sl[$hammasir_c_open['status']] : $hammasir_c_open['status'];
    $hammasir_c_title = $hammasir_c_prov ? $hammasir_c_prov['title'] : ('همراه #' . (int) $hammasir_c_open['provider_user_id']);
    $hammasir_c_labels = hammasir_perm_view_labels();
    ?>
    <section class="card">
      <h2>همراه شما در این مسیر</h2>
      <div class="btn-row">
        <span class="chip"><?php echo e($hammasir_c_title); ?></span>
        <span class="chip"><?php echo e($hammasir_c_status); ?></span>
      </div>
      <?php if ($hammasir_c_open['status'] === 'PENDING') { ?>
        <p>تا قبل از پذیرش همراه، هیچ اطلاعاتی از شما برای او نمایش داده نمی‌شود.</p>
        <form class="card" method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="hammasir_action" value="link_cancel">
          <input type="hidden" name="link_id" value="<?php echo (int) $hammasir_c_open['id']; ?>">
          <div class="btn-row">
            <button class="btn sec" type="submit">لغو درخواست</button>
          </div>
        </form>
      <?php } else {
      // حالت ACTIVE (حکم PO — پنج-۷): کارت وضعیت + ویرایش مجوزها + تب گفتگو (جدا) + قطع ارتباط؛
      // توگل پیام‌رسانی فقط دست همراه است (D37) — از سمت مراجع حذف شد.
      ?>
      <details>
        <summary>ویرایش مجوزها</summary>
        <form class="card" method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="hammasir_action" value="perms_update">
          <input type="hidden" name="link_id" value="<?php echo (int) $hammasir_c_open['id']; ?>">
          <label>اجازه‌ی مشاهده</label>
          <div><label><input type="checkbox" checked disabled> <?php echo e($hammasir_c_labels['VIEW_SUMMARY']); ?> (پایه — همیشه فعال)</label></div>
          <div><label><input type="checkbox" name="perm_VIEW_PROGRESS" value="1" <?php if (isset($hammasir_c_perms['VIEW_PROGRESS']) && (int) $hammasir_c_perms['VIEW_PROGRESS'] === 1) echo 'checked'; ?>> <?php echo e($hammasir_c_labels['VIEW_PROGRESS']); ?></label></div>
          <div><label><input type="checkbox" name="perm_VIEW_ACTIVITY_DETAILS" value="1" <?php if (isset($hammasir_c_perms['VIEW_ACTIVITY_DETAILS']) && (int) $hammasir_c_perms['VIEW_ACTIVITY_DETAILS'] === 1) echo 'checked'; ?>> <?php echo e($hammasir_c_labels['VIEW_ACTIVITY_DETAILS']); ?></label></div>
          <div><label><input type="checkbox" name="perm_VIEW_MOOD" value="1" <?php if (isset($hammasir_c_perms['VIEW_MOOD']) && (int) $hammasir_c_perms['VIEW_MOOD'] === 1) echo 'checked'; ?>> <?php echo e($hammasir_c_labels['VIEW_MOOD']); ?></label></div>
          <div class="btn-row">
            <button class="btn" type="submit">ذخیره‌ی دسترسی‌ها</button>
          </div>
        </form>
      </details>
      <?php
      /* (C3/بند ۴) نشانگر وضعیت ثبت — کادر مستقل، یک‌کلیک خاموش/روشن.
         فقط وضعیت ثبت؛ هرگز حال، خلق یا یادداشت. */
      $hammasir_c_share = function_exists('joma_status_share_get') ? joma_status_share_get($hammasir_me) : false;
      ?>
      <div class="share-box">
        <b>نشانگر وضعیت ثبت</b>
        <small><?php echo $hammasir_c_share
            ? 'همراهت می‌داند چند وقت است چیزی ثبت نکرده‌ای. این نشانگر هیچ‌وقت حال، خلق یا یادداشتت را نشان نمی‌دهد.'
            : 'همراهت نشانگر وضعیت ثبت را نمی‌بیند.'; ?></small>
        <form method="post" style="margin-top:8px">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="hammasir_action" value="status_share_set">
          <input type="hidden" name="enabled" value="<?php echo $hammasir_c_share ? '0' : '1'; ?>">
          <button class="btn <?php echo $hammasir_c_share ? 'sec' : 'soft'; ?> sm" type="submit"><?php echo $hammasir_c_share ? 'خاموشش کن' : 'روشنش کن'; ?></button>
        </form>
      </div>
      <details>
        <summary>گفتگو</summary>
        <?php if ($hammasir_c_msg_on) { ?>
          <?php
          $hammasir_link = $hammasir_c_open;
          $hammasir_other_label = $hammasir_c_title;
          $hammasir_msg_on = true;
          include dirname(__FILE__) . '/hammasir_chat.php';
          ?>
        <?php } else { ?>
          <p>همراه شما در حال حاضر دریافت پیام را فعال نکرده است.</p>
        <?php } ?>
      </details>
      <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="hammasir_action" value="link_revoke">
        <input type="hidden" name="link_id" value="<?php echo (int) $hammasir_c_open['id']; ?>">
        <div class="btn-row">
          <button class="btn sec" type="submit">قطع ارتباط</button>
        </div>
      </form>
      <?php } ?>
    </section>
    <?php
}
