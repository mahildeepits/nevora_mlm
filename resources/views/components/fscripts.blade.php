<!-- Library Bundle Script -->
<script src="{{ asset('template/hope-ui/js/core/libs.min.js') }}"></script>
<!-- External Library Bundle Script -->
<script src="{{ asset('template/hope-ui/js/core/external.min.js') }}"></script>
<!-- Widgetchart Script -->
<script src="{{ asset('template/hope-ui/js/charts/widgetcharts.js') }}"></script>
<!-- mapchart Script -->
<script src="{{ asset('template/hope-ui/js/charts/vectore-chart.js') }}"></script>
<script src="{{ asset('template/hope-ui/js/charts/dashboard.js') }}"></script>
<!-- fslightbox Script -->
<script src="{{ asset('template/hope-ui/js/plugins/fslightbox.js') }}"></script>
<!-- Settings Script -->
<script src="{{ asset('template/hope-ui/js/plugins/setting.js') }}"></script>
<!-- Slider-tab Script -->
<script src="{{ asset('template/hope-ui/js/plugins/slider-tabs.js') }}"></script>
<!-- Form Wizard Script -->
<script src="{{ asset('template/hope-ui/js/plugins/form-wizard.js') }}"></script>
<!-- AOS Animation Plugin-->
<script src="{{ asset('template/hope-ui/vendor/aos/dist/aos.js') }}"></script>
<!-- App Script -->
<script src="{{ asset('template/hope-ui/js/hope-ui.js') }}" defer></script>
<!-- Toastr JS -->
<script src="{{ asset('template/hope-ui/vendor/toastr/toastr.min.js') }}"></script>
<!-- Datatable -->
<script src="{{ asset('template/hope-ui/vendor/jQuery/datatable.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/bootstrap5/dataTables.min.js') }}"></script>
<!-- Datatable Buttons -->
<script src="{{ asset('template/hope-ui/vendor/datatables/buttons.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/bootstrap5/buttons.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/lib/jszip.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/lib/pdfmake.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/lib/pdfmake-vfs_fonts.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/lib/buttons.html5.min.js') }}"></script>
<script src="{{ asset('template/hope-ui/vendor/lib/buttons.print.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- My functions.js -->
<script src="{{ asset('assets/js/functions.js') }}?{{ rand(000000000, 999999999) }}"></script>
<script>
    function baseUrl() {
        return '{{ url('/') }}';
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons();
    }

    function updateThemeIcons() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const themeBtn = document.getElementById('theme-btn');
        if (themeBtn) {
            const icon = themeBtn.querySelector('i');
            if (icon) {
                if (currentTheme === 'dark') {
                    icon.className = 'fa-solid fa-sun font-16 text-warning';
                } else {
                    icon.className = 'fa-solid fa-moon font-16 text-secondary';
                }
            }
        }
        const lightIcons = document.querySelectorAll('.theme-icon-light');
        const darkIcons = document.querySelectorAll('.theme-icon-dark');

        if (currentTheme === 'dark') {
            lightIcons.forEach(el => el.classList.remove('d-none'));
            darkIcons.forEach(el => el.classList.add('d-none'));
        } else {
            lightIcons.forEach(el => el.classList.add('d-none'));
            darkIcons.forEach(el => el.classList.remove('d-none'));
        }
    }
    // document.addEventListener('DOMContentLoaded', updateThemeIcons);
    document.addEventListener('DOMContentLoaded', () => {
        updateThemeIcons();
        @if(session('success'))
            toastrMessage("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            toastrMessage("{{ session('error') }}", 'error');
        @endif
        @if(session('warning'))
            toastrMessage("{{ session('warning') }}", 'warning');
        @endif
        @if(session('info'))
            toastrMessage("{{ session('info') }}", 'info');
        @endif
    });
</script>

<!-- Web3 & SafePal Automated Integration Engine -->
<script src="{{ asset('assets/js/ethers.umd.min.js') }}"></script>
<script src="{{ asset('assets/js/web3-nevora.js') }}?v={{ time() }}"></script>

@stack('scripts')