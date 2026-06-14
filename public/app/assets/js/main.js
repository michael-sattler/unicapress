/**
 * Universal site listener — session alerts, shared behaviors
 */
document.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.site-alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 6000);
    });
});
