<?php

namespace App\Services;

use App\Models\Penggajian;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PenggajianService
{
    /**
     * Generate penggajian dari pesanan
     */
    public function generateFromPesanan($tanggalDari, $tanggalSampai, $idBarber = null)
    {
        try {
            DB::beginTransaction();

            // Query pesanan yang sudah dibayar (paid) dalam rentang tanggal
            $query = Pesanan::with(['barber', 'pelanggan', 'jadwal']) // Tambahkan relasi jadwal
                ->whereDate('tgl_pesanan', '>=', $tanggalDari)
                ->whereDate('tgl_pesanan', '<=', $tanggalSampai)
                ->where('status_pembayaran', 'paid')
                ->whereNotIn('id', function($q) {
                    $q->select('id_pesanan')->from('penggajian');
                });

            // Filter berdasarkan barber jika dipilih
            if ($idBarber) {
                $query->where('id_barber', $idBarber);
            }

            $pesananList = $query->get();

            $generated = 0;
            foreach ($pesananList as $pesanan) {
                // Pastikan relasi ada
                if (!$pesanan->barber || !$pesanan->pelanggan) {
                    continue;
                }

                // Hitung total dari nominal + ongkos kirim
                $totalAmount = $pesanan->getTotalAmount();

                // Hitung potongan 5%
                $potongan = $totalAmount * 0.05;
                $totalGaji = $totalAmount - $potongan;

                // Ambil data jadwal
                $jadwalId = $pesanan->jadwal_id;
                $tanggalJadwal = null;
                $jamJadwal = null;

                if ($pesanan->jadwal) {
                    $tanggalJadwal = $pesanan->jadwal->tanggal;
                    $jamJadwal = $pesanan->jadwal->jam;
                }

                // Buat data penggajian
                Penggajian::create([
                    'id_pesanan' => $pesanan->id,
                    'id_barber' => $pesanan->id_barber,
                    'nama_barber' => $pesanan->barber->nama,
                    'rekening_barber' => $pesanan->barber->rekening_barber ?? 'Belum diset',
                    'id_pelanggan' => $pesanan->id_pelanggan,
                    'nama_pelanggan' => $pesanan->pelanggan->nama,
                    'tanggal_pesanan' => $pesanan->tgl_pesanan,
                    'jadwal_id' => $jadwalId,
                    'tanggal_jadwal' => $tanggalJadwal,
                    'jam_jadwal' => $jamJadwal,
                    'total_bayar' => $totalAmount,
                    'potongan' => $potongan,
                    'total_gaji' => $totalGaji,
                    'status' => 'belum lunas'
                ]);

                $generated++;
            }

            DB::commit();
            return $generated;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Generate penggajian tanpa relasi (fallback method)
     */
    public function generateFromPesananDirect($tanggalDari, $tanggalSampai, $idBarber = null)
    {
        try {
            DB::beginTransaction();

            // Query pesanan yang sudah dibayar (paid) dalam rentang tanggal
            $query = Pesanan::whereDate('tgl_pesanan', '>=', $tanggalDari)
                ->whereDate('tgl_pesanan', '<=', $tanggalSampai)
                ->where('status_pembayaran', 'paid')
                ->whereNotIn('id', function($q) {
                    $q->select('id_pesanan')->from('penggajian');
                });

            // Filter berdasarkan barber jika dipilih
            if ($idBarber) {
                $query->where('id_barber', $idBarber);
            }

            $pesananList = $query->get();

            $generated = 0;
            foreach ($pesananList as $pesanan) {
                // Ambil data barber dan pelanggan langsung dari database
                $barber = TukangCukur::find($pesanan->id_barber);
                $pelanggan = Pelanggan::find($pesanan->id_pelanggan);

                if (!$barber || !$pelanggan) {
                    continue;
                }

                // Hitung total dari nominal + ongkos kirim
                $totalAmount = $pesanan->nominal + $pesanan->ongkos_kirim;

                // Hitung potongan 5%
                $potongan = $totalAmount * 0.05;
                $totalGaji = $totalAmount - $potongan;

                // Ambil data jadwal menggunakan relasi
                $jadwal = null;
                if ($pesanan->jadwal_id) {
                    $jadwal = \App\Models\JadwalTukangCukur::find($pesanan->jadwal_id);
                }

                // Buat data penggajian
                Penggajian::create([
                    'id_pesanan' => $pesanan->id,
                    'id_barber' => $pesanan->id_barber,
                    'nama_barber' => $barber->nama,
                    'rekening_barber' => $barber->rekening_barber ?? 'Belum diset',
                    'id_pelanggan' => $pesanan->id_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama,
                    'tanggal_pesanan' => $pesanan->tgl_pesanan,
                    'jadwal_id' => $pesanan->jadwal_id,
                    'tanggal_jadwal' => $jadwal ? $jadwal->tanggal : null,
                    'jam_jadwal' => $jadwal ? $jadwal->jam : null,
                    'total_bayar' => $totalAmount,
                    'potongan' => $potongan,
                    'total_gaji' => $totalGaji,
                    'status' => 'belum lunas'
                ]);

                $generated++;
            }

            DB::commit();
            return $generated;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Proses pembayaran gaji
     */
    public function processBayar($idGajiArray, $buktiTransfer)
    {
        try {
            DB::beginTransaction();

            // Validasi input
            if (empty($idGajiArray) || !$buktiTransfer) {
                return [
                    'success' => false,
                    'message' => 'Data tidak lengkap'
                ];
            }

            // Upload bukti transfer
            $fileName = time() . '_' . $buktiTransfer->getClientOriginalName();
            $filePath = $buktiTransfer->storeAs('bukti_transfer', $fileName, 'public');

            // Update status penggajian
            $updated = Penggajian::whereIn('id_gaji', $idGajiArray)
                ->where('status', 'belum lunas')
                ->update([
                    'status' => 'lunas',
                    'bukti_transfer' => $filePath,
                    'updated_at' => now()
                ]);

            DB::commit();

            return [
                'success' => true,
                'updated' => $updated,
                'message' => 'Pembayaran berhasil diproses'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            // Hapus file jika ada error
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete penggajian
     */
    public function deletePenggajian($idGaji)
    {
        try {
            DB::beginTransaction();

            $penggajian = Penggajian::find($idGaji);

            if (!$penggajian) {
                return [
                    'success' => false,
                    'message' => 'Data penggajian tidak ditemukan'
                ];
            }

            // Hapus bukti transfer jika ada
            if ($penggajian->bukti_transfer && Storage::disk('public')->exists($penggajian->bukti_transfer)) {
                Storage::disk('public')->delete($penggajian->bukti_transfer);
            }

            $penggajian->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get laporan penggajian
     */
    public function getLaporanPenggajian($tanggalDari = null, $tanggalSampai = null, $idBarber = null)
    {
        $query = Penggajian::query();

        // Filter tanggal - menggunakan tanggal_pesanan karena itu yang kita simpan dari tgl_pesanan
        if ($tanggalDari) {
            $query->whereDate('tanggal_pesanan', '>=', $tanggalDari);
        }

        if ($tanggalSampai) {
            $query->whereDate('tanggal_pesanan', '<=', $tanggalSampai);
        }

        // Filter barber
        if ($idBarber) {
            $query->where('id_barber', $idBarber);
        }

        $data = $query->orderBy('tanggal_pesanan', 'desc')->get();

        // Hitung summary
        $summary = [
            'total_pesanan' => $data->count(),
            'total_bayar' => $data->sum('total_bayar'),
            'total_potongan' => $data->sum('potongan'),
            'total_gaji' => $data->sum('total_gaji'),
            'lunas' => $data->where('status', 'lunas')->count(),
            'belum_lunas' => $data->where('status', 'belum lunas')->count(),
        ];

        return [
            'data' => $data,
            'summary' => $summary
        ];
    }

    /**
     * Get statistik penggajian per barber
     */
    public function getStatistikPerBarber($bulan = null, $tahun = null)
    {
        $query = Penggajian::select(
                'nama_barber',
                DB::raw('COUNT(*) as total_pesanan'),
                DB::raw('SUM(total_bayar) as total_pendapatan'),
                DB::raw('SUM(potongan) as total_potongan'),
                DB::raw('SUM(total_gaji) as total_gaji'),
                DB::raw('COUNT(CASE WHEN status = "lunas" THEN 1 END) as sudah_dibayar'),
                DB::raw('COUNT(CASE WHEN status = "belum lunas" THEN 1 END) as belum_dibayar')
            )
            ->groupBy('nama_barber');

        if ($bulan) {
            $query->whereMonth('tanggal_pesanan', $bulan);
        }

        if ($tahun) {
            $query->whereYear('tanggal_pesanan', $tahun);
        }

        return $query->get();
    }

    /**
     * Get data untuk debugging
     */
    public function debugPesananData($tanggalDari, $tanggalSampai, $idBarber = null)
    {
        $query = Pesanan::whereDate('tgl_pesanan', '>=', $tanggalDari)
            ->whereDate('tgl_pesanan', '<=', $tanggalSampai)
            ->where('status_pembayaran', 'paid');

        if ($idBarber) {
            $query->where('id_barber', $idBarber);
        }

        $pesananList = $query->get();

        $debug = [];
        foreach ($pesananList as $pesanan) {
            $barber = TukangCukur::find($pesanan->id_barber);
            $pelanggan = Pelanggan::find($pesanan->id_pelanggan);

            $totalAmount = $pesanan->nominal + $pesanan->ongkos_kirim;
            $potongan = $totalAmount * 0.05;

            $debug[] = [
                'id_pesanan' => $pesanan->id,
                'id_barber' => $pesanan->id_barber,
                'id_pelanggan' => $pesanan->id_pelanggan,
                'barber_found' => $barber ? true : false,
                'barber_nama' => $barber ? $barber->nama : 'NOT FOUND',
                'barber_rekening' => $barber ? ($barber->rekening_barber ?? 'Belum diset') : 'NOT FOUND',
                'pelanggan_found' => $pelanggan ? true : false,
                'pelanggan_nama' => $pelanggan ? $pelanggan->nama : 'NOT FOUND',
                'nominal' => $pesanan->nominal,
                'ongkos_kirim' => $pesanan->ongkos_kirim,
                'total_amount' => $totalAmount,
                'potongan_5_persen' => $potongan,
                'total_gaji' => $totalAmount - $potongan,
                'status_pembayaran' => $pesanan->status_pembayaran,
                'tgl_pesanan' => $pesanan->tgl_pesanan,
                'id_transaksi' => $pesanan->id_transaksi
            ];
        }

        return $debug;
    }

    /**
     * Get summary pesanan untuk periode tertentu
     */
    public function getSummaryPesanan($tanggalDari, $tanggalSampai, $idBarber = null)
    {
        $query = Pesanan::whereDate('tgl_pesanan', '>=', $tanggalDari)
            ->whereDate('tgl_pesanan', '<=', $tanggalSampai)
            ->where('status_pembayaran', 'paid');

        if ($idBarber) {
            $query->where('id_barber', $idBarber);
        }

        $pesananList = $query->get();

        // Pesanan yang sudah ada di penggajian
        $pesananSudahDigaji = Pesanan::whereIn('id', function($q) {
            $q->select('id_pesanan')->from('penggajian');
        })->whereDate('tgl_pesanan', '>=', $tanggalDari)
          ->whereDate('tgl_pesanan', '<=', $tanggalSampai)
          ->where('status_pembayaran', 'paid');

        if ($idBarber) {
            $pesananSudahDigaji->where('id_barber', $idBarber);
        }

        $sudahDigaji = $pesananSudahDigaji->count();

        return [
            'total_pesanan_paid' => $pesananList->count(),
            'sudah_di_penggajian' => $sudahDigaji,
            'belum_di_penggajian' => $pesananList->count() - $sudahDigaji,
            'total_nominal' => $pesananList->sum('nominal'),
            'total_ongkir' => $pesananList->sum('ongkos_kirim'),
            'total_amount' => $pesananList->sum(function($p) {
                return $p->nominal + $p->ongkos_kirim;
            })
        ];
    }
}
