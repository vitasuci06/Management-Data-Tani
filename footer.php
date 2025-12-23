<!-- ! Footer -->
    <footer class="footer mt-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="footer-info">
                <h6 class="mb-1 fw-bold">Kelompok Tani Makmur</h6>
                <p class="mb-0 text-muted" style="font-size: 14px;">
                    Aplikasi Manajemen Data Petani & Hasil Panen
                </p>
            </div>
            <div class="footer-right text-end">
                <p class="mb-0 text-muted" style="font-size: 13px;">
                    Karangtengah I, Kecamatan Wonosari, Kabupaten Gunungkidul
                </p>
            </div>
        </div>
    </footer>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const dropdownButton = document.querySelector(".dropdown-toggle");
        const dropdownMenu   = document.querySelector(".dropdown-menu");

        // ❗ Jika dropdown tidak ada di halaman ini, hentikan script
        if (!dropdownButton || !dropdownMenu) return;

        dropdownButton.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle("show");
        });

        // Klik di luar → dropdown tertutup
        document.addEventListener("click", function () {
            dropdownMenu.classList.remove("show");
        });
    });
    </script>


    <!-- Chart library -->
    <!-- Icons library -->
    <script src="plugins/feather.min.js"></script>
    <!-- Custom scripts -->
    <script src="js/script.js"></script>
    <!-- Core JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/core/jquery-3.7.1.min.js"></script>
    <script src="js/core/popper.min.js"></script>
    <script src="js/core/bootstrap.min.js"></script>
    <script src="js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="js/plugin/chart.js/chart.min.js"></script>
    <script src="js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="js/plugin/chart-circle/circles.min.js"></script>
    <script src="js/plugin/datatables/datatables.min.js"></script>
    <script src="js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="js/plugin/jsvectormap/world.js"></script>
    <script src="js/tani.min.js"></script>
    <script src="js/plugin/sweetalert/sweetalert.min.js"></script>
    
</body>

</html>