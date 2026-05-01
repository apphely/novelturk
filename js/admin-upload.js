/**
 * Novel Türk - Admin Bulk Upload JavaScript
 * Handles ZIP file upload via AJAX with progress tracking
 */
(function ($) {
    'use strict';

    var $startBtn = $('#start-bulk-upload');
    var $progressWrap = $('#upload-progress');
    var $progressBar = $('#progress-bar');
    var $progressText = $('#progress-text');
    var $uploadLog = $('#upload-log');
    var isUploading = false;

    $startBtn.on('click', function () {
        if (isUploading) return;

        var novelId = $('#bulk_novel_id').val();
        var fileInput = document.getElementById('bulk_zip_file');
        var chapterStatus = $('#chapter_status').val();

        // Validation
        if (!novelId) {
            alert('Lütfen bir roman seçin!');
            return;
        }

        if (!fileInput.files || !fileInput.files[0]) {
            alert('Lütfen bir ZIP dosyası seçin!');
            return;
        }

        var file = fileInput.files[0];

        if (!file.name.toLowerCase().endsWith('.zip')) {
            alert('Sadece ZIP dosyaları kabul edilir!');
            return;
        }

        // Confirm
        var novelName = $('#bulk_novel_id option:selected').text();
        var fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        if (!confirm('Roman: ' + novelName + '\nDosya: ' + file.name + ' (' + fileSizeMB + ' MB)\n\nYükleme başlatılsın mı?')) {
            return;
        }

        // Start upload
        isUploading = true;
        $startBtn.prop('disabled', true).text('⏳ Yükleniyor...');
        $progressWrap.show();
        $progressBar.css('width', '0%');
        $progressText.text('ZIP dosyası yükleniyor...');
        $uploadLog.html('');

        addLog('info', '📦 ZIP dosyası yükleniyor: ' + file.name + ' (' + fileSizeMB + ' MB)');
        addLog('info', '📖 Roman: ' + novelName);

        // Prepare form data
        var formData = new FormData();
        formData.append('action', 'webnovel_bulk_upload');
        formData.append('nonce', webnovelAdmin.nonce);
        formData.append('novel_id', novelId);
        formData.append('chapter_status', chapterStatus);
        formData.append('zip_file', file);

        // Send AJAX request
        $.ajax({
            url: webnovelAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 600000, // 10 minutes timeout for large files
            xhr: function () {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 50); // Upload = 50%
                        $progressBar.css('width', percent + '%');
                        $progressText.text('Dosya yükleniyor... ' + percent + '%');
                    }
                });
                return xhr;
            },
            success: function (response) {
                if (response.success) {
                    var data = response.data;

                    $progressBar.css('width', '100%');
                    $progressText.html(
                        '<strong style="color:#22c55e;">✓ Tamamlandı!</strong> ' +
                        'Toplam: ' + data.total + ' | ' +
                        '<span style="color:#22c55e;">Başarılı: ' + data.success + '</span> | ' +
                        '<span style="color:#ef4444;">Hata: ' + data.errors + '</span>'
                    );

                    addLog('info', '');
                    addLog('info', '═══════════════════════════════════════');
                    addLog('info', '📊 İşlem Sonucu:');
                    addLog('info', '   Toplam dosya: ' + data.total);
                    addLog('success', '   Başarılı: ' + data.success);
                    if (data.errors > 0) {
                        addLog('error', '   Hata: ' + data.errors);
                    }
                    addLog('info', '═══════════════════════════════════════');

                    // Show individual results
                    if (data.log && data.log.length) {
                        addLog('info', '');
                        data.log.forEach(function (entry) {
                            addLog(entry.type, entry.message);
                        });
                    }
                } else {
                    $progressBar.css('width', '100%').css('background', '#ef4444');
                    $progressText.html('<strong style="color:#ef4444;">✗ Hata!</strong> ' + (response.data || 'Bilinmeyen hata'));
                    addLog('error', '❌ ' + (response.data || 'Bilinmeyen hata'));
                }
            },
            error: function (xhr, status, error) {
                $progressBar.css('width', '100%').css('background', '#ef4444');
                var errorMsg = 'Bağlantı hatası';
                if (status === 'timeout') {
                    errorMsg = 'Zaman aşımı - Dosya çok büyük olabilir. PHP max_execution_time değerini artırmayı deneyin.';
                } else if (xhr.responseText) {
                    errorMsg = error + ': ' + xhr.responseText.substring(0, 200);
                }
                $progressText.html('<strong style="color:#ef4444;">✗ Hata!</strong> ' + errorMsg);
                addLog('error', '❌ ' + errorMsg);
            },
            complete: function () {
                isUploading = false;
                $startBtn.prop('disabled', false).text('🚀 Yüklemeyi Başlat');
            }
        });
    });

    function addLog(type, message) {
        var className = '';
        if (type === 'success') className = 'style="color:#22c55e;"';
        else if (type === 'error') className = 'style="color:#ef4444;"';
        else if (type === 'info') className = 'style="color:#60a5fa;"';

        $uploadLog.append('<div ' + className + '>' + escapeHtml(message) + '</div>');
        $uploadLog.scrollTop($uploadLog[0].scrollHeight);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

})(jQuery);
