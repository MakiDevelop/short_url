$(document).ready(function() {
    // 綁定按鈕點擊事件
    $('#shorten-button').click(function() {
        var url = $('#url-input').val();

        // 發送 POST 請求到後端 API
        $.ajax({
            url: '/shorten/',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ url: url }),
            success: function(response) {
                // 顯示生成的短網址和複製按鈕
                $('#short-url-display').html(`
                    <div class="input-group">
                        <input type="text" class="form-control" id="short-url" value="${response.short_url}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" id="copy-button">複製</button>
                        </div>
                    </div>
                `);

                // 綁定複製按鈕的點擊事件
                $('#copy-button').click(function() {
                    var shortUrlInput = document.getElementById('short-url');
                    shortUrlInput.select();
                    shortUrlInput.setSelectionRange(0, 99999);  // 選擇範圍
                    document.execCommand("copy");  // 複製到剪貼簿

                    // 更改按鈕文本顯示已複製
                    $('#copy-button').text('已複製');
                    
                    // 短暫延遲後恢復原來的文本
                    setTimeout(function() {
                        $('#copy-button').text('複製');
                    }, 2000);
                });
            },
            error: function() {
                $('#short-url-display').text("無法生成短網址，請確認輸入正確的網址格式。");
            }
        });
    });

    // 倒數計時功能
    let countdownElement = document.getElementById("countdown");
    if (countdownElement) {
        let countdownTime = 10;

        // 每秒更新一次倒計時
        let countdownInterval = setInterval(function() {
            countdownTime--;
            countdownElement.textContent = countdownTime;

            // 倒計時結束後，執行跳轉或其他操作
            if (countdownTime <= 0) {
                clearInterval(countdownInterval);
                // 在這裡添加您想要的跳轉邏輯
                window.location.href = countdownElement.getAttribute('data-url');  // 自動跳轉到目標URL
            }
        }, 1000);  // 每1000ms（1秒）更新一次
    }

    // 登入用戶縮短網址Modal
    $('#createUrlModal').on('hidden.bs.modal', function () {
        const form = $(this).find('form')[0];
        if (form) {
            form.reset();  // 确保表单被正确重置
        }
        // $('#create-url-form')[0].reset();  // 清空表單
        $('#image-preview').attr('src', '').hide();  // 清空圖片預覽
    });
    
    // 當目的網址欄位有輸入時，自動解析 OG 資訊
    $('#target-url').on('blur', function() {
        let url = $(this).val().trim(); // 去除前後空格
        
        // 確保 URL 欄位有值
        if (url === '') {
            return; // 如果網址为空，直接返回，不做解析
        }

        // 如果用户没有输入 http:// 或 https://，则自动加上 http://
        if (!/^https?:\/\//i.test(url)) {
            url = 'https://' + url;
            $(this).val(url); // 將修正後的網址写回输入框
        }

        // 確保 URL 非空並且合法后，發起 OG 資料解析
        $.ajax({
            url: '/parse_og_data',
            type: 'POST',
            data: JSON.stringify({ url: url }),
            contentType: 'application/json',
            success: function(response) {
                $('#title').val(response.title || '');
                $('#description').val(response.description || '');

                // 檢查 og:image 並更新圖片預覽
                if (response.image) {
                    $('#image-preview').attr('src', response.image).show();
                    $('#og-image-url').val(response.image); // 將 og:image URL 存入隱藏欄位
                } else {
                    $('#image-preview').attr('src', '').hide(); // 如果沒有圖片則隱藏
                    $('#og-image-url').val(''); // 清空隱藏欄位
                }
            },
            error: function() {
                // alert('無法解析 OG 資料');
                showModal('無法解析 OG 資料', "錯誤");
                setTimeout(() => {
                    $('#infoModal').modal('hide'); // 自動關閉 Modal
                }, 1500); // 2秒後自動關閉
            }
        });
    });

    // 當 Modal 關閉時，清空表單內容
    $('#createUrlModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

    // 登入用戶建立短網址
    $('#create-url-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '/create_short_url/',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showModal('短網址已建立！', "成功");
                setTimeout(() => {
                    $('#infoModal').modal('hide');  // 自動關閉 Modal
                    window.location.reload();
                }, 3000);  // 3秒後自動關閉
            },
            error: function(response) {
                if (response.status === 413) {  // 确保是 response.status，而不是 response.statu
                    showModal("上傳的文件太大，請選擇小於500KB的圖片。", "錯誤", false);
                } else if (!response.ok) {
                    showModal(`建立失敗，請重試。錯誤碼: ${response.status}`, "錯誤");
                }
                setTimeout(() => {
                    $('#infoModal').modal('hide');  // 自動關閉 Modal
                }, 5000);  // 2秒後自動關閉
            }
        });
    });

    // Modal圖片處理
    // 拖曳或點擊上傳圖片
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('image');
    const previewImage = document.getElementById('image-preview');

    // 阻止默認事件（讓它支持拖曳）
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // 拖曳進入和離開時的樣式變化
    ['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
    });

    // 處理拖曳上傳圖片
    dropArea.addEventListener('drop', handleDrop, false);
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const file = dt.files[0];
        handleFile(file);
    }

    // 點擊選擇圖片
    dropArea.addEventListener('click', () => {
        fileInput.click();  // 模擬點擊 input
    });

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        handleFile(file);
    });

    function handleFile(file) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = () => {
            previewImage.src = reader.result;
            previewImage.style.display = 'block';
        };
    }    
});

