</div>
    
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Todo List App. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Enhanced Navigation JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.modern-navbar');
            const navbarToggler = document.querySelector('.modern-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            
            // Scroll effect for navbar
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                lastScrollTop = scrollTop;
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInsideNav = navbar.contains(event.target);
                const isNavOpen = navbarCollapse.classList.contains('show');
                
                if (!isClickInsideNav && isNavOpen) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
           
            document.querySelectorAll('.modern-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href') !== '#') {
                        this.classList.add('nav-loading');
                        setTimeout(() => {
                            this.classList.remove('nav-loading');
                        }, 1500);
                    }
                });
            });
        });
        
        
        function showProfileModal() {
            Swal.fire({
                title: 'Edit Profil',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Fitur edit profil akan segera tersedia!
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#4a90e2'
            });
        }
        
        function showSettingsModal() {
            Swal.fire({
                title: 'Pengaturan',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label">Tema</label>
                            <select class="form-select">
                                <option selected>Soft Blue & Slate</option>
                                <option>Dark Mode (Coming Soon)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bahasa</label>
                            <select class="form-select">
                                <option selected>Bahasa Indonesia</option>
                                <option>English (Coming Soon)</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-cog me-2"></i>
                            Pengaturan lanjutan akan segera tersedia!
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#4a90e2'
            });
        }
        
    
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);

      
        function confirmDelete(url, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

       
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('fade-in');
            }, index * 100);
        });
    </script>
</body>
</html>
