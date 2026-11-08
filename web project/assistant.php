<?php
require_once __DIR__ . '/includes/init.php';
header('Content-Type: application/json; charset=utf-8');

function reply_json($text) {
    echo json_encode(['reply' => $text], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Get user input
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$msg = trim((string)($input['message'] ?? ''));

if ($msg === '' || mb_strlen($msg) > 500) {
    reply_json('Please write a short question (up to 500 characters).');
}

// 2. Fetch available products from database
$products = $pdo->query('SELECT id, name, category, price, stock, description FROM products ORDER BY id ASC LIMIT 100')->fetchAll();
$names = [];
foreach ($products as $p) {
    $names[$p['id']] = $p['name'];
}

// Helper: formats [[id]] into clickable HTML links and bold text
function format_reply($text, $names) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace_callback('/\[\[(\d+)\]\]/', function ($m) use ($names) {
        $id = (int)$m[1];
        return isset($names[$id]) ? '<a href="' . e(url('product.php?id=' . $id)) . '"><b>' . e($names[$id]) . '</b></a>' : '';
    }, $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $text);
    return nl2br($text);
}

// 3. Optional: Call Claude AI if API key is set
if (defined('ANTHROPIC_API_KEY') && ANTHROPIC_API_KEY !== '' && function_exists('curl_init')) {
    $catalog = '';
    foreach ($products as $p) {
        $catalog .= sprintf("[%d] %s | %s | %s | %s\n", $p['id'], $p['name'], $p['category'], money($p['price']), $p['stock'] > 0 ? 'in stock' : 'out of stock');
    }
    
    $systemPrompt = "You are the shopping assistant for NO CAP store. Help customers find products.\n"
        . "Rules:\n"
        . "- Recommend ONLY products from the catalog using [[id]], for example [[3]].\n"
        . "- Always reply in clear English under 100 words.\n"
        . "- Respect customer budget and preferences.\n\n"
        . "CATALOG:\n" . $catalog;

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => AI_SSL_VERIFY,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . ANTHROPIC_API_KEY,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => ANTHROPIC_MODEL,
            'max_tokens' => 400,
            'system' => $systemPrompt,
            'messages' => [['role' => 'user', 'content' => $msg]]
        ]),
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res !== false && $code === 200) {
        $d = json_decode($res, true);
        $aiText = '';
        foreach ($d['content'] ?? [] as $b) {
            if (($b['type'] ?? '') === 'text') $aiText .= $b['text'];
        }
        if (trim($aiText) !== '') {
            reply_json(format_reply($aiText, $names));
        }
    }
}

// 4. Smart Local Search (Runs automatically without API key)
$cleanMsg = strtolower($msg);

// Quick FAQ matching
if (preg_match('/(shipping|delivery|time|courier)/i', $cleanMsg)) {
    reply_json("We deliver nationwide within 2 to 4 business days. Delivery is free on orders above " . money(FREE_SHIPPING_ABOVE) . "!");
}
if (preg_match('/(payment|cod|cash)/i', $cleanMsg)) {
    reply_json("We offer Cash on Delivery (COD). You pay in cash when the parcel arrives at your door.");
}
if (preg_match('/(return|exchange|refund)/i', $cleanMsg)) {
    reply_json("We offer a 7-day hassle-free return and exchange policy on all unworn items.");
}
if (preg_match('/^(hi|hello|hey)/i', $cleanMsg)) {
    reply_json("Hello! Welcome to NO CAP. Tell me what product, style, or budget you are looking for!");
}

// Keyword matching against catalog
preg_match_all('/[\p{L}\p{N}]{3,}/u', $cleanMsg, $m);
$words = array_unique($m[0] ?? []);
$matches = [];

if ($words) {
    foreach ($products as $p) {
        if ($p['stock'] < 1) continue;
        $haystack = strtolower($p['name'] . ' ' . $p['category'] . ' ' . $p['description']);
        $score = 0;
        foreach ($words as $w) {
            if (strpos($haystack, $w) !== false) $score++;
        }
        if ($score > 0) {
            $matches[] = ['score' => $score, 'p' => $p];
        }
    }
    usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
}

// Budget check (e.g. "under 5000" or "3500")
if (preg_match('/(\d{3,6})/', $cleanMsg, $bm)) {
    $budget = (int)$bm[1];
    $pool = $matches ?: array_map(fn($p) => ['score' => 0, 'p' => $p], $products);
    $matches = array_filter($pool, fn($r) => $r['p']['price'] <= $budget && $r['p']['stock'] > 0);
}

$top = array_slice(array_values($matches), 0, 4);

if (!$top) {
    reply_json("I couldn't find a direct match. Try searching for hoodies, sneakers, tech gadgets, or specify your budget (e.g. 'under 4000').");
}

$output = "Here are the best recommendations for you:\n";
foreach ($top as $r) {
    $output .= "- [[" . $r['p']['id'] . "]] (" . money($r['p']['price']) . ")\n";
}

reply_json(format_reply($output, $names));