// 編輯短網址
function editUrl(shortCode) {
    $.ajax({
        url: `/get_url_data/${shortCode}`,
        type: 'GET',
        success: function(response) {
            // 填充表單的值
            $('#edit-short-code').val(shortCode);  // 設置 short_code
            $('#edit-original-url').val(response.original_url);  // 顯示但不能修改
            $('#edit-title').val(response.title || '');
            $('#edit-description').val(response.description || '');
            // 更新 Checkbox 的狀態
            $('#edit-direct-redirect').prop('checked', response.direct_redirect === true);

            $('#edit-tags').val(response.tags || '');
            $('#edit-utm-source').val(response.utm_source || '');
            $('#edit-utm-medium').val(response.utm_medium || '');
            $('#edit-utm-campaign').val(response.utm_campaign || '');
            $('#edit-utm-term').val(response.utm_term || '');
            $('#edit-utm-content').val(response.utm_content || '');

            // 如果有 image 数据，更新 image 預覽
            if (response.image) {
                $('#edit-image-preview').attr('src', response.image).show();  // 正確更新圖片
                $('#og_image_url').val(response.image);  // 同步設置og_image_url隱藏欄位
            } else {
                $('#edit-image-preview').attr('src', '').hide();  // 如果沒有圖片，清空並隱藏預覽
                $('#og_image_url').val('');  // 清空隱藏的 og_image_url
            }
        },
        error: function() {
            // alert('無法載入短網址資料');
            showModal('無法載入短網址資料', "錯誤");
            setTimeout(() => {
                $('#infoModal').modal('hide');  // 自動關閉 Modal
            }, 2000);  // 2秒後自動關閉
        }
    });

    // 編輯短網址 - 圖片拖曳或上傳
    const editDropArea = document.getElementById('edit-drop-area');
    const editImageInput = document.getElementById('edit-image');
    const editImagePreview = document.getElementById('edit-image-preview');

    // 防止瀏覽器打開文件
    editDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        editDropArea.classList.add('dragging');
    });

    editDropArea.addEventListener('dragleave', () => {
        editDropArea.classList.remove('dragging');
    });

    editDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        editDropArea.classList.remove('dragging');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                editImagePreview.src = event.target.result;
                $('#og_image_url').val('');  // 清空 og_image_url 隱藏欄位，表示這次是上傳圖片
            };
            reader.readAsDataURL(file);

            // 將文件放入 input 中，供表單提交使用
            editImageInput.files = e.dataTransfer.files;
        }
    });

    // 點擊上傳
    editDropArea.addEventListener('click', () => {
        editImageInput.click();
    });

    editImageInput.addEventListener('change', () => {
        const file = editImageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                editImagePreview.src = event.target.result;
                $('#og_image_url').val('');  // 清空 og_image_url 隱藏欄位，表示這次是上傳圖片
            };
            reader.readAsDataURL(file);
        }
    });

    $('#edit-image').on('change', function() {
        var fileInput = this.files[0];  // 取得上傳的圖片檔案
        var reader = new FileReader();  // 建立 FileReader 用於讀取圖片
    
        if (fileInput) {
            // 讀取並顯示上傳的圖片
            reader.onload = function(e) {
                $('#edit-image-preview').attr('src', e.target.result).show();
                $('#og_image_url').val('');  // 清空 og_image_url 隱藏欄位
                // console.log("上傳的圖片檔名：", fileInput.name);
            };
            reader.readAsDataURL(fileInput);  // 將圖片轉換為 Base64 URL
        } else {
            $('#edit-image-preview').attr('src', '').hide();  // 沒有圖片則清空預覽
            $('#og_image_url').val('');  // 清空 og_image_url 隱藏欄位
            // console.log("未選擇圖片");
        }
    });
}

