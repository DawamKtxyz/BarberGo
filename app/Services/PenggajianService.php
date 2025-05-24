<?php

namespace App\Services;

use App\Models\Penggajian;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PenggajianService
{
    /**
     * Generate data penggajian dari pesanan
     */
    public function generateFromPesanan($tanggalDari, $tanggalSampai, $idBarber = null)
    {
        $query = Pesanan::with(['barber', 'pelanggan'])
            ->whereDate('created_at', '>=', $tanggalDari)
            ->whereDate('created_at', '<=', $tanggalSampai)
            ->where('status', 'selesai');

        if ($idBarber) {
            $query->where('id_barber', $idBarber);
        }

        $pesanan = $query->get();
        $generated = 0;

        foreach ($pesanan as $p) {
            // Cek apakah sudah ada di penggajian
            $exists = Penggajian::where('id_pesanan', $p->id)->exists();

            if (!$exists && $p->barber && $p->pelanggan) {
                $totalBayar = $p->nominal + ($p->ongkos_kirim ?? 0);

                Penggajian::create([
                    'id_pesanan' => $p->id,
                    'id_barber' => $p->id_barber,
                    'nama_barber' => $p->barber->nama,
                    'rekening_barber' => $p->barber->rekening_barber ?? '-',
                    'id_pelanggan' => $p->id_pelanggan,
                    'nama_pelanggan' => $p->pelanggan->nama,
                    'tanggal_pesanan' => $p->created_at,
                    'total_bayar' => $totalBayar,
                ]);
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * Proses pembayaran gaji multiple
     */
    public function processBayar(array $idGaji, $buktiTransfer)
    {
        try {
            // Upload bukti transfer
            $buktiPath = $buktiTransfer->store('bukti_transfer', 'public');

            // Update status menjadi lunas untuk semua ID yang dipilih
            $updated = Penggajian::whereIn('id_gaji', $idGaji)
                ->where('status', 'belum lunas')
                ->update([
                    'status' => 'lunas',
                    'bukti_transfer' => $buktiPath
                ]);

            return [
                'success' => true,
                'updated' => $updated,
                'bukti_path' => $buktiPath
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Hapus data penggajian beserta bukti transfer
     */
    public function deletePenggajian($id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            // Hapus bukti transfer jika ada
            if ($penggajian->bukti_transfer && Storage::disk('public')->exists($penggajian->bukti_transfer)) {
                Storage::disk('public')->delete($penggajian->bukti_transfer);
            }

            $penggajian->delete();

            return ['success' => true];
        } catch (\Exception $e) {
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

        if ($tanggalDari) {
            $query->whereDate('tanggal_pesanan', '>=', $tanggalDari);
        }

        if ($tanggalSampai) {
            $query->whereDate('tanggal_pesanan', '<=', $tanggalSampai);
        }

        if ($idBarber) {
            $query->where('id_barber', $idBarber);
        }

        $data = $query->get();

        return [
            'total_pesanan' => $data->count(),
            'total_bayar' => $data->sum('total_bayar'),
            'total_potongan' => $data->sum('potongan'),
            'total_gaji' => $data->sum('total_gaji'),
            'lunas' => $data->where('status', 'lunas')->count(),
            'belum_lunas' => $data->where('status', 'belum lunas')->count(),
            'detail' => $data
        ];
    }

    /**
     * Get statistik per barber
     */
    public function getStatistikPerBarber($bulan = null, $tahun = null)
    {
        $query = Penggajian::selectRaw('
                id_barber,
                nama_barber,
                COUNT(*) as total_pesanan,
                SUM(total_bayar) as total_bayar,
                SUM(potongan) as total_potongan,
                SUM(total_gaji) as total_gaji,
                SUM(CASE WHEN status = "lunas" THEN 1 ELSE 0 END) as lunas,
                SUM(CASE WHEN status = "belum lunas" THEN 1 ELSE 0 END) as belum_lunas
            ')
            ->groupBy('id_barber', 'nama_barber');

        if ($bulan) {
            $query->whereMonth('tanggal_pesanan', $bulan);
        }

        if ($tahun) {
            $query->whereYear('tanggal_pesanan', $tahun);
        }

        return $query->get();
    }
}
