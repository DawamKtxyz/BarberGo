<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TukangCukur;

class FixSertifikatPath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sertifikat-path';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix sertifikat path in database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing sertifikat paths...');

        $tukangCukurs = TukangCukur::whereNotNull('sertifikat')->get();

        foreach ($tukangCukurs as $tukangCukur) {
            $oldPath = $tukangCukur->sertifikat;

            // Jika path masih menggunakan format lama (storage/sertifikat/...)
            if (strpos($oldPath, 'storage/') === 0) {
                // Ubah ke format baru (sertifikat/...)
                $newPath = str_replace('storage/', '', $oldPath);

                $tukangCukur->update(['sertifikat' => $newPath]);

                $this->info("Updated: {$oldPath} -> {$newPath}");
            }
        }

        $this->info('Sertifikat paths fixed successfully!');

        return Command::SUCCESS;
    }
}
