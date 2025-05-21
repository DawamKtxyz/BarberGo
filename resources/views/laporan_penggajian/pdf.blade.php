<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penggajian</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h3 style="text-align: center;">Laporan Penggajian Barber</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barber</th>
                <th>Jumlah Potong</th>
                <th>Tarif</th>
                <th>Total Pendapatan</th>
                <th>Komisi</th>
                <th>Total Gaji</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporanPenggajian as $i => $laporan)
                @php
                    $jumlahPendapatan = $laporan->jumlah_potong * $laporan->tarif_per_potong;
                    $komisi = $jumlahPendapatan * 0.02;
                    $totalGaji = $jumlahPendapatan - $komisi;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $laporan->barber->nama ?? '-' }}</td>
                    <td>{{ $laporan->jumlah_potong }}</td>
                    <td>Rp {{ number_format($laporan->tarif_per_potong, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($jumlahPendapatan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($komisi, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($totalGaji, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
