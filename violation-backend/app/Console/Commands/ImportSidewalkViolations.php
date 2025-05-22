<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Violation;


class ImportSidewalkViolations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:violations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import sidewalk violations from NYC OpenData';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://data.cityofnewyork.us/resource/6kbp-uz6m.json?$limit=50';

        $this->info("Fetching violations from OpenData NYC...");
        $response = Http::withOptions(['verify' => false])->get($url);

        if (!$response->ok()) {
            $this->error("Request failed: " . $response->status());
            return;
        }

        $violations = $response->json();
        $this->info("Retrieved " . count($violations) . " records.");
        \Log::info("Retrieved " . count($violations) . " records.");
        \Log::info($violations);
        foreach ($violations as $data) {
            Violation::updateOrCreate(
                [
                    'house_number' => $data['house_num'] ?? null,
                    'street_name'  => $data['onstname'] ?? null,
                    'borough'      => $data['boro'] ?? null,
                ],
                [
                    'violation_type' => 'Sidewalk',
                    'description'    => $data['violation_description'] ?? null,
                ]
            );
        }


        $this->info("Import complete.");
    }
}
