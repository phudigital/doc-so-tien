<?php
/**
 * Currency Reader Backend
 * Version: 1.3
 */

// Báo lỗi chi tiết để debug local
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Định nghĩa hằng số dự phòng cho mbstring nếu máy thiếu thư viện này
if (!defined('MB_CASE_UPPER')) define('MB_CASE_UPPER', 0);
if (!defined('MB_CASE_LOWER')) define('MB_CASE_LOWER', 1);
if (!defined('MB_CASE_TITLE')) define('MB_CASE_TITLE', 2);

// ==================== HISTORY MANAGEMENT ====================
$history_file = __DIR__ . '/history.json';
$one_week_ago = time() - (7 * 24 * 60 * 60); // 1 week in seconds

function roundDownToThousand($amount) {
    return (int) (floor($amount / 1000) * 1000);
}

function buildRoundedSuggestion($amount, $vat_rate) {
    $step = ($vat_rate == 0.10) ? 11000 : 13500;
    $steps_count = floor($amount / $step);
    $base_suggested_total = $steps_count * $step;
    $suggested_total = roundDownToThousand($base_suggested_total);

    if ($suggested_total >= $amount || $suggested_total < 100000) {
        return null;
    }

    $suggested_pre_tax = $suggested_total / (1 + $vat_rate);
    $suggested_vat = $suggested_total - $suggested_pre_tax;

    return [
        'amount_raw' => $suggested_total,
        'amount_fmt' => number_format($suggested_total, 0, ',', '.'),
        'pre_fmt'    => number_format($suggested_pre_tax, 0, ',', '.'),
        'vat_fmt'    => number_format($suggested_vat, 0, ',', '.'),
        'diff'       => number_format($amount - $suggested_total, 0, ',', '.')
    ];
}

function loadHistory() {
    global $history_file;
    if (!file_exists($history_file)) {
        return [];
    }
    $content = file_get_contents($history_file);
    return json_decode($content, true) ?? [];
}

