jQuery(document).ready(function($) {
    let isRunning = false;
    let successCount = 0;
    let failedCount = 0;
    let totalToProcess = parseInt($('#remaining-count').text());
    let initialRemaining = totalToProcess;

    function addLog(message, type = 'info') {
        const log = $('#scraper-log');
        const entry = $('<p class="log-entry ' + type + '">[' + new Date().toLocaleTimeString() + '] ' + message + '</p>');
        log.prepend(entry);
    }

    function updateProgress() {
        const processed = successCount + failedCount;
        const percent = initialRemaining > 0 ? (processed / initialRemaining) * 100 : 100;
        $('.progress-bar-fill').css('width', percent + '%');
        $('#processed-count').text(processed);
        $('#success-count').text(successCount);
        $('#failed-count').text(failedCount);
    }

    function processBatch() {
        if (!isRunning) return;

        $.ajax({
            url: gsScraper.ajax_url,
            type: 'POST',
            data: {
                action: 'gs_scraper_process_batch',
                nonce: gsScraper.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    data.results.forEach(res => {
                        if (res.status === 'success') {
                            successCount++;
                            addLog('✓ ' + res.sku + ': ' + res.message, 'success');
                        } else {
                            failedCount++;
                            addLog('✗ ' + res.sku + ': ' + res.message, 'error');
                        }
                    });

                    $('#remaining-count').text(data.remaining);
                    updateProgress();

                    if (data.remaining > 0 && isRunning) {
                        processBatch();
                    } else {
                        isRunning = false;
                        $('#sync-status').text('Completed');
                        $('#start-scraper').prop('disabled', true);
                        $('#stop-scraper').prop('disabled', true);
                        addLog('Process completed.', 'info');
                    }
                } else {
                    addLog('Error: ' + response.data, 'error');
                    isRunning = false;
                    $('#stop-scraper').click();
                }
            },
            error: function() {
                addLog('Network error during batch processing.', 'error');
                isRunning = false;
                $('#stop-scraper').click();
            }
        });
    }

    $('#start-scraper').on('click', function() {
        isRunning = true;
        $(this).prop('disabled', true);
        $('#stop-scraper').prop('disabled', false);
        $('.sp-scraper-progress').slideDown();
        $('#sync-status').text('Processing...');
        addLog('Starting scraper...', 'info');
        processBatch();
    });

    $('#stop-scraper').on('click', function() {
        isRunning = false;
        $(this).prop('disabled', true);
        $('#start-scraper').prop('disabled', false);
        $('#sync-status').text('Stopped');
        addLog('Stopping scraper...', 'info');
    });
});
