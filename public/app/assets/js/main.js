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

    var waitlistToast = document.getElementById('waitlist-toast');
    if (waitlistToast) {
        requestAnimationFrame(function () {
            waitlistToast.classList.add('is-visible');
        });

        var dismissToast = function () {
            if (waitlistToast.classList.contains('is-hiding')) {
                return;
            }
            waitlistToast.classList.remove('is-visible');
            waitlistToast.classList.add('is-hiding');
            setTimeout(function () {
                waitlistToast.remove();
            }, 450);
        };

        var closeBtn = waitlistToast.querySelector('.site-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', dismissToast);
        }

        setTimeout(dismissToast, 7000);
    }

    var waitlistForm = document.getElementById('waitlist-form');
    if (waitlistForm && window.location.hash === '#waitlist' && !waitlistToast) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            bootstrap.Collapse.getOrCreateInstance(waitlistForm, { toggle: false }).show();
        }
        waitlistForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
