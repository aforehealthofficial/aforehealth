/**
 * AFOREHEALTH - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const toggleBtn = document.getElementById('admin-sidebar-toggle');
    const toggleBtnTop = document.getElementById('admin-sidebar-toggle-top');
    const sidebar = document.querySelector('.admin-sidebar');
    
    function toggleSidebar() {
        if (sidebar) sidebar.classList.toggle('show');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (toggleBtnTop) toggleBtnTop.addEventListener('click', toggleSidebar);

    // Close sidebar on click outside on mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !e.target.closest('#admin-sidebar-toggle') && !e.target.closest('#admin-sidebar-toggle-top')) {
                sidebar.classList.remove('show');
            }
        }
    });
});
