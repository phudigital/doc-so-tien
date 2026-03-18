const amountInput = document.getElementById("amount");
const resultArea = document.getElementById("result-area");
const emptyState = document.getElementById("empty-state");
const suggestionBox = document.getElementById("suggestion-box");
const vatRadios = document.getElementsByName("vat_rate");
const historyList = document.getElementById("history-list");
const btnClearHistory = document.getElementById("btn-clear-history");
let currentRawSuggestion = 0;

const copyIconSvg = `
  <svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M9 9h10v10H9z"></path>
    <path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"></path>
  </svg>
`;

const copiedIconSvg = `
  <svg viewBox="0 0 24 24" aria-hidden="true">
    <path d="M5 13l4 4L19 7"></path>
  </svg>
`;

function setTextIfExists(id, value) {
  const node = document.getElementById(id);
  if (node) {
    node.innerText = value;
  }
}

// Initialize history on page load
window.addEventListener("DOMContentLoaded", function () {
  loadHistory();
});

// 1. Format input and show suggestions
amountInput.addEventListener("input", function (e) {
  let value = e.target.value.replace(/\D/g, "");
  
  // Show suggestions if user types a number
  if (value && value.length <= 3) {
    showSuggestions(value);
  } else {
    hideSuggestions();
  }
  
  if (value) value = parseInt(value, 10).toLocaleString("vi-VN");
  e.target.value = value;
});

// Hide suggestions when clicking outside
document.addEventListener("click", function(e) {
  const dropdown = document.getElementById("suggestion-dropdown");
  if (e.target !== amountInput && !dropdown.contains(e.target)) {
    hideSuggestions();
  }
});

function showSuggestions(num) {
  const dropdown = document.getElementById("suggestion-dropdown");
  const baseNum = parseInt(num);
  
  if (isNaN(baseNum) || baseNum === 0) {
    hideSuggestions();
    return;
  }
  
  const suggestions = [
    { value: baseNum * 100000, label: "trăm ngàn" },
    { value: baseNum * 1000000, label: "triệu" },
    { value: baseNum * 10000000, label: "chục triệu" }
  ];
  
  dropdown.innerHTML = suggestions.map(sug => 
    `<div class="suggestion-item" onclick="applySuggestion(${sug.value})">
      <strong>${sug.value.toLocaleString('vi-VN')}</strong>
      <small>(${sug.label})</small>
    </div>`
  ).join('');
  
  dropdown.classList.add('visible');
}

function hideSuggestions() {
  const dropdown = document.getElementById("suggestion-dropdown");
  dropdown.classList.remove('visible');
}

function applySuggestion(value) {
  amountInput.value = value.toLocaleString('vi-VN');
  hideSuggestions();
  amountInput.focus();
}

// 2. Tự động tính khi đổi Radio VAT
vatRadios.forEach((radio) => {
  radio.addEventListener("change", function (e) {
    if (amountInput.value) runCalculation(e);
  });
});

// 3. Submit Logic
document.getElementById("calcForm").addEventListener("submit", function (e) {
  e.preventDefault();
  runCalculation(e);
});

function runCalculation(event) {
  const rawValue = amountInput.value.replace(/\./g, "");
  const isTaxIncluded = document.getElementById("is_tax_included").checked;

  // Lấy giá trị VAT từ radio
  let selectedVat = 0.08;
  for (const radio of vatRadios) {
    if (radio.checked) {
      selectedVat = radio.value;
      break;
    }
  }

  const errorDiv = document.getElementById("error-msg");
  suggestionBox.style.display = "none";

  if (!rawValue || isNaN(rawValue)) {
    if (event && event.type === "submit")
      showError("Vui lòng nhập số tiền hợp lệ.");
    return;
  }

  const amount = parseFloat(rawValue);
  if (amount < 100000 || amount > 100000000000) {
    showError("Số tiền phải từ 100 ngàn đến 100 tỷ.");
    return;
  }

  errorDiv.style.display = "none";

  const params = new URLSearchParams();
  params.append("action", "convert");
  params.append("amount", amount);
  params.append("is_tax_included", isTaxIncluded);
  params.append("vat_rate", selectedVat);

  fetch("process.php", {
    method: "POST",
    body: params,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
  })
    .then(async (res) => {
      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch (e) {
        console.group("Server Response Error");
        console.error("JSON Parse Error:", e);
        console.log("Raw Response received:", text);
        console.groupEnd();
        throw new Error(
          "Dữ liệu từ server không đúng định dạng JSON. Vui lòng kiểm tra Console (F12) để biết chi tiết."
        );
      }
    })
    .then((data) => {
      if (data.status === "success") {
        emptyState.style.display = "none";
        resultArea.style.display = "grid";

        setTextIfExists("res-pre", data.data.pre_tax);
        setTextIfExists("res-vat", data.data.vat);
        // Cập nhật nhãn VAT
        setTextIfExists("lbl-vat", `VAT (${data.data.vat_percent}%):`);

        setTextIfExists("res-post", data.data.post_tax);

        setTextIfExists("txt-sentence", data.data.text_sentence);
        setTextIfExists("txt-title", data.data.text_title);
        setTextIfExists("txt-upper", data.data.text_upper);
        setTextIfExists("txt-en", data.data.text_en);

        if (data.data.suggestion) {
          const sug = data.data.suggestion;
          setTextIfExists("sug-amount", sug.amount_fmt + " VNĐ");
          setTextIfExists("sug-pre", sug.pre_fmt);
          setTextIfExists("sug-vat", sug.vat_fmt);
          setTextIfExists("sug-diff", sug.diff + " VNĐ");
          currentRawSuggestion = sug.amount_raw;
          suggestionBox.style.display = "block";
        }

        // Update history display
        if (data.history) {
          displayHistory(data.history);
        }
      } else {
        showError(data.message);
      }
    })
    .catch((err) => {
      console.error("Fetch Error:", err);
      showError("Lỗi hệ thống: " + err.message);
    });
}

