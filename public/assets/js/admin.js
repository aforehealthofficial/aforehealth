/**
 * AFOREHEALTH - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('admin-sidebar-toggle');
    const toggleBtnTop = document.getElementById('admin-sidebar-toggle-top');
    const collapseBtn = document.getElementById('admin-sidebar-collapse-btn');
    const sidebar = document.querySelector('.admin-sidebar');
    
    // Restore desktop sidebar collapsed state
    if (window.innerWidth >= 992 && localStorage.getItem('admin_sidebar_collapsed') === 'true' && sidebar) {
        sidebar.classList.add('collapsed');
    }

    // Toggle Desktop Collapse
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('admin_sidebar_collapsed', sidebar.classList.contains('collapsed'));
            }
        });
    }

    // Mobile Sidebar Toggle
    function toggleMobileSidebar() {
        if (sidebar) sidebar.classList.toggle('show');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleMobileSidebar);
    if (toggleBtnTop) toggleBtnTop.addEventListener('click', toggleMobileSidebar);

    // Close sidebar on click outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !e.target.closest('#admin-sidebar-toggle') && !e.target.closest('#admin-sidebar-toggle-top')) {
                sidebar.classList.remove('show');
            }
        }
    });
});
