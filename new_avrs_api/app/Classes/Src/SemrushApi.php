<?php
namespace App\Classes\Src;

use \Exception;

class SemrushApi {

	const API_BASE_URL = 'https://api.semrush.com/';

	private $apiKey;

	private function __construct() {}

	/**
	 * @param string $apiKey SEMrush API key
	 * @return static
	 * @throws Exception
	 */
	public static function Factory($apiKey)
	{
		if (empty($apiKey)) {
			throw new Exception('SEMrush API key is required');
		}
		$instance = new static;
		$instance->apiKey = $apiKey;
		return $instance;
	}

	/**
	 * Fetch organic keyword rankings for a domain.
	 *
	 * @param string $domain The domain to query (e.g. "example.com")
	 * @param string $database The SEMrush database/country code (default: "us")
	 * @param int $displayLimit Max number of results to return (default: 100)
	 * @return array Array of associative arrays with keyword data
	 * @throws Exception
	 */
	public function fetchKeywordRankings($domain, $database = 'us', $displayLimit = 100)
	{
		$params = [
			'type'            => 'domain_organic',
			'key'             => $this->apiKey,
			'display_limit'   => $displayLimit,
			'export_columns'  => 'Ph,Po,Pp,Pd,Nq,Cp,Ur,Tr,Tc,Co,Nr,Td',
			'domain'          => $domain,
			'database'        => $database,
		];

		$url = self::API_BASE_URL . '?' . http_build_query($params);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if ($result === false) {
			throw new Exception('cURL error: ' . $curlError);
		}

		if ($httpCode !== 200) {
			throw new Exception('SEMrush API returned HTTP ' . $httpCode . ': ' . trim($result));
		}

		// SEMrush returns semicolon-delimited text with a header row
		$lines = array_filter(explode("\n", trim($result)), function($line) {
			return $line !== '';
		});

		if (count($lines) < 2) {
			return [];
		}

		$headers = str_getcsv(array_shift($lines), ';');
		$rows = [];
		foreach ($lines as $line) {
			$values = str_getcsv($line, ';');
			if (count($values) === count($headers)) {
				$rows[] = array_combine($headers, $values);
			}
		}

		return $rows;
	}

	/**
	 * Write keyword ranking data to a CSV file.
	 *
	 * @param array $rows Array of associative arrays (from fetchKeywordRankings)
	 * @param string $outputPath File path to write the CSV
	 * @throws Exception
	 */
	public static function writeCsv(array $rows, $outputPath)
	{
		if (empty($rows)) {
			throw new Exception('No data to write');
		}

		$fp = fopen($outputPath, 'w');
		if ($fp === false) {
			throw new Exception('Unable to open file for writing: ' . $outputPath);
		}

		// Write header row
		fputcsv($fp, array_keys($rows[0]));

		// Write data rows
		foreach ($rows as $row) {
			fputcsv($fp, $row);
		}

		fclose($fp);
	}
}
