<?php
/**
 * جوما — کلید نقش و پنل «نقش‌های من» (بستهٔ C2 — سند ۲۴)
 * ------------------------------------------------------------------
 * • کاربر تک‌نقشه: این فایل هیچ خروجی‌ای نمی‌دهد — حتی یک پیکسل.
 * • کاربر چندنقشه: نوار بالای صفحه، کلید نقش کنار آواتار + پنل بازشو.
 * • موبایل: همان کلید، اولین بلوک پوشش «بیشتر».
 * • رنگ نقش: سه نشانهٔ محدود (نوار باریک بالای صفحه، هالهٔ پس‌زمینهٔ آرام،
 *   رنگ دکمه‌های همان بخش) — نه یک تم تازه.
 * هیچ‌جای این فایل مجوز نمی‌سازد؛ فقط نمایش را عوض می‌کند.
 */

/** نقش جاری → کلاس رنگ */
function joma_v2_role_class($role) {
    if ($role === 'provider') return 'role-provider';
    if ($role === 'admin') return 'role-admin';
    return 'role-client';
}

/** ردیف‌های پنل: نقش‌های واقعاً موجود کاربر */
function joma_v2_role_rows() {
    $u = current_user();
    $uid = $u ? (int) $u['id'] : 0;
    $cur = joma_role_current();
    $labels = joma_role_labels();
    $out = array();
    foreach (joma_roles_available() as $role) {
        $out[] = array(
            'key' => $role,
            'label' => isset($labels[$role]) ? $labels[$role] : $role,
            'sub' => joma_role_sub_label($role, $uid),
            'current' => ($role === $cur),
        );
    }
    return $out;
}

/** یک ردیف نقش (فرم POST به همان مسیر رسمی ماژول: index.php?p=hammasir) */
function joma_v2_role_row_html($row) {
    $page_key = ($row['key'] === 'provider') ? 'hammasir' : (($row['key'] === 'admin') ? 'admin_console' : 'dashboard');
    $html = '<form method="post" action="' . e(joma_url('index.php?p=hammasir')) . '" class="role-form">';
    $html .= csrf_field();
    $html .= '<input type="hidden" name="hammasir_action" value="mode_set">';
    $html .= '<input type="hidden" name="mode" value="' . e($row['key']) . '">';
    $html .= '<input type="hidden" name="redirect_to" value="' . e($page_key) . '">';
    $html .= '<button class="role-row' . ($row['current'] ? ' on' : '') . '" type="submit">';
    $html .= '<span class="role-dot ' . e(joma_v2_role_class($row['key'])) . '"></span>';
    $html .= '<span class="role-names"><b>' . e($row['label']) . '</b><small>' . e($row['sub']) . '</small></span>';
    $html .= $row['current']
        ? '<span class="chip g role-now">الآن</span>'
        : '<span class="role-go">' . joma_v2_icon('i-chev-l', 'ic') . '</span>';
    $html .= '</button></form>';
    return $html;
}

/** بدنهٔ مشترک پنل: نقش‌ها + پروفایل و تنظیمات + خروج */
function joma_v2_role_menu_html() {
    $html = '<div class="rolemenu-head">نقش‌های من</div>';
    foreach (joma_v2_role_rows() as $row) $html .= joma_v2_role_row_html($row);
    $html .= '<div class="rolemenu-sep"></div>';
    $html .= '<a class="role-next" href="' . e(joma_url('index.php?p=profile')) . '">' . joma_v2_icon('i-eye') . '<span>پروفایل و تنظیمات</span></a>';
    $html .= '<a class="role-next" href="' . e(joma_url('index.php?p=logout')) . '">' . joma_v2_icon('i-x') . '<span>خروج</span></a>';
    return $html;
}

/**
 * کلید نقش + پنل بازشو.
 * $variant = 'bar' (نوار بالای صفحه — دسکتاپ) یا 'sheet' (بلوک داخل پوشش «بیشتر» موبایل)
 */
