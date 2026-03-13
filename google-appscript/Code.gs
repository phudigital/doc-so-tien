/**
 * QuoteCalc+ Google Apps Script
 * Version: 1.0.0
 * Tính Thuế VAT & Đọc Số Tiền Thành Chữ
 * 
 * Deploy: Triển khai > Web App mới > Ai cũng đc truy cập
 */

const APP_VERSION = '1.0.0';
const HISTORY_KEY = 'calc_history';
const MAX_HISTORY = 20;
const ONE_WEEK_MS = 7 * 24 * 60 * 60 * 1000;

/**
 * Serve the web app
 */
function doGet() {
  return HtmlService.createHtmlOutputFromFile('Index')
    .setTitle('QuoteCalc+ | Tính Thuế VAT & Đọc Số Tiền')
    .setFaviconUrl('https://img.icons8.com/fluency/48/calculator.png')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1.0')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

// ==================== MAIN CALCULATION ====================

function processCalculation(amount, vatRate, isTaxIncluded) {
  // Validate
  if (amount < 100000 || amount > 100000000000) {
    return { status: 'error', message: 'Số tiền phải từ 100.000 đến 100 Tỷ.' };
  }

  if (![0.08, 0.10].includes(vatRate)) {
    vatRate = 0.08;
  }

  let preTax, vatAmount, postTax, suggestion = null;

  if (isTaxIncluded) {
    postTax = amount;
    preTax = amount / (1 + vatRate);
    vatAmount = postTax - preTax;

    const step = (vatRate === 0.10) ? 11000 : 13500;
    const stepsCount = Math.floor(amount / step);
    const suggestedTotal = stepsCount * step;

    if (suggestedTotal !== amount && suggestedTotal >= 100000) {
      const sPre = suggestedTotal / (1 + vatRate);
      const sVat = suggestedTotal - sPre;
      suggestion = {
        amount_raw: suggestedTotal,
        amount_fmt: formatNumber(suggestedTotal),
        pre_fmt: formatNumber(Math.round(sPre)),
        vat_fmt: formatNumber(Math.round(sVat)),
        diff: formatNumber(amount - suggestedTotal)
      };
    }
  } else {
    preTax = amount;
    vatAmount = amount * vatRate;
    postTax = amount + vatAmount;
  }

  // Đọc số tiền
  const textViRaw = readNumberVi(amount) + ' đồng';
  const textEnRaw = convertNumberToWordsEn(amount) + ' VND';

  const result = {
    status: 'success',
    data: {
      pre_tax: formatNumber(Math.round(preTax)) + ' VNĐ',
      vat: formatNumber(Math.round(vatAmount)) + ' VNĐ (' + (vatRate * 100) + '%)',
      post_tax: formatNumber(Math.round(postTax)) + ' VNĐ',
      text_sentence: capitalizeFirst(textViRaw),
      text_title: capitalizeWords(textViRaw),
      text_upper: textViRaw.toUpperCase(),
      text_en: capitalizeFirst(textEnRaw),
      suggestion: suggestion,
      vat_percent: vatRate * 100
    }
  };

  // Save to history
  saveToHistory(amount, vatRate, isTaxIncluded, {
    pre_tax: Math.round(preTax),
    vat: Math.round(vatAmount),
    post_tax: Math.round(postTax)
  });

  result.history = getHistory();
  return result;
}

// ==================== NUMBER TO WORDS (VIETNAMESE) ====================

function readNumberVi(number) {
  const dictionary = {
    0: 'không', 1: 'một', 2: 'hai', 3: 'ba', 4: 'bốn',
    5: 'năm', 6: 'sáu', 7: 'bảy', 8: 'tám', 9: 'chín',
    10: 'mười', 11: 'mười một', 12: 'mười hai', 13: 'mười ba',
    14: 'mười bốn', 15: 'mười lăm', 16: 'mười sáu', 17: 'mười bảy',
    18: 'mười tám', 19: 'mười chín', 20: 'hai mươi', 30: 'ba mươi',
    40: 'bốn mươi', 50: 'năm mươi', 60: 'sáu mươi', 70: 'bảy mươi',
    80: 'tám mươi', 90: 'chín mươi'
  };

  const units = { 100: 'trăm', 1000: 'nghìn', 1000000: 'triệu', 1000000000: 'tỷ' };

  if (typeof number !== 'number' || isNaN(number)) return '';

  number = Math.round(number);

  if (number < 21) return dictionary[number] || '';

  if (number < 100) {
    const tens = Math.floor(number / 10) * 10;
    const unit = number % 10;
    let str = dictionary[tens];
    if (unit) {
      str += ' ' + (unit === 1 ? 'mốt' : (unit === 5 ? 'lăm' : dictionary[unit]));
    }
    return str;
  }

  if (number < 1000) {
    const hundreds = Math.floor(number / 100);
    const remainder = number % 100;
    let str = dictionary[hundreds] + ' trăm';
    if (remainder) {
      if (remainder < 10) str += ' linh ' + dictionary[remainder];
      else str += ' ' + readNumberVi(remainder);
    }
    return str;
  }

  // >= 1000
  const baseUnit = Math.pow(1000, Math.floor(Math.log(number) / Math.log(1000)));
  const numBaseUnits = Math.floor(number / baseUnit);
  const remainder = number % baseUnit;
  let str = readNumberVi(numBaseUnits) + ' ' + units[baseUnit];

  if (remainder) {
    str += ' ';
    if (remainder < 100 && baseUnit > 100) str += 'không trăm linh ';
    else if (remainder < 1000 && baseUnit > 1000 && remainder >= 100) str += 'không trăm ';
    str += readNumberVi(remainder);
  }

  return str;
}

// ==================== NUMBER TO WORDS (ENGLISH) ====================

function convertNumberToWordsEn(number) {
  const dictionary = {
    0: 'zero', 1: 'one', 2: 'two', 3: 'three', 4: 'four', 5: 'five',
    6: 'six', 7: 'seven', 8: 'eight', 9: 'nine', 10: 'ten',
    11: 'eleven', 12: 'twelve', 13: 'thirteen', 14: 'fourteen', 15: 'fifteen',
    16: 'sixteen', 17: 'seventeen', 18: 'eighteen', 19: 'nineteen',
    20: 'twenty', 30: 'thirty', 40: 'forty', 50: 'fifty',
    60: 'sixty', 70: 'seventy', 80: 'eighty', 90: 'ninety'
  };

  const units = { 100: 'hundred', 1000: 'thousand', 1000000: 'million', 1000000000: 'billion' };

  if (typeof number !== 'number' || isNaN(number)) return '';

  number = Math.round(number);

  if (number < 21) return dictionary[number] || '';

  if (number < 100) {
    const tens = Math.floor(number / 10) * 10;
    const unit = number % 10;
    let str = dictionary[tens];
    if (unit) str += '-' + dictionary[unit];
    return str;
  }

  if (number < 1000) {
    const hundreds = Math.floor(number / 100);
    const remainder = number % 100;
    let str = dictionary[hundreds] + ' hundred';
    if (remainder) str += ' and ' + convertNumberToWordsEn(remainder);
    return str;
  }

  const baseUnit = Math.pow(1000, Math.floor(Math.log(number) / Math.log(1000)));
  const numBaseUnits = Math.floor(number / baseUnit);
  const remainder = number % baseUnit;
  let str = convertNumberToWordsEn(numBaseUnits) + ' ' + units[baseUnit];
  if (remainder) str += ', ' + convertNumberToWordsEn(remainder);
  return str;
}

// ==================== HISTORY MANAGEMENT ====================

function saveToHistory(amount, vatRate, isTaxIncluded, result) {
  const props = PropertiesService.getUserProperties();
  let history = JSON.parse(props.getProperty(HISTORY_KEY) || '[]');

  const now = Date.now();
  // Remove entries older than 1 week
  history = history.filter(item => (now - item.timestamp) < ONE_WEEK_MS);

  // Add new entry
  history.unshift({
    timestamp: now,
    amount: amount,
    vat_rate: vatRate,
    is_tax_included: isTaxIncluded,
    pre_tax: result.pre_tax,
    vat: result.vat,
    post_tax: result.post_tax
  });

  // Keep max 20
  history = history.slice(0, MAX_HISTORY);

  props.setProperty(HISTORY_KEY, JSON.stringify(history));
}

function getHistory() {
  const props = PropertiesService.getUserProperties();
  let history = JSON.parse(props.getProperty(HISTORY_KEY) || '[]');
  const now = Date.now();
  return history.filter(item => (now - item.timestamp) < ONE_WEEK_MS);
}

function clearHistoryData() {
  PropertiesService.getUserProperties().deleteProperty(HISTORY_KEY);
  return { status: 'success', message: 'Đã xóa lịch sử' };
}

function fetchHistory() {
  return { status: 'success', history: getHistory() };
}

// ==================== UTILITY ====================

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function capitalizeFirst(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function capitalizeWords(str) {
  if (!str) return '';
  return str.replace(/(?:^|\s)\S/g, a => a.toUpperCase());
}

function getAppVersion() {
  return APP_VERSION;
}
