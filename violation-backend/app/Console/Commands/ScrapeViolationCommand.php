<?php

namespace App\Console\Commands;

use App\Models\BisData;
use App\Models\Violation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeViolationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:violations {borough} {houseNumber} {streetName}';

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
        $borough = escapeshellarg($this->argument('borough'));
        $houseNumber = escapeshellarg($this->argument('houseNumber'));
        $streetName = escapeshellarg($this->argument('streetName'));

        $this->info("Scraping data for Borough: $borough, $houseNumber $streetName...");

        // Use absolute path to the scraper script
        $scriptPath = realpath(__DIR__ . '/../../../../scraper/scrape_bis.js');
        if (!$scriptPath) {
            Log::error("Scraper script not found at the expected path: " . __DIR__ . '/../../../../scraper/scrape_bis.js');
            $this->error("Scraper script not found. Check the path.");
            return;
        }

        $command = "node \"$scriptPath\" $borough $houseNumber $streetName";

        $this->info("Executing command: $command");

        // Log the command being executed
        Log::info("Executing scraper command: $command");

        // Execute the command and capture both output and errors
        $output = shell_exec($command . ' 2>&1');

        // Log the raw output from the scraper
        Log::info("Raw scraper output: $output");

        if ($output === null) {
            Log::error("Scraper did not produce any output. Check if Node.js is installed and the script path is correct.");
            $this->error("Scraper did not produce any output. Check logs for details.");
            return;
        }

        $isLegalAdultUse = trim($output) === "true";

        Log::info("Parsed scraper output (isLegalAdultUse): " . ($isLegalAdultUse ? 'true' : 'false'));

        BisData::updateOrCreate(
            [
                'house_number' => $this->argument('houseNumber'),
                'street_name' => $this->argument('streetName'),
            ],
            [
                'legal_adult_use' => $isLegalAdultUse,
            ]
        );

        // Map borough ID to its corresponding value
        $boroughMapping = [
            '1' => 'Manhattan',
            '2' => 'Bronx',
            '3' => 'Brooklyn',
            '4' => 'Queens',
            '5' => 'Staten Island'
        ];

        $boroughValue = $boroughMapping[$this->argument('borough')] ?? $this->argument('borough');

        // Also update or create the record in the Violation table
        Violation::updateOrCreate(
            [
                'house_number' => $this->argument('houseNumber'),
                'street_name' => $this->argument('streetName'),
            ],
            [
                'violation_type' => 'Legal Adult Use', // Example field, adjust as needed
                'borough' => $boroughValue, // Store the borough value instead of ID
            ]
        );

        $this->info("$houseNumber $streetName → Legal Adult Use: " . ($isLegalAdultUse ? 'Yes' : 'No'));
    }
}
