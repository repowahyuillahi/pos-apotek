<?php
/**
 * Footer — ditutup di akhir halaman.
 */
?>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= esc($brandName ?? 'Apotek') ?>. Hak cipta dilindungi undang-undang.</p>
        </div>
    </footer>

    <script>
        // Set csrf token untuk JS (kalau perlu)
        window.AP = window.AP || {};
        window.AP.csrfToken = '<?= csrf_token() ?>';
    </script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