document
  .getElementById("btn-apply-suggestion")
  .addEventListener("click", function () {
    amountInput.value = currentRawSuggestion.toLocaleString("vi-VN");
    runCalculation();
  });

function showError(msg) {
  const err = document.getElementById("error-msg");
  err.innerText = msg;
  err.style.display = "block";
  resultArea.style.display = "none";
  emptyState.style.display = "block";
}

function copyToClip(id, btn) {
  const text = document.getElementById(id).innerText;
  navigator.clipboard.writeText(text).then(() => {
    btn.innerHTML = copiedIconSvg;
    btn.classList.add("copied");
    setTimeout(() => {
      btn.innerHTML = copyIconSvg;
      btn.classList.remove("copied");
    }, 1500);
  });
}

// ========== HISTORY FUNCTIONS ==========

function formatTime(timestamp) {
  const date = new Date(timestamp * 1000);
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");
  const day = date.getDate();
  const month = date.getMonth() + 1;
  
  return `${hours}:${minutes} - ${day}/${month}`;
}

function displayHistory(history) {
  if (!history || history.length === 0) {
    historyList.innerHTML = '<div class="history-empty">Chưa có lịch sử</div>';
    btnClearHistory.style.display = "none";
    return;
  }

  btnClearHistory.style.display = "inline-block";
  historyList.innerHTML = history.map((item) => `
    <div class="history-item" onclick="loadHistoryItem(${item.amount}, ${item.vat_rate}, ${item.is_tax_included ? 'true' : 'false'})">
      <div class="history-item-content">
        <div class="history-amount">${number_format(item.amount, 0, ',', '.')} VNĐ</div>
        <div class="history-meta">
          ${item.is_tax_included ? 'Có thuế' : 'Không thuế'} • VAT ${(item.vat_rate * 100).toFixed(0)}%
        </div>
      </div>
      <div class="history-time">${formatTime(item.timestamp)}</div>
    </div>
  `).join("");
}

function number_format(number, decimals, decPoint, thousandsSep) {
  number = (number + '').replace(/[^0-9+\-E.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep,
    dec = (typeof decPoint === 'undefined') ? '.' : decPoint,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

function loadHistoryItem(amount, vatRate, isTaxIncluded) {
  amountInput.value = number_format(amount, 0, ',', '.');
  
  // Set VAT rate
  vatRadios.forEach((radio) => {
    radio.checked = parseFloat(radio.value) === vatRate;
  });
  
  // Set tax included checkbox
  document.getElementById("is_tax_included").checked = isTaxIncluded;
  
  // Run calculation
  runCalculation();
  
  // Scroll to top
  if (window.innerWidth < 1024) {
    const anchor = document.getElementById("content-start");
    if (anchor) {
      anchor.scrollIntoView({ behavior: "smooth" });
    }
  }
}

function loadHistory() {
  const params = new URLSearchParams();
  params.append("action", "getHistory");

  fetch("process.php", {
    method: "POST",
    body: params,
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        displayHistory(data.history);
      }
    })
    .catch((err) => console.error("History Load Error:", err));
}

btnClearHistory.addEventListener("click", function () {
  if (confirm("Bạn có chắc muốn xóa toàn bộ lịch sử?")) {
    const params = new URLSearchParams();
    params.append("action", "clearHistory");

    fetch("process.php", {
      method: "POST",
      body: params,
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          loadHistory();
        }
      })
      .catch((err) => console.error("Clear History Error:", err));
  }
});
