/**
 * AFOREHEALTH - Custom Confirmation Popups & Modal Engine
 * Replaces native browser alert/confirm with elegant, branded modals.
 */

window.AforeConfirm = (function () {
    let modalEl = null;
    let bsModal = null;
    let confirmBtn = null;
    let cancelBtn = null;
    let titleEl = null;
    let textEl = null;
    let iconWrap = null;
    let iconEl = null;
    let pendingCallback = null;

    function init() {
        modalEl = document.getElementById('aforeConfirmModal');
        if (!modalEl) return false;

        confirmBtn = document.getElementById('aforeConfirmOkBtn');
        cancelBtn = document.getElementById('aforeConfirmCancelBtn');
        titleEl = document.getElementById('aforeConfirmTitle');
        textEl = document.getElementById('aforeConfirmText');
        iconWrap = document.getElementById('aforeConfirmIconWrap');
        iconEl = document.getElementById('aforeConfirmIcon');

        if (window.bootstrap && window.bootstrap.Modal) {
            bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        if (confirmBtn) {
            confirmBtn.onclick = function () {
                if (bsModal) bsModal.hide();
                if (typeof pendingCallback === 'function') {
                    const cb = pendingCallback;
                    pendingCallback = null;
                    cb();
                }
            };
        }

        return true;
    }

    /**
     * Show custom confirmation modal
     * @param {Object} options
     *   - type: 'delete' | 'toggle' | 'logout' | 'warning' | 'info'
     *   - title: string
     *   - message: string
     *   - confirmText: string
     *   - confirmClass: string (e.g. 'btn-danger', 'btn-warning', 'btn-primary')
     *   - cancelText: string
     *   - onConfirm: function
     */
    function ask(options) {
        if (!modalEl && !init()) {
            // Fallback if modal DOM is missing
            if (confirm(options.message || 'Are you sure?')) {
                if (options.onConfirm) options.onConfirm();
            }
            return;
        }

        const type = options.type || 'warning';
        pendingCallback = options.onConfirm || null;

        // Set Texts
        titleEl.textContent = options.title || (
            type === 'delete' ? 'Delete Confirmation' :
            type === 'toggle' ? 'Change Status?' :
            type === 'logout' ? 'Sign Out Confirmation' : 'Are you sure?'
        );

        textEl.textContent = options.message || (
            type === 'delete' ? 'Are you sure you want to delete this item? This action cannot be undone.' :
            type === 'toggle' ? 'Are you sure you want to toggle the active status of this item?' :
            type === 'logout' ? 'Are you sure you want to end your current session and sign out?' :
            'Please confirm that you wish to proceed with this action.'
        );

        if (cancelBtn) {
            cancelBtn.textContent = options.cancelText || 'Cancel';
        }

        // Configure Icons & Styles per type
        iconWrap.style.transform = 'scale(1)';
        confirmBtn.className = 'btn px-4 py-2 rounded-pill fw-semibold shadow-sm';

        if (type === 'delete') {
            iconWrap.style.background = '#FEE2E2';
            iconWrap.style.color = '#DC2626';
            iconEl.className = 'bi bi-trash3-fill fs-1';
            confirmBtn.classList.add(options.confirmClass || 'btn-danger');
            confirmBtn.textContent = options.confirmText || 'Yes, Delete';
        } else if (type === 'toggle') {
            iconWrap.style.background = '#FEF3C7';
            iconWrap.style.color = '#D97706';
            iconEl.className = 'bi bi-arrow-repeat fs-1';
            confirmBtn.classList.add(options.confirmClass || 'btn-warning');
            confirmBtn.textContent = options.confirmText || 'Yes, Update Status';
        } else if (type === 'logout') {
            iconWrap.style.background = '#F3E8FF';
            iconWrap.style.color = '#7C3AED';
            iconEl.className = 'bi bi-box-arrow-right fs-1';
            confirmBtn.classList.add(options.confirmClass || 'btn-afh-primary');
            confirmBtn.style.background = '#8B263E';
            confirmBtn.style.borderColor = '#8B263E';
            confirmBtn.style.color = '#ffffff';
            confirmBtn.textContent = options.confirmText || 'Sign Out';
        } else if (type === 'info') {
            iconWrap.style.background = '#E0F2FE';
            iconWrap.style.color = '#0284C7';
            iconEl.className = 'bi bi-info-circle-fill fs-1';
            confirmBtn.classList.add(options.confirmClass || 'btn-info');
            confirmBtn.textContent = options.confirmText || 'Confirm';
        } else {
            iconWrap.style.background = '#FEF3C7';
            iconWrap.style.color = '#D97706';
            iconEl.className = 'bi bi-exclamation-triangle-fill fs-1';
            confirmBtn.classList.add(options.confirmClass || 'btn-warning');
            confirmBtn.textContent = options.confirmText || 'Continue';
        }

        if (bsModal) {
            bsModal.show();
        }
    }

    return {
        init: init,
        ask: ask
    };
})();

// Automatic Global Event Delegation
document.addEventListener('DOMContentLoaded', function () {
    window.AforeConfirm.init();

    // 1. Intercept Delete Forms (.form-delete-confirm or forms containing /delete in action)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Skip if already confirmed
        if (form.dataset.confirmed === 'true') return;

        const isDeleteForm = form.classList.contains('form-delete-confirm') || 
                             (form.action && form.action.includes('/delete')) ||
                             form.hasAttribute('data-confirm-delete');

        if (isDeleteForm) {
            e.preventDefault();
            const customTitle = form.getAttribute('data-confirm-title') || 'Delete Confirmation';
            const customMsg = form.getAttribute('data-confirm-msg') || 'Are you sure you want to permanently delete this item? This action cannot be reversed.';
            
            window.AforeConfirm.ask({
                type: 'delete',
                title: customTitle,
                message: customMsg,
                confirmText: 'Yes, Delete',
                onConfirm: function () {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
            return;
        }

        // 2. Intercept Status Toggle Forms
        const isToggleForm = form.classList.contains('form-toggle-confirm') || 
                             (form.action && (form.action.includes('/toggle') || form.action.includes('/toggle-status'))) ||
                             form.hasAttribute('data-confirm-toggle');

        if (isToggleForm) {
            e.preventDefault();
            const customTitle = form.getAttribute('data-confirm-title') || 'Update Status?';
            const customMsg = form.getAttribute('data-confirm-msg') || 'Are you sure you want to change the active status of this item?';
            
            window.AforeConfirm.ask({
                type: 'toggle',
                title: customTitle,
                message: customMsg,
                confirmText: 'Yes, Update Status',
                onConfirm: function () {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
            return;
        }

        // 3. Intercept Generic data-confirm Forms
        if (form.hasAttribute('data-confirm')) {
            e.preventDefault();
            const msg = form.getAttribute('data-confirm');
            const title = form.getAttribute('data-confirm-title') || 'Please Confirm';
            window.AforeConfirm.ask({
                type: 'warning',
                title: title,
                message: msg,
                confirmText: 'Confirm',
                onConfirm: function () {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    }, true);

    // 4. Intercept Logout links and buttons (a[href*="/logout"])
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href*="/logout"], [data-confirm-logout]');
        if (!link) return;

        // Skip if already confirmed
        if (link.dataset.confirmed === 'true') return;

        e.preventDefault();
        const targetUrl = link.href || link.getAttribute('data-logout-url') || '/logout';
        const isAdmin = targetUrl.includes('/admin');

        window.AforeConfirm.ask({
            type: 'logout',
            title: isAdmin ? 'Sign Out from Admin Panel?' : 'Log Out from AFOREHEALTH?',
            message: isAdmin ? 
                'You will need to enter your administrative credentials to log back in.' : 
                'You are about to sign out of your account. Items in your active cart will remain saved.',
            confirmText: 'Sign Out',
            onConfirm: function () {
                window.location.href = targetUrl;
            }
        });
    }, true);

    // 5. Intercept Click on elements with [data-confirm-url] or a[data-confirm]
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-confirm-url], a[data-confirm]');
        if (!el) return;

        // Skip if logout handled above
        if (el.closest('a[href*="/logout"], [data-confirm-logout]')) return;

        e.preventDefault();
        const url = el.getAttribute('data-confirm-url') || el.href;
        const title = el.getAttribute('data-confirm-title') || 'Are you sure?';
        const msg = el.getAttribute('data-confirm-msg') || el.getAttribute('data-confirm') || 'Do you want to proceed with this action?';
        const type = el.getAttribute('data-confirm-type') || 'warning';

        window.AforeConfirm.ask({
            type: type,
            title: title,
            message: msg,
            confirmText: type === 'delete' ? 'Yes, Delete' : 'Proceed',
            onConfirm: function () {
                window.location.href = url;
            }
        });
    }, true);
});
