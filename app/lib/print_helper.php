<?php
/**
 * Print Helper — interface ke local print service.
 *
 * Di development: simpan log & preview di layar.
 * Di production: POST ke local helper (http://localhost:8080/print)
 * yang menerjemahkan ke ESC/POS dan mengirim ke Bluetooth printer.
 */

/**
 * Kirim data cetak ke local print helper.
 *
 * @param array $data
 *   Struktur:
 *   [
 *     'nama_apotek'  => string,
 *     'no_transaksi' => string,
 *     'tanggal'      => string,
 *     'kasir'        => string,
 *     'items'        => [ ['nama'=>..., 'jumlah'=>..., 'subtotal'=>...] ],
 *     'total'        => int,
 *     'metode_bayar' => string,
 *   ]
 */
function printToLocal(array $data): bool
{
    $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

    // Development: simpan log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/last_print.json';
    file_put_contents($logFile, $payload . "\n", FILE_APPEND);

    // Production: kirim ke local helper
    // $host = 'http://localhost:8080';
    // $ch = curl_init($host . '/print');
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //     'Content-Type: application/json',
    //     'Content-Length: ' . strlen($payload),
    // ]);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // $response = curl_exec($ch);
    // $success = curl_errno($ch) === 0;
    // curl_close($ch);
    // return $success;

    // Development mode: selalu "sukses" (log sudah tercatat)
    return true;
}

/**
 * Generate teks struk dalam format plain text (ESC/POS ready).
 * Digunakan oleh local helper jika ada.
 */
function generateStrukText(array $data): string
{
    $lines = [];

    // Header
    $lines[] = str_repeat('=', 40);
    $lines[] = '  ' . ($data['nama_apotek'] ?? 'APOTIK');
    $lines[] = '  ' . ($data['no_transaksi'] ?? '');
    $lines[] = '  Tanggal: ' . ($data['tanggal'] ?? '');
    $lines[] = '  Kasir: ' . ($data['kasir'] ?? '');
    $lines[] = '  ' . str_repeat('-', 36);

    // Items
    foreach ($data['items'] ?? [] as $item) {
        $nama = substr($item['nama'] ?? '', 0, 28);
        $qty  = $item['jumlah'] ?? 1;
        $sub  = number_format($item['subtotal'] ?? 0, 0, ',', '.');
        $lines[] = sprintf('  %-*s x%d    %s', 28, $nama, $qty, $sub);
    }

    $lines[] = '  ' . str_repeat('-', 36);
    $lines[] = sprintf('  %-36s %s', 'TOTAL', number_format($data['total'] ?? 0, 0, ',', '.'));
    $lines[] = '  ' . str_repeat('=', 40);
    $lines[] = '  Terima kasih!';
    $lines[] = '';

    return implode("\n", $lines);
}
