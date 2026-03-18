/* ============================================================
   QuoteCalc — script.js
   ============================================================ */

// ── DOM refs ─────────────────────────────────────────────
const amountInput       = document.getElementById('amount');
const calcForm          = document.getElementById('calcForm');
const errorMsg          = document.getElementById('error-msg');
const emptyState        = document.getElementById('empty-state');
const resultArea        = document.getElementById('result-area');
const suggestionBox     = document.getElementById('suggestion-box');
const historyList       = document.getElementById('history-list');
const btnClearHistory   = document.getElementById('btn-clear-history');
const suggestionDropdown = document.getElementById('suggestion-dropdown');
const vatRadios         = document.getElementsByName('vat_rate');

// ── State ─────────────────────────────────────────────────
let currentRawSuggestion = 0;

// ── SVG icon helpers ──────────────────────────────────────
const ICON_COPY = `<svg viewBox="0 0 24 24" aria-hidden="true">
  <rect x="9" y="9" width="13" height="13" rx="2"/>
  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
</svg>`;

const ICON_CHECK = `<svg viewBox="0 0 24 24" aria-hidden="true">
  <polyline points="20 6 9 17 4 12"/>
</svg>`;

// ── Utility: number_format ────────────────────────────────
function numFmt(number, thousandsSep = '.', decPoint = ',', decimals = 0) {
  const n = !isFinite(+number) ? 0 : +number;
  const prec = Math.abs(decimals);
  const s = (prec ? (Math.round(n * Math.pow(10, prec)) / Math.pow(10, prec)).toFixed(prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousandsSep);
  if ((s[1] || '').length < prec) { s[1] = s[1] || ''; s[1] += new Array(prec - s[1].length + 1).join('0'); }
  return s.join(decPoint);
}

function setTextIfExists(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

// ── Amount input: format & suggestions ───────────────────
amountInput.addEventListener('input', function (e) {
  const digits = e.target.value.replace(/\D/g, '');

  // Quick suggestions for short inputs (1-3 digits)
  if (digits && digits.length <= 3) {
    showSuggestions(digits);
  } else {
    hideSuggestions();
  }

  // Format with locale separators
  e.target.value = digits ? parseInt(digits, 10).toLocaleString('vi-VN') : '';
});

amountInput.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') hideSuggestions();
});

// Close dropdown on outside click
document.addEventListener('click', function (e) {
  if (e.target !== amountInput && !suggestionDropdown.contains(e.target)) {
    hideSuggestions();
  }
});

function showSuggestions(num) {
  const base = parseInt(num);
  if (isNaN(base) || base === 0) { hideSuggestions(); return; }

  const suggestions = [
    { value: base * 100000,    label: 'trăm ngàn' },
    { value: base * 1000000,   label: 'triệu'     },
    { value: base * 10000000,  label: 'chục triệu' },
  ];

  suggestionDropdown.innerHTML = suggestions.map(s =>
    `<div class="suggestion-item" role="option" tabindex="0"
          onclick="applySuggestion(${s.value})"
          onkeydown="if(event.key==='Enter')applySuggestion(${s.value})">
       <strong>${s.value.toLocaleString('vi-VN')}</strong>
       <small>${s.label}</small>
     </div>`
  ).join('');

  suggestionDropdown.classList.add('visible');
}

function hideSuggestions() {
  suggestionDropdown.classList.remove('visible');
}

function applySuggestion(value) {
  amountInput.value = value.toLocaleString('vi-VN');
  hideSuggestions();
  amountInput.focus();
}

// ── Auto-recalc on VAT change ─────────────────────────────
vatRadios.forEach(radio => {
  radio.addEventListener('change', () => {
    if (amountInput.value) runCalculation();
  });
});

// Recalc on toggle change
document.getElementById('is_tax_included').addEventListener('change', () => {
  if (amountInput.value) runCalculation();
});

// ── Form submit ───────────────────────────────────────────
calcForm.addEventListener('submit', function (e) {
  e.preventDefault();
  runCalculation();
});

