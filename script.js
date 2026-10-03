document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const hamburgerBtn = document.getElementById('hamburger-menu');
    const sidebar = document.getElementById('sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    function openSidebar() {
        if (sidebar) sidebar.classList.add('active');
        if (sidebarOverlay) sidebarOverlay.classList.add('active');
    }
    
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('active');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    }

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', openSidebar);
    }
    
    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', closeSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Flash Message auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000); // 5 seconds
    });

    // Password visibility toggle
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // Confirmation dialogs
    const confirmActions = document.querySelectorAll('.confirm-action');
    confirmActions.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to perform this action?')) {
                e.preventDefault();
            }
        });
    });
});
