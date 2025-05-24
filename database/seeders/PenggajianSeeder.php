<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Penggajian;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Models\Pelanggan;
use Carbon\Carbon;

class PenggajianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada data pesanan, barber, dan pelanggan terlebih dahulu
        $pesanan = Pesanan::with(['barber', 'pelanggan'])->limit(10)->get();

        if ($pesanan->isEmpty()) {
            $this->command->info('Tidak ada data pesanan. Silakan seed data pesanan terlebih dahulu.');
            return;
        }

        foreach ($pesanan as $p) {
            // Skip jika sudah ada data penggajian untuk pesanan ini
            if (Penggajian::where('id_pesanan', $p->id)->exists()) {
                continue;
            }

            $totalBayar = $p->nominal + ($p->ongkos_kirim ?? 0);
            $potongan = $totalBayar * 0.05; // 5%
            $totalGaji = $totalBayar - $potongan;

            Penggajian::create([
                'id_pesanan' => $p->id,
                'id_barber' => $p->id_barber ?? $p->barber->id,
                'nama_barber' => $p->barber->nama,
                'rekening_barber' => $p->barber->rekening_barber ?? '1234567890',
                'id_pelanggan' => $p->id_pelanggan ?? $p->pelanggan->id,
                'nama_pelanggan' => $p->pelanggan->nama,
                'tanggal_pesanan' => $p->created_at,
                'total_bayar' => $totalBayar,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'status' => rand(0, 1) ? 'lunas' : 'belum lunas',
                'bukti_transfer' => rand(0, 1) ? 'bukti_transfer/sample.jpg' : null,
            ]);
        }

        $this->command->info('Data penggajian berhasil di-seed.');
    }
}