// ── Core: run calculation ─────────────────────────────────
function runCalculation() {
  const rawValue = amountInput.value.replace(/\./g, '');

  if (!rawValue || isNaN(rawValue)) {
    showError('Vui lòng nhập số tiền hợp lệ.');
    return;
  }

  const amount = parseFloat(rawValue);
  if (amount < 100000 || amount > 100000000000) {
    showError('Số tiền phải từ 100 ngàn đến 100 tỷ.');
    return;
  }

  clearError();
  setBtnLoading(true);

  const isTaxIncluded = document.getElementById('is_tax_included').checked;
  let selectedVat = 0.08;
  for (const radio of vatRadios) {
    if (radio.checked) { selectedVat = radio.value; break; }
  }

  const params = new URLSearchParams({
    action: 'convert',
    amount,
    is_tax_included: isTaxIncluded,
    vat_rate: selectedVat,
  });

  fetch('process.php', {
    method: 'POST',
    body: params,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
    .then(async res => {
      const text = await res.text();
      try { return JSON.parse(text); }
      catch (err) {
        console.error('JSON parse error:', err, '\nRaw:', text);
        throw new Error('Server trả về dữ liệu không hợp lệ. Kiểm tra Console để biết thêm.');
      }
    })
    .then(data => {
      setBtnLoading(false);
      if (data.status === 'success') {
        renderResult(data.data);
        if (data.history) renderHistory(data.history);
      } else {
        showError(data.message);
      }
    })
    .catch(err => {
      setBtnLoading(false);
      console.error('Fetch error:', err);
      showError('Lỗi hệ thống: ' + err.message);
    });
}

// ── Render result ─────────────────────────────────────────
function renderResult(d) {
  // Show result, hide empty state
  emptyState.style.display = 'none';
  resultArea.style.display = 'flex';

  // Metrics
  setTextIfExists('res-pre',  d.pre_tax);
  setTextIfExists('res-vat',  d.vat);
  setTextIfExists('res-post', d.post_tax);
  setTextIfExists('lbl-vat',  `VAT (${d.vat_percent}%)`);

  // Text outputs
  setTextIfExists('txt-sentence', d.text_sentence);
  setTextIfExists('txt-en',       d.text_en);

  // Suggestion
  if (d.suggestion) {
    const sug = d.suggestion;
    setTextIfExists('sug-amount', sug.amount_fmt + ' VNĐ');
    setTextIfExists('sug-pre',    sug.pre_fmt);
    setTextIfExists('sug-vat',    sug.vat_fmt);
    setTextIfExists('sug-diff',   sug.diff + ' VNĐ');
    currentRawSuggestion = sug.amount_raw;
    suggestionBox.style.display = 'block';
  } else {
    suggestionBox.style.display = 'none';
  }
}

// ── Apply suggestion button ───────────────────────────────
document.getElementById('btn-apply-suggestion').addEventListener('click', () => {
  amountInput.value = currentRawSuggestion.toLocaleString('vi-VN');
  // Uncheck tax-included (suggestion is always the total)
  document.getElementById('is_tax_included').checked = true;
  runCalculation();
});

// ── Error helpers ─────────────────────────────────────────
function showError(msg) {
  errorMsg.textContent = msg;
  errorMsg.style.display = 'block';
  emptyState.style.display = 'flex';
  resultArea.style.display = 'none';
  suggestionBox.style.display = 'none';
}

function clearError() {
  errorMsg.style.display = 'none';
}

// ── Button loading state ──────────────────────────────────
function setBtnLoading(loading) {
  const btn = document.getElementById('btn-calc');
  if (!btn) return;
  btn.disabled = loading;
  btn.textContent = loading ? 'Đang tính…' : 'Tính nhanh';
}

// ── Copy to clipboard ─────────────────────────────────────
function copyToClip(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const text = el.textContent.trim();
  navigator.clipboard.writeText(text).then(() => {
    btn.innerHTML = ICON_CHECK;
    btn.classList.add('copied');
    setTimeout(() => {
      btn.innerHTML = ICON_COPY;
      btn.classList.remove('copied');
    }, 1500);
  }).catch(() => {
    // Fallback for older browsers
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    ta.remove();
    btn.innerHTML = ICON_CHECK;
    btn.classList.add('copied');
    setTimeout(() => { btn.innerHTML = ICON_COPY; btn.classList.remove('copied'); }, 1500);
  });
}

// ── History ───────────────────────────────────────────────
function formatTime(timestamp) {
  const d = new Date(timestamp * 1000);
  const hh = String(d.getHours()).padStart(2, '0');
  const mm = String(d.getMinutes()).padStart(2, '0');
  const day = d.getDate();
  const mon = d.getMonth() + 1;
  return `${hh}:${mm} · ${day}/${mon}`;
}

function renderHistory(history) {
  if (!history || history.length === 0) {
    historyList.innerHTML = '<div class="history-empty">Chưa có lịch sử</div>';
    btnClearHistory.style.display = 'none';
    return;
  }

  btnClearHistory.style.display = 'inline-flex';

  historyList.innerHTML = history.map(item => {
    const vatLabel = item.is_tax_included ? 'Có thuế' : 'Không thuế';
    const vatPct   = (item.vat_rate * 100).toFixed(0);
    return `
      <div class="history-item"
           role="button"
           tabindex="0"
           aria-label="${numFmt(item.amount)} VNĐ — ${vatLabel} — VAT ${vatPct}%"
           onclick="loadHistoryItem(${item.amount}, ${item.vat_rate}, ${item.is_tax_included ? 'true' : 'false'})"
           onkeydown="if(event.key==='Enter'||event.key===' ')loadHistoryItem(${item.amount}, ${item.vat_rate}, ${item.is_tax_included ? 'true' : 'false'})">
        <div>
          <div class="history-amount">${numFmt(item.amount)} đ</div>
          <div class="history-meta">
            <span>${vatLabel}</span>
            <span class="history-dot"></span>
            <span>VAT ${vatPct}%</span>
          </div>
        </div>
        <div class="history-time">${formatTime(item.timestamp)}</div>
      </div>`;
  }).join('');
}

function loadHistoryItem(amount, vatRate, isTaxIncluded) {
  amountInput.value = numFmt(amount);
  vatRadios.forEach(r => { r.checked = parseFloat(r.value) === vatRate; });
  document.getElementById('is_tax_included').checked = isTaxIncluded;
  runCalculation();

  // On mobile, scroll to top of calculator
  if (window.innerWidth < 900) {
    document.getElementById('content-start').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function loadHistory() {
  fetch('process.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'getHistory' }),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
    .then(res => res.json())
    .then(data => { if (data.status === 'success') renderHistory(data.history); })
    .catch(err => console.error('Load history error:', err));
}

btnClearHistory.addEventListener('click', () => {
  if (!confirm('Xóa toàn bộ lịch sử?')) return;
  fetch('process.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'clearHistory' }),
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  })
    .then(res => res.json())
    .then(data => { if (data.status === 'success') renderHistory([]); })
    .catch(err => console.error('Clear history error:', err));
});

// ── Init ──────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', loadHistory);
