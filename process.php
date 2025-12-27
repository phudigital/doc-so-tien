<?php
$version = '1.3';

// ==========================================
// PHẦN 1: BACKEND (PHP)
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert') {
    header('Content-Type: application/json');

    $raw_amount = isset($_POST['amount']) ? str_replace('.', '', $_POST['amount']) : 0;
    $amount = floatval($raw_amount);
    $is_tax_included = isset($_POST['is_tax_included']) && $_POST['is_tax_included'] === 'true';
    
    // Lấy mức thuế, mặc định 0.08
    $vat_rate = isset($_POST['vat_rate']) ? floatval($_POST['vat_rate']) : 0.08;
    // Validate chỉ nhận 8% hoặc 10%, nếu sai quay về 8%
    if (!in_array($vat_rate, [0.08, 0.10])) {
        $vat_rate = 0.08;
    }

    // Validate giới hạn tiền
    if ($amount < 100000 || $amount > 100000000000) {
        echo json_encode(['status' => 'error', 'message' => 'Số tiền phải từ 100.000 đến 100 Tỷ.']);
        exit;
    }

    $suggestion = null;

    if ($is_tax_included) {
        // --- TÍNH NGƯỢC (Gross to Net) ---
        $post_tax = $amount;
        $pre_tax  = $amount / (1 + $vat_rate);
        $vat_amount = $post_tax - $pre_tax;

        // --- LOGIC ĐỀ XUẤT SỐ ĐẸP ---
        // VAT 8%  -> Chia hết cho 13.500 (Gốc 12.500 + VAT 1.000)
        // VAT 10% -> Chia hết cho 11.000 (Gốc 10.000 + VAT 1.000)
        $step = ($vat_rate == 0.10) ? 11000 : 13500;
        
        $steps_count = floor($amount / $step); 
        $suggested_total = $steps_count * $step;

        // Chỉ gợi ý nếu kết quả khác số nhập vào và > 0
        if ($suggested_total != $amount && $suggested_total >= 100000) {
            $s_pre = $suggested_total / (1 + $vat_rate);
            $s_vat = $suggested_total - $s_pre;
            
            $suggestion = [
                'amount_raw' => $suggested_total,
                'amount_fmt' => number_format($suggested_total, 0, ',', '.'),
                'pre_fmt'    => number_format($s_pre, 0, ',', '.'),
                'vat_fmt'    => number_format($s_vat, 0, ',', '.'),
                'diff'       => number_format($amount - $suggested_total, 0, ',', '.')
            ];
        }
    } else {
        // --- TÍNH XUÔI (Net to Gross) ---
        $pre_tax = $amount;
        $vat_amount = $amount * $vat_rate;
        $post_tax = $amount + $vat_amount;
    }

    // --- HÀM ĐỌC SỐ TIẾNG VIỆT ---
    function readNumberVi($number) {
        $dictionary  = array(
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
                $hundreds  = $number / 100;
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

    // --- HÀM ĐỌC SỐ TIẾNG ANH ---
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
                $hundreds = $number / 100; $remainder = $number % 100;
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

    $text_vi_raw = readNumberVi($amount) . ' đồng';
    $text_en_raw = convert_number_to_words_en($amount) . ' VND';

    echo json_encode([
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
            'vat_percent' => ($vat_rate * 100) // Trả về để update UI
        ]
    ]);
    exit;
}
?>