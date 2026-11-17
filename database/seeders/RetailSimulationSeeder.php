<?php

namespace Database\Seeders;

use App\Services\SimulationDataService;
use Illuminate\Database\Seeder;

class RetailSimulationSeeder extends Seeder
{
    public function run(SimulationDataService $simulation): void
    {
        $stats = $simulation->generate();

        $this->command?->info(sprintf(
            'LUMEN MART: %d produk, %d transaksi, %d baris detail, %d prediksi regresi, %d aturan Apriori.',
            $stats['products'],
            $stats['transactions'],
            $stats['details'],
            $stats['predictions'] ?? 0,
            $stats['apriori']['rules'] ?? 0,
        ));
    }
}
