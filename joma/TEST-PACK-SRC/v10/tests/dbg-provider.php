<?php
require dirname(__FILE__) . '/../includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
if (function_exists('joma_companion_module_ready')) joma_companion_module_ready();
$pid = 217;
$links = hammasir_links_by_provider($pid, 'ACTIVE');
echo "ACTIVE links: " . count($links) . "\n";
$data = joma_provider_cards($pid, 'all');
echo "cards: " . count($data['cards']) . " counts=" . json_encode($data['counts'], JSON_UNESCAPED_UNICODE) . "\n";
foreach ($data['cards'] as $c) {
    echo "- " . $c['name'] . " state=" . $c['state']['key'] . " text=" . $c['state']['text'] . " days=" . var_export($c['state']['days'], true) . " start=" . $c['start_jalali'] . "\n";
}
$st = hammasir_store_load();
echo "store keys: " . implode(',', array_keys($st)) . "\n";
