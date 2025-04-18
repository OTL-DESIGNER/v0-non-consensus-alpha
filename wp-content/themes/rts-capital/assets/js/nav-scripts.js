document.addEventListener('DOMContentLoaded', function() {
    // Mobile dropdown toggles
    const mobileDropdowns = document.querySelectorAll('.mobile-dropdown > .mobile-link');
    
    if (mobileDropdowns) {
        mobileDropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.parentNode;
                const submenu = parent.querySelector('.mobile-submenu');
                
                // Toggle parent class
                parent.classList.toggle('show');
                
                // Toggle submenu display
                if (submenu) {
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                    } else {
                        submenu.style.display = 'block';
                    }
                }
            });
        });
    }
});