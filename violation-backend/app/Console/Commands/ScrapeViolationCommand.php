<?php

namespace App\Console\Commands;

use App\Models\BisData;
use App\Models\Violation;
use Illuminate\Console\Command;

class ScrapeViolationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:violations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Legal Adult Use info and store it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting scraping process...");

        $violations = Violation::all();

        foreach ($violations as $violation) {
            $houseNumber = escapeshellarg($violation->house_number);
            $streetName = escapeshellarg($violation->street_name);

            $command = "node ../scraper/scrape_bis.js $houseNumber $streetName";
            $output = trim(shell_exec($command));
            $isLegalAdultUse = $output === "true";

            BisData::updateOrCreate(
                [
                    'house_number' => $violation->house_number,
                    'street_name' => $violation->street_name,
                ],
                [
                    'legal_adult_use' => $isLegalAdultUse,
                ]
            );

            $this->info("{$violation->house_number} {$violation->street_name} → Legal Adult Use: " . ($isLegalAdultUse ? 'Yes' : 'No'));
        }

        $this->info("Scraping completed.");
    }
}