function saveToHistory($amount, $vat_rate, $is_tax_included, $result) {
    global $history_file, $one_week_ago;
    
    $history = loadHistory();
    
    // Remove old entries (older than 1 week)
    $history = array_filter($history, function($item) use ($one_week_ago) {
        return $item['timestamp'] > $one_week_ago;
    });
    
    // Add new entry at the beginning
    $new_entry = [
        'timestamp' => time(),
        'amount' => $amount,
        'vat_rate' => $vat_rate,
        'is_tax_included' => $is_tax_included,
        'pre_tax' => $result['pre_tax'] ?? 0,
        'vat' => $result['vat'] ?? 0,
        'post_tax' => $result['post_tax'] ?? 0
    ];
    
    array_unshift($history, $new_entry);
    
    // Keep only last 20 entries
    $history = array_slice($history, 0, 20);
    
    // Save to file
    file_put_contents($history_file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getHistory() {
    global $one_week_ago;
    $history = loadHistory();
    
    // Clean up old entries
    $history = array_filter($history, function($item) use ($one_week_ago) {
        return $item['timestamp'] > $one_week_ago;
    });
    
    return array_values($history);
}

function clearHistory() {
    global $history_file;
    if (file_exists($history_file)) {
        unlink($history_file);
    }
}

// Xử lý request POST từ script.js
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    header('Content-Type: application/json');
    
    // Lấy dữ liệu từ $_POST (URLSearchParams sẽ đổ vào đây)
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'convert') {
        $raw_amount = isset($_POST['amount']) ? str_replace('.', '', $_POST['amount']) : 0;
        $amount = floatval($raw_amount);
        $is_tax_included = isset($_POST['is_tax_included']) && ($_POST['is_tax_included'] === 'true' || $_POST['is_tax_included'] === '1');
        
        $vat_rate = isset($_POST['vat_rate']) ? floatval($_POST['vat_rate']) : 0.08;
        if (!in_array($vat_rate, [0.08, 0.10])) {
            $vat_rate = 0.08;
        }

        if ($amount < 100000 || $amount > 100000000000) {
            echo json_encode(['status' => 'error', 'message' => 'Số tiền phải từ 100.000 đến 100 Tỷ.']);
            exit;
        }

        $suggestion = null;

        if ($is_tax_included) {
            $post_tax = $amount;
            $pre_tax  = $amount / (1 + $vat_rate);
            $vat_amount = $post_tax - $pre_tax;
            $suggestion = buildRoundedSuggestion($amount, $vat_rate);
        } else {
            $pre_tax = $amount;
            $vat_amount = $amount * $vat_rate;
            $post_tax = $amount + $vat_amount;
        }

        // --- HÀM ĐỌC SỐ ---
        if (!function_exists('readNumberVi')) {
            function readNumberVi($number) {
                $dictionary = array(
                    0 => 'không', 1 => 'một', 2 => 'hai', 3 => 'ba', 4 => 'bốn',
                    5 => 'năm', 6 => 'sáu', 7 => 'bảy', 8 => 'tám', 9 => 'chín',
                    10 => 'mười', 11 => 'mười một', 12 => 'mười hai', 13 => 'mười ba',
                    14 => 'mười bốn', 15 => 'mười lăm', 16 => 'mười sáu', 17 => 'mười bảy',
                    18 => 'mười tám', 19 => 'mười chín', 20 => 'hai mươi', 30 => 'ba mươi',
                    40 => 'bốn mươi', 50 => 'năm mươi', 60 => 'sáu mươi', 70 => 'bảy mươi',
                    80 => 'tám mươi', 90 => 'chín mươi', 100 => 'trăm', 1000 => 'nghìn',
                    1000000 => 'triệu', 1000000000 => 'tỷ',
                );
                if (!is_numeric($number)) return false;
                $string = "";
                switch (true) {
                    case $number < 21: $string = $dictionary[$number]; break;
                    case $number < 100:
                        $tens   = ((int) ($number / 10)) * 10;
                        $units  = $number % 10;
                        $string = $dictionary[$tens];
                        if ($units) $string .= ' ' . ($units==1 ? 'mốt' : ($units==5 ? 'lăm' : $dictionary[$units]));
                        break;
                    case $number < 1000:
                        $hundreds  = (int) floor($number / 100);
                        $remainder = $number % 100;
                        $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                        if ($remainder) {
                            if ($remainder < 10) $string .= ' linh ' . $dictionary[$remainder];
                            else $string .= ' ' . readNumberVi($remainder);
                        }
                        break;
                    default:
                        $baseUnit = pow(1000, floor(log($number, 1000)));
                        $numBaseUnits = (int) ($number / $baseUnit);
                        $remainder = $number % $baseUnit;
                        $string = readNumberVi($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                        if ($remainder) {
                            $string .= ' ';
                            if ($remainder < 100 && $baseUnit > 100) $string .= "không trăm linh "; 
                            elseif ($remainder < 1000 && $baseUnit > 1000 && $remainder >= 100) $string .= "không trăm ";
                            $string .= readNumberVi($remainder);
                        }
                        break;
                }
                return $string;
            }
        }

        if (!function_exists('convert_number_to_words_en')) {
            function convert_number_to_words_en($number) {
                $hyphen = '-'; $conjunction = ' and '; $separator = ', '; $dictionary = array(
                    0=>'zero', 1=>'one', 2=>'two', 3=>'three', 4=>'four', 5=>'five', 6=>'six', 7=>'seven', 8=>'eight', 9=>'nine',
                    10=>'ten', 11=>'eleven', 12=>'twelve', 13=>'thirteen', 14=>'fourteen', 15=>'fifteen', 16=>'sixteen', 17=>'seventeen', 18=>'eighteen', 19=>'nineteen',
                    20=>'twenty', 30=>'thirty', 40=>'forty', 50=>'fifty', 60=>'sixty', 70=>'seventy', 80=>'eighty', 90=>'ninety',
                    100=>'hundred', 1000=>'thousand', 1000000=>'million', 1000000000=>'billion'
                );
                if (!is_numeric($number)) return false;
                $string = null;
                switch (true) {
                    case $number < 21: $string = $dictionary[$number]; break;
                    case $number < 100:
                        $tens = ((int) ($number / 10)) * 10; $units = $number % 10;
                        $string = $dictionary[$tens]; if ($units) $string .= $hyphen . $dictionary[$units];
                        break;
                    case $number < 1000:
                        $hundreds = (int) floor($number / 100); $remainder = $number % 100;
                        $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                        if ($remainder) $string .= $conjunction . convert_number_to_words_en($remainder);
                        break;
                    default:
                        $baseUnit = pow(1000, floor(log($number, 1000)));
                        $numBaseUnits = (int) ($number / $baseUnit);
                        $remainder = $number % $baseUnit;
                        $string = convert_number_to_words_en($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                        if ($remainder) $string .= $separator . convert_number_to_words_en($remainder);
                        break;
                }
                return $string;
            }
        }

        // --- FALLBACK CHO MBSTRING ---
        if (!function_exists('mb_substr')) {
            function mb_substr($str, $start, $len = null, $encoding = 'UTF-8') {
                return substr($str, $start, $len);
            }
        }
        if (!function_exists('mb_convert_case')) {
            function mb_convert_case($str, $mode, $encoding = 'UTF-8') {
                if ($mode === MB_CASE_UPPER) return strtoupper($str);
                if ($mode === MB_CASE_TITLE) return ucwords(strtolower($str));
                return $str;
            }
        }
        if (!function_exists('mb_strtoupper')) {
            function mb_strtoupper($str, $encoding = 'UTF-8') {
                return strtoupper($str);
            }
        }

        $amount_for_words = (int) round($amount);
        $text_vi_raw = readNumberVi($amount_for_words) . ' đồng';
        $text_en_raw = convert_number_to_words_en($amount_for_words) . ' VND';

        $response_data = [
            'status' => 'success',
            'data' => [
                'pre_tax' => number_format($pre_tax, 0, ',', '.') . ' VNĐ',
                'vat' => number_format($vat_amount, 0, ',', '.') . ' VNĐ (' . ($vat_rate * 100) . '%)',
                'post_tax' => number_format($post_tax, 0, ',', '.') . ' VNĐ',
                'text_sentence' => mb_convert_case(mb_substr($text_vi_raw, 0, 1), MB_CASE_UPPER, "UTF-8") . mb_substr($text_vi_raw, 1),
                'text_title' => mb_convert_case($text_vi_raw, MB_CASE_TITLE, "UTF-8"),
                'text_upper' => mb_strtoupper($text_vi_raw, "UTF-8"),
                'text_en' => ucfirst($text_en_raw),
                'suggestion' => $suggestion,
                'vat_percent' => ($vat_rate * 100) 
            ]
        ];

        // Save to history
        $result = [
            'pre_tax' => $pre_tax,
            'vat' => $vat_amount,
            'post_tax' => $post_tax
        ];
        saveToHistory($amount, $vat_rate, $is_tax_included, $result);

        // Get updated history
        $response_data['history'] = getHistory();

        echo json_encode($response_data);
        exit;
    } else if ($action === 'getHistory') {
        echo json_encode([
            'status' => 'success',
            'history' => getHistory()
        ]);
        exit;
    } else if ($action === 'clearHistory') {
        clearHistory();
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã xóa lịch sử'
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ: ' . $action]);
        exit;
    }
}

// Nếu truy cập bằng GET (ví dụ qua trình duyệt)
if (isset($_GET['debug'])) {
    echo "<h1>Currency Reader Debug</h1>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    echo "<p>Server: 127.0.0.1:6688</p>";
    echo "<p>Status: Working</p>";
    exit;
}