// 更新短網址
function updateUrl() {
    const formData = new FormData($('#edit-form')[0]);  // 取得表單資料
    $.ajax({
        url: '/update_url',  // 後端更新 URL 的路由
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            showModal('短網址更新成功！', "成功");
            setTimeout(() => {
                $('#infoModal').modal('hide');  // 自動關閉 Modal
                window.location.href = '/dashboard';  // 正确的跳转方式
            }, 2000);  // 2秒後自動跳轉
            
        },
        error: function(response) {
            // showModal('更新失敗', "錯誤");
            if (response.status === 413) {
                showModal("上傳的文件太大，請選擇小於500KB的圖片。", "錯誤");
            } else if (!response.ok) {
                showModal("發生錯誤，請重試。", "錯誤");
            }
            setTimeout(() => {
                $('#infoModal').modal('hide');  // 自動關閉 Modal
            }, 2000);  // 2秒後自動關閉
        }
    });
}

// 複製到剪貼簿
function copyToClipboard(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
    // alert("短網址已複製！");
    showModal("短網址已複製！", "成功");
    setTimeout(() => {
        $('#infoModal').modal('hide');  // 自動關閉 Modal
    }, 2000);  // 2秒後自動關閉
}

// 生成QRCode
function generateQRCode(shortCode) {
    // 發送請求生成QR Code，並顯示在Modal中
    const qrcodeContent = document.getElementById('qrcode-content');
    qrcodeContent.innerHTML = `<img src="/generate_qrcode/${shortCode}" alt="QR Code" />`;
}

// 點擊分析
function analyzeClicks(shortCode) {
    window.location.href = `/analyze/${shortCode}`;
}

// 刪除短網址
function deleteUrl(shortCode) {
    if (confirm("確定要刪除這個短網址嗎？")) {
        $.ajax({
            url: `/delete_url/${shortCode}`,
            type: 'DELETE',
            success: function(response) {
                alert(response.message);
                // 重新加载页面或删除该行
                location.reload();
            },
            error: function(xhr) {
                // alert("刪除失敗: " + xhr.responseJSON.detail);
                showModal("刪除失敗: " + xhr.responseJSON.detail, "錯誤");
                setTimeout(() => {
                    $('#infoModal').modal('hide');  // 自動關閉 Modal
                }, 2000);  // 2秒後自動關閉
            }
        });
    }
}

