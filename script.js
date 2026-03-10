const amountInput = document.getElementById("amount");
const resultArea = document.getElementById("result-area");
const emptyState = document.getElementById("empty-state");
const suggestionBox = document.getElementById("suggestion-box");
const vatRadios = document.getElementsByName("vat_rate");
const historyList = document.getElementById("history-list");
const btnClearHistory = document.getElementById("btn-clear-history");
let currentRawSuggestion = 0;

// Initialize history on page load
window.addEventListener("DOMContentLoaded", function () {
  loadHistory();
});

// 1. Format input
amountInput.addEventListener("input", function (e) {
  let value = e.target.value.replace(/\D/g, "");
  if (value) value = parseInt(value, 10).toLocaleString("vi-VN");
  e.target.value = value;
});

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
        resultArea.style.display = "block";

        document.getElementById("res-pre").innerText = data.data.pre_tax;
        document.getElementById("res-vat").innerText = data.data.vat;
        // Cập nhật nhãn VAT
        document.getElementById(
          "lbl-vat"
        ).innerText = `VAT (${data.data.vat_percent}%):`;

        document.getElementById("res-post").innerText = data.data.post_tax;

        document.getElementById("txt-sentence").innerText =
          data.data.text_sentence;
        document.getElementById("txt-title").innerText = data.data.text_title;
        document.getElementById("txt-upper").innerText = data.data.text_upper;
        document.getElementById("txt-en").innerText = data.data.text_en;

        if (data.data.suggestion) {
          const sug = data.data.suggestion;
          document.getElementById("sug-amount").innerText =
            sug.amount_fmt + " VNĐ";
          document.getElementById("sug-pre").innerText = sug.pre_fmt;
          document.getElementById("sug-vat").innerText = sug.vat_fmt;
          document.getElementById("sug-diff").innerText = sug.diff + " VNĐ";
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
    if (window.innerWidth < 992) {
      const anchor = document.getElementById("content-start");
      if (anchor) {
        anchor.scrollIntoView({ behavior: "smooth" });
      }
    }
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
    const originText = btn.innerText;
    btn.innerText = "ĐÃ COPY";
    btn.classList.add("copied");
    setTimeout(() => {
      btn.innerText = originText;
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
