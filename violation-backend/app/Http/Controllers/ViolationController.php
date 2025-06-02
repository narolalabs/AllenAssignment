<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Violation;

class ViolationController extends Controller
{
    public function getViolations(Request $request)
    {
        $query = Violation::with('bisData'); // <- eager load BIS data

        // if ($request->borough) {
        //     $query->where('borough', $request->borough);
        // }

        if ($request->houseNumber) {
            $query->where('house_number', 'LIKE', '%' . $request->houseNumber . '%');
        }

        if ($request->street) {
            $query->where('street_name', 'LIKE', '%' . $request->street . '%');
        }

        if ($request->violation_type) {
            $query->where('violation_type', $request->violation_type);
        }

        $results = $query->paginate(10);
        $querySql = $query->toSql();
        Log::info('Query executed', $results->toArray());

        if ($results->isEmpty() || $results->first()->bis_data === null) {
            $borough = $request->borough;
            $houseNumber = $request->houseNumber;
            $streetName = $request->street;

            Log::info('No data found or BIS data is null. Triggering scraper.', [
                'borough' => $borough,
                'houseNumber' => $houseNumber,
                'streetName' => $streetName
            ]);

            Artisan::call('scrape:violations', [
                'borough' => $borough,
                'houseNumber' => $houseNumber,
                'streetName' => $streetName
            ]);

            // Re-run the query to fetch the newly scraped data
            $results = $query->paginate(10);

            if ($results->isEmpty()) {
                return response()->json(['message' => 'No data found'], 404);
            }
        }

        return $results;
    }
}
