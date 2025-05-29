<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penggajian Barber</title>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 3px solid #2c3e50;
            position: relative;
        }

        .logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .report-title {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .report-subtitle {
            font-size: 12px;
            color: #7f8c8d;
            font-style: italic;
        }

        .report-info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .info-row {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #34495e;
        }

        .table-container {
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #3498db !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        thead th {
            color: white !important;
            font-weight: 600 !important;
            padding: 12px 8px !important;
            text-align: center !important;
            font-size: 10px !important;
            border-right: 1px solid rgba(255,255,255,0.2) !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        th:last-child {
            border-right: none !important;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ecf0f1;
            border-right: 1px solid #ecf0f1;
            text-align: center;
            font-size: 10px;
        }

        td:last-child {
            border-right: none;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e3f2fd;
        }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        .currency {
            font-weight: 500;
            color: #27ae60;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-lunas {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-belum {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .summary-section {
            margin-top: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }

        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 11px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .summary-pendapatan { color: #3498db; }
        .summary-komisi { color: #e74c3c; }
        .summary-gaji { color: #27ae60; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 8px;
        }

        @media print {
            body {
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }
            .table-container {
                box-shadow: none;
            }
            thead {
                background: #3498db !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            thead th {
                background: #3498db !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <img src="{{ public_path('assets/images/barbergo-logo.jpg') }}" alt="BarberGo Logo" class="logo">
        <div class="company-name">BARBERGO</div>
        <div class="report-title">LAPORAN PENGGAJIAN BARBER</div>
        <div class="report-subtitle">Dicetak: {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('l, d F Y \p\u\k\u\l H:i') }} WIB</div>
    </div>

    <!-- Report Information -->
    <div class="report-info">
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('l, d F Y H:i:s') }} WIB</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Laporan:</span>
            <span class="info-value">{{ count($laporanPenggajian) }} data</span>
        </div>
    </div>

    @if(count($laporanPenggajian) > 0)
        <!-- Main Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5% !important; background: #3498db !important; color: white !important;">No</th>
                        <th style="width: 15% !important; background: #3498db !important; color: white !important;">Nama Barber</th>
                        <th style="width: 8% !important; background: #3498db !important; color: white !important;">Jumlah Pesanan</th>
                        <th style="width: 8% !important; background: #3498db !important; color: white !important;">Jumlah Pelanggan</th>
                        <th style="width: 15% !important; background: #3498db !important; color: white !important;">Total Pendapatan</th>
                        <th style="width: 12% !important; background: #3498db !important; color: white !important;">Potongan Komisi</th>
                        <th style="width: 15% !important; background: #3498db !important; color: white !important;">Total Gaji</th>
                        <th style="width: 12% !important; background: #3498db !important; color: white !important;">Status Gaji</th>
                        <th style="width: 10% !important; background: #3498db !important; color: white !important;">Periode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporanPenggajian as $i => $laporan)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-left">
                                <strong>{{ $laporan->nama_barber ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($laporan->jumlah_pesanan ?? 0) }}</strong>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($laporan->jumlah_pelanggan ?? 0) }}</strong>
                            </td>
                            <td class="text-right currency">
                                Rp {{ number_format($laporan->total_pendapatan ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right" style="color: #e74c3c;">
                                Rp {{ number_format($laporan->potongan_komisi ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right currency" style="font-weight: bold;">
                                Rp {{ number_format($laporan->total_gaji ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @php
                                    $statusClass = ($laporan->status_gaji ?? 'Belum Digaji') == 'Sudah Digaji' ? 'status-lunas' : 'status-belum';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $laporan->status_gaji ?? 'Belum Digaji' }}
                                </span>
                            </td>
                            <td class="text-center" style="font-size: 9px;">
                                {{ date('d/m/Y', strtotime($laporan->periode_dari ?? '')) }}<br>
                                <small style="color: #7f8c8d;">s/d</small><br>
                                {{ date('d/m/Y', strtotime($laporan->periode_sampai ?? '')) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-title">RINGKASAN TOTAL</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Total Pendapatan</span>
                    <div class="summary-value summary-pendapatan">
                        Rp {{ number_format($totalKeseluruhan['total_pendapatan'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Komisi</span>
                    <div class="summary-value summary-komisi">
                        Rp {{ number_format($totalKeseluruhan['total_komisi'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Gaji</span>
                    <div class="summary-value summary-gaji">
                        Rp {{ number_format($totalKeseluruhan['total_gaji'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Data Message -->
        <div class="no-data">
            <h3>Tidak Ada Data</h3>
            <p>Tidak ada data laporan penggajian yang tersedia untuk ditampilkan.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>BarberGo</strong> - Sistem Manajemen Barbershop</p>
        <p>Dicetak pada {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('l, d F Y \p\u\k\u\l H:i:s') }} WIB</p>
    </div>
</body>
</html>