// 分析
let clicksPieChart = null;  // 全局變數，儲存圖表對象

function analyzeClicks(shortCode) {
    $.ajax({
        url: `/analyze_clicks/${shortCode}`,
        type: 'GET',
        success: function(response) {
            // console.log(response);  // 在控制台打印出返回的数据

            const simplifiedUserAgentData = response.simplified_user_agent_data;
            const labels = Object.keys(simplifiedUserAgentData);
            const data = Object.values(simplifiedUserAgentData).map(item => item.clicks);  // 获取点击数
            const percentages = Object.values(simplifiedUserAgentData).map(item => item.percentage);  // 获取百分比

            // 如果圖表已經存在，先銷毀它
            if (clicksPieChart) {
                clicksPieChart.destroy();
            }

            // 繪製圓餅圖
            const ctx = document.getElementById('clicksPieChart').getContext('2d');
            clicksPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels.map((label, index) => `${label} (${percentages[index]}%)`),  // 标签显示百分比
                    datasets: [{
                        data: data,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'], // 自定义颜色
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    const label = tooltipItem.label || '';
                                    const clicks = data[tooltipItem.dataIndex];
                                    return `${label}: ${clicks} 點擊數`;
                                }
                            }
                        }
                    }
                }
            });

            // 顯示 Modal
            $('#analyzeModal').modal('show');
        },
        error: function() {
            // alert('無法載入分析數據');
            showModal('無法載入分析數據', "錯誤");
            setTimeout(() => {
                $('#infoModal').modal('hide');  // 自動關閉 Modal
            }, 2000);  // 2秒後自動關閉
        }
    });
}

// 顯示錯誤Modal
function showErrorModal(message) {
    $('#errorMessage').text(message);
    $('#errorModal').modal('show');
}

// 建立短網址請求
function createShortUrl() {
    // const form = document.getElementById('create-url-form');
    // const submitButton = form.querySelector('button[type="button"]');
    // const formData = new FormData(form);
    // const imageFile = document.getElementById('image').files[0];
    const formData = new FormData(document.getElementById('create-url-form'));
    const imageFile = document.getElementById('image').files[0];

    // 檢查圖片大小是否超過500KB
    if (imageFile && imageFile.size > 500 * 1024) {
        showModal("圖片大小超過500KB，請上傳較小的圖片。", "錯誤");
        return;
    }

    // 禁用提交按钮，防止重复提交
    const submitButton = document.querySelector('#create-url-form button[type="button"]');
    submitButton.disabled = true;

    // 發送請求
    fetch('/create_short_url/', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        submitButton.disabled = false;  // 恢复按钮

        // if (response.status === 413) {
        //     showModal("上傳的文件太大，請選擇小於500KB的圖片。", "錯誤");
        // } else if (!response.ok) {
        //     showModal("發生錯誤，請重試。", "錯誤");
        // } else {
        //     // 成功建立短網址，顯示成功Modal
        //     showModal("短網址已成功建立！::function createShortUrl", "成功");
        //     setTimeout(() => {
        //         location.reload();
        //     }, 2000);  // 延遲5秒
        // }
    })
    .catch(error => {
        submitButton.disabled = false;  // 恢复按钮
        showModal("發生錯誤，請重試。", "錯誤");
        console.error('Error:', error);
    });
}

// 顯示通用的 Modal
function showModal(message, title = "提示", closeOtherModals = true) {
    // 如果需要，关闭所有已打开的 Modal
    if (closeOtherModals) {
        $('.modal').modal('hide');  // 隐藏当前所有 Modal
    }

    // 等待当前 Modal 关闭后再显示新的 Modal
    setTimeout(() => {
        $('#modalMessage').text(message);
        $('#infoModalLabel').text(title);
        $('#infoModal').modal('show');  // 显示提示 Modal
    }, closeOtherModals ? 300 : 0);  // 如果关闭了其他 Modal，延迟显示；否则立即显示
}