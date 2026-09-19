<?php
require dirname(__FILE__) . '/../includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (function_exists('joma_companion_module_ready')) joma_companion_module_ready();
$ic = dirname(__FILE__) . '/../functions/companion_invitecode.php';
if (is_file($ic)) require_once $ic;
$p = user_by_username('ostad');
$d = joma_provider_cards((int) $p['id'], 'all');
echo "cards=" . count($d['cards']) . " counts=" . json_encode($d['counts'], JSON_UNESCAPED_UNICODE) . "\n";
foreach ($d['cards'] as $c) {
    echo sprintf("- %-14s key=%-9s days=%-4s chip=%-14s color=%-8s last=%s\n",
        $c['name'], $c['state']['key'], var_export($c['days'], true), $c['chip'], $c['color'], $c['last_label']);
}
echo "thresholds=" . json_encode(joma_provider_thresholds()) . "\n";
echo "codes=" . json_encode(joma_invitecode_view_list((int) $p['id']), JSON_UNESCAPED_UNICODE) . "\n";