function joma_v2_roleswitch($variant = 'bar') {
    if (!function_exists('joma_role_is_multi')) return;
    if (!joma_role_is_multi()) return; // تک‌نقشه: هیچ‌چیز

    $u = current_user();
    if (!$u) return;
    $uid = (int) $u['id'];
    $cur = joma_role_current();
    $labels = joma_role_labels();
    $role_label = isset($labels[$cur]) ? $labels[$cur] : $cur;
    $name = !empty($u['first_name']) ? $u['first_name'] : (!empty($u['full_name']) ? $u['full_name'] : $u['username']);
    $initial = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : '؟';
    $cls = joma_v2_role_class($cur);

    if ($variant === 'sheet') {
        echo '<div class="mob-roles ' . e($cls) . '">';
        echo '<div class="mob-head"><b>نقش‌های من</b><span class="chip">' . e($role_label) . '</span></div>';
        echo joma_v2_role_menu_html();
        echo '</div>';
        return;
    }

    echo '<div class="rolebar ' . e($cls) . '">';
    echo '<span class="role-strip"></span>';
    echo '<details class="rs-wrap">';
    echo '<summary class="roleswitch" aria-haspopup="menu">';
    echo '<span class="rs-avatar">' . e($initial) . '</span>';
    echo '<span class="rs-txt"><b>' . e($name) . '</b><small>' . e($role_label) . '</small></span>';
    echo joma_v2_icon('i-chev-d', 'ic chev');
    echo '</summary>';
    echo '<div class="rolemenu" role="menu">' . joma_v2_role_menu_html() . '</div>';
    echo '</details>';
    echo '</div>';
}

/** نوار باریک رنگ نقش — حتی برای کاربر تک‌نقشه، فقط رنگ نقش خودش (سند ۲۴ §۳٫۱) */
function joma_v2_role_strip() {
    if (!function_exists('joma_role_current')) return;
    $cur = joma_role_current();
    if ($cur === 'client') return; // کاربری = رنگ خانهٔ محصول؛ نوار اضافه لازم نیست
    echo '<div class="role-strip role-strip-block ' . e(joma_v2_role_class($cur)) . '"></div>';
}

/** صفحهٔ «این صفحه در حالت مشاور باز نمی‌شود» — آدرس مستقیم */
function joma_v2_role_guard_render($page) {
    $page_key = in_array($page, joma_role_personal_pages(), true) ? $page : 'dashboard';
    $titles = array('mood' => 'حال من', 'jooje' => 'جوجهٔ من', 'plan' => 'برنامهٔ من');
    $wanted = isset($titles[$page]) ? $titles[$page] : 'این صفحه';
    joma_header('حالت مشاور', array());
    ?>
    <div class="role-accent role-provider">
      <span class="role-strip role-strip-block"></span>
      <section class="card role-guard">
        <span class="chip ind">حالت مشاور</span>
        <h1 style="margin-top:8px">این صفحه در حالت مشاور باز نمی‌شود</h1>
        <p class="lede" style="margin-top:6px">
          در حالت مشاور، «حال من»، «جوجهٔ من» و «برنامهٔ من» بسته‌اند تا دادهٔ خودت با پروندهٔ مراجعان قاطی نشود.
          برای دیدن این صفحه، به نقش کاربری برگرد.
        </p>
        <p class="tiny">صفحهٔ خواسته‌شده: <?php echo e($wanted); ?></p>
        <div class="btn-row" style="margin-top:12px">
          <form method="post" action="<?php echo e(joma_url('index.php?p=hammasir')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hammasir_action" value="mode_set">
            <input type="hidden" name="mode" value="client">
            <input type="hidden" name="redirect_to" value="<?php echo e($page_key); ?>">
            <button class="btn" type="submit">برگشت به نقش کاربری</button>
          </form>
          <a class="btn sec" href="<?php echo e(joma_url('index.php?p=hammasir')); ?>">داشبورد مشاور</a>
        </div>
      </section>
    </div>
    <?php
    joma_footer();
}
