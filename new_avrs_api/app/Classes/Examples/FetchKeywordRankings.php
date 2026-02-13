<?php
namespace App\Classes\Examples;

use App\Classes\Src\SemrushApi;

/**
 * Fetches organic keyword ranking data from SEMrush for a given domain
 * and saves the results to a CSV file.
 *
 * Usage:
 *   Set the SEMRUSH_API_KEY environment variable, then run:
 *   php FetchKeywordRankings.php [domain] [database] [limit]
 *
 *   Defaults: domain=quickautotags.com, database=us, limit=100
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$domain   = isset($argv[1]) ? $argv[1] : 'quickautotags.com';
$database = isset($argv[2]) ? $argv[2] : 'us';
$limit    = isset($argv[3]) ? (int)$argv[3] : 100;

$apiKey = getenv('SEMRUSH_API_KEY');
if (empty($apiKey)) {
    fwrite(STDERR, "Error: SEMRUSH_API_KEY environment variable is not set.\n");
    fwrite(STDERR, "Usage: SEMRUSH_API_KEY=your_key php FetchKeywordRankings.php [domain] [database] [limit]\n");
    exit(1);
}

$outputFile = __DIR__ . '/' . str_replace('.', '_', $domain) . '_keyword_rankings.csv';

try {
    $semrush = SemrushApi::Factory($apiKey);

    echo "Fetching keyword rankings for {$domain} (database: {$database}, limit: {$limit})...\n";

    $rows = $semrush->fetchKeywordRankings($domain, $database, $limit);

    if (empty($rows)) {
        echo "No keyword ranking data found for {$domain}.\n";
        exit(0);
    }

    SemrushApi::writeCsv($rows, $outputFile);

    echo "Success: " . count($rows) . " keywords saved to {$outputFile}\n";
} catch (\Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
