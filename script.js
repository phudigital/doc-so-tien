    const amountInput = document.getElementById('amount');
    const resultArea = document.getElementById('result-area');
    const emptyState = document.getElementById('empty-state');
    const suggestionBox = document.getElementById('suggestion-box');
    const vatRadios = document.getElementsByName('vat_rate');
    let currentRawSuggestion = 0; 

    // 1. Format input
    amountInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value) value = parseInt(value, 10).toLocaleString('vi-VN');
        e.target.value = value;
    });

    // 2. Tự động tính khi đổi Radio VAT
    vatRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if(amountInput.value) runCalculation();
        });
    });

    // 3. Submit Logic
    document.getElementById('calcForm').addEventListener('submit', function(e) {
        e.preventDefault();
        runCalculation();
    });

    function runCalculation() {
        const rawValue = amountInput.value.replace(/\./g, '');
        const isTaxIncluded = document.getElementById('is_tax_included').checked;
        
        // Lấy giá trị VAT từ radio
        let selectedVat = 0.08;
        for (const radio of vatRadios) {
            if (radio.checked) {
                selectedVat = radio.value;
                break;
            }
        }

        const errorDiv = document.getElementById('error-msg');
        suggestionBox.style.display = 'none';

        if (!rawValue || isNaN(rawValue)) {
            if(event.type === 'submit') showError("Vui lòng nhập số tiền hợp lệ.");
            return;
        }

        const amount = parseFloat(rawValue);
        if (amount < 100000 || amount > 100000000000) {
            showError("Số tiền phải từ 100 ngàn đến 100 tỷ.");
            return;
        }

        errorDiv.style.display = 'none';
        
        const formData = new FormData();
        formData.append('action', 'convert');
        formData.append('amount', amount);
        formData.append('is_tax_included', isTaxIncluded);
        formData.append('vat_rate', selectedVat);

        fetch('process.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                emptyState.style.display = 'none';
                resultArea.style.display = 'block';

                document.getElementById('res-pre').innerText = data.data.pre_tax;
                document.getElementById('res-vat').innerText = data.data.vat;
                // Cập nhật nhãn VAT
                document.getElementById('lbl-vat').innerText = `VAT (${data.data.vat_percent}%):`;
                
                document.getElementById('res-post').innerText = data.data.post_tax;

                document.getElementById('txt-sentence').innerText = data.data.text_sentence;
                document.getElementById('txt-title').innerText = data.data.text_title;
                document.getElementById('txt-upper').innerText = data.data.text_upper;
                document.getElementById('txt-en').innerText = data.data.text_en;

                if (data.data.suggestion) {
                    const sug = data.data.suggestion;
                    document.getElementById('sug-amount').innerText = sug.amount_fmt + ' VNĐ';
                    document.getElementById('sug-pre').innerText = sug.pre_fmt;
                    document.getElementById('sug-vat').innerText = sug.vat_fmt;
                    document.getElementById('sug-diff').innerText = sug.diff + ' VNĐ';
                    currentRawSuggestion = sug.amount_raw;
                    suggestionBox.style.display = 'block';
                }
            } else {
                showError(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            showError("Lỗi kết nối server.");
        });
    }

    document.getElementById('btn-apply-suggestion').addEventListener('click', function() {
        amountInput.value = currentRawSuggestion.toLocaleString('vi-VN');
        runCalculation();
        if(window.innerWidth < 992) {
             document.querySelector('.container').scrollIntoView({ behavior: 'smooth' });
        }
    });

    function showError(msg) {
        const err = document.getElementById('error-msg');
        err.innerText = msg;
        err.style.display = 'block';
        resultArea.style.display = 'none';
        emptyState.style.display = 'block';
    }

    function copyToClip(id, btn) {
        const text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originText = btn.innerText;
            btn.innerText = "ĐÃ COPY";
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerText = originText;
                btn.classList.remove('copied');
            }, 1500);
        });
    }