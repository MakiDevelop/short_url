$(document).ready(function() {
    // 頁面載入時執行一次檢查
    checkDevice();

    // 當視窗大小改變時重新檢查
    $(window).resize(function () {
        checkDevice();
    });

    // 檢查URL參數中的錯誤
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error === 'inactive') {
        // 顯示模態框，告知使用者帳號已停用
        $('#inactiveModal').modal('show');
    }

    $('#create-url-form').on('submit', function(event) {
        event.preventDefault();  // 防止表單的預設送出行為

        var url = $('#target-url').val();

        $.ajax({
            url: '/shorten/',  // 提交表單的後端路由
            type: 'POST',
            contentType: 'application/json',  // 指定傳送 JSON 格式數據
            data: JSON.stringify({ url: url }),  // 將數據轉換為 JSON 格式
            success: function(response) {
                // 顯示生成的短網址
                $('#generated-url').text(response.short_url);
                $('#short-url-result').show();

                // 生成 QR Code
                $('#qrcode').attr('src', '/generate_qrcode/' + response.short_code);

                // 清空表單
                $('#create-url-form')[0].reset();
            },
            error: function(xhr) {  // 這裡需要將參數定義為 xhr
                if (xhr.status === 429) {
                    // 如果狀態碼是429，彈出限流警示
                    // alert('超過未登入者短時間內可生成短網址數量，請稍後再試');
                    showModal('超過未登入者短時間內可生成短網址數量，請稍後再試', "錯誤");
                } else {
                    // alert('生成短網址失敗，請重試');
                    showModal('生成短網址失敗，請重試', "錯誤");
                }
            }
        });
    });
});

// 複製到剪貼簿的功能
function copyToClipboard() {
    var $temp = $('<input>');
    $('body').append($temp);
    $temp.val($('#generated-url').text()).select();
    document.execCommand('copy');
    $temp.remove();
    // alert('短網址已複製到剪貼簿');
    showModal('短網址已複製到剪貼簿', "成功");
}

// JavaScript 判斷裝置大小並切換廣告顯示
function checkDevice() {
    var width = $(window).width();
    if (width >= 768) {
        // 桌機版
        $('.desktop-ad').show();
        $('.mobile-ad').hide();
    } else {
        // 手機版
        $('.desktop-ad').hide();
        $('.mobile-ad').show();
    }
}

// 顯示通用的 Modal
function showModal(message, title = "提示") {
    $('#modalMessage').text(message);
    $('#infoModalLabel').text(title);
    $('#infoModal').modal('show');
}