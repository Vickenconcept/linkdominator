<?php
/**
 * Test Script: PhantomBuster Profile Scraper - Multiple URLs Test
 * 
 * This script tests if PhantomBuster API accepts multiple profile URLs directly
 * without requiring a Google Sheets spreadsheet.
 * 
 * Usage: php test_phantombuster_batch.php
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['PHANTOMBUSTER_API_KEY'] ?? getenv('PHANTOMBUSTER_API_KEY');
$phantomId = $_ENV['PHANTOMBUSTER_LINKEDIN_PROFILE_SCRAPER_PHANTOM_ID'] ?? getenv('PHANTOMBUSTER_LINKEDIN_PROFILE_SCRAPER_PHANTOM_ID');

if (!$apiKey) {
    die("❌ ERROR: PHANTOMBUSTER_API_KEY not found in environment variables\n");
}

if (!$phantomId) {
    die("❌ ERROR: PHANTOMBUSTER_LINKEDIN_PROFILE_SCRAPER_PHANTOM_ID not found in environment variables\n");
}

echo "🧪 Testing PhantomBuster Profile Scraper API\n";
echo "==========================================\n";
echo "API Key: " . substr($apiKey, 0, 10) . "...\n";
echo "Phantom ID: {$phantomId}\n\n";

// Test URLs (use real LinkedIn profile URLs for testing)
$testUrls = [
    'https://www.linkedin.com/in/vicken-concept/',
    'https://www.linkedin.com/in/jatinder-kaur-24151622b/',
    'https://www.linkedin.com/in/abhijeetkharat18/',
];

echo "📋 Test URLs:\n";
foreach ($testUrls as $index => $url) {
    echo "  " . ($index + 1) . ". {$url}\n";
}
echo "\n";

// Test 1: Multiple URLs in 'urls' array parameter
echo "🔬 TEST 1: Passing multiple URLs in 'urls' array parameter\n";
echo "-----------------------------------------------------------\n";

$test1Payload = [
    'id' => $phantomId,
    'argument' => [
        'urls' => $testUrls,
        'emailChooser' => 'phantombuster',
        'enrichWithCompanyData' => false,
        'updateMonitoringMetadata' => false,
        'pushResultToCRM' => true,
        'numberOfAddsPerLaunch' => count($testUrls),
    ]
];

$result1 = makeApiCall($apiKey, $phantomId, $test1Payload);
echo "Result: " . ($result1['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
if (!$result1['success']) {
    echo "Error: " . $result1['error'] . "\n";
    echo "Response: " . json_encode($result1['response'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Container ID: " . ($result1['container_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 2: Multiple URLs in 'spreadsheetUrl' as comma-separated
echo "🔬 TEST 2: Passing multiple URLs as comma-separated in 'spreadsheetUrl'\n";
echo "------------------------------------------------------------------------\n";

$test2Payload = [
    'id' => $phantomId,
    'argument' => [
        'spreadsheetUrl' => implode(',', $testUrls),
        'emailChooser' => 'phantombuster',
        'enrichWithCompanyData' => false,
        'updateMonitoringMetadata' => false,
        'pushResultToCRM' => true,
        'numberOfAddsPerLaunch' => count($testUrls),
    ]
];

$result2 = makeApiCall($apiKey, $phantomId, $test2Payload);
echo "Result: " . ($result2['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
if (!$result2['success']) {
    echo "Error: " . $result2['error'] . "\n";
    echo "Response: " . json_encode($result2['response'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Container ID: " . ($result2['container_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 3: Multiple URLs in 'spreadsheetUrl' as newline-separated
echo "🔬 TEST 3: Passing multiple URLs as newline-separated in 'spreadsheetUrl'\n";
echo "-------------------------------------------------------------------------\n";

$test3Payload = [
    'id' => $phantomId,
    'argument' => [
        'spreadsheetUrl' => implode("\n", $testUrls),
        'emailChooser' => 'phantombuster',
        'enrichWithCompanyData' => false,
        'updateMonitoringMetadata' => false,
        'pushResultToCRM' => true,
        'numberOfAddsPerLaunch' => count($testUrls),
    ]
];

$result3 = makeApiCall($apiKey, $phantomId, $test3Payload);
echo "Result: " . ($result3['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
if (!$result3['success']) {
    echo "Error: " . $result3['error'] . "\n";
    echo "Response: " . json_encode($result3['response'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Container ID: " . ($result3['container_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Test 4: Check API documentation format (based on your example)
echo "🔬 TEST 4: Using 'spreadsheetUrl' with single URL (baseline - current working method)\n";
echo "------------------------------------------------------------------------------------\n";

$test4Payload = [
    'id' => $phantomId,
    'argument' => [
        'spreadsheetUrl' => $testUrls[0], // Single URL
        'emailChooser' => 'phantombuster',
        'enrichWithCompanyData' => false,
        'updateMonitoringMetadata' => false,
        'pushResultToCRM' => true,
        'numberOfAddsPerLaunch' => 1,
    ]
];

$result4 = makeApiCall($apiKey, $phantomId, $test4Payload);
echo "Result: " . ($result4['success'] ? "✅ SUCCESS" : "❌ FAILED") . "\n";
if (!$result4['success']) {
    echo "Error: " . $result4['error'] . "\n";
    echo "Response: " . json_encode($result4['response'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Container ID: " . ($result4['container_id'] ?? 'N/A') . "\n";
}
echo "\n";

// Summary
echo "📊 TEST SUMMARY\n";
echo "===============\n";
echo "Test 1 (urls array): " . ($result1['success'] ? "✅" : "❌") . "\n";
echo "Test 2 (comma-separated): " . ($result2['success'] ? "✅" : "❌") . "\n";
echo "Test 3 (newline-separated): " . ($result3['success'] ? "✅" : "❌") . "\n";
echo "Test 4 (single URL baseline): " . ($result4['success'] ? "✅" : "❌") . "\n";
echo "\n";

if ($result1['success']) {
    echo "✅ RECOMMENDATION: Use 'urls' array parameter for batch processing\n";
} elseif ($result2['success']) {
    echo "✅ RECOMMENDATION: Use comma-separated URLs in 'spreadsheetUrl'\n";
} elseif ($result3['success']) {
    echo "✅ RECOMMENDATION: Use newline-separated URLs in 'spreadsheetUrl'\n";
} else {
    echo "⚠️  RECOMMENDATION: Multiple URLs not supported directly. Use Google Sheets method.\n";
}

/**
 * Make API call to PhantomBuster
 */
function makeApiCall($apiKey, $phantomId, $payload) {
    $url = "https://api.phantombuster.com/api/v2/agents/launch";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Phantombuster-Key-1: ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'response' => null
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 || $httpCode === 201) {
        $containerId = $responseData['containerId'] 
            ?? $responseData['data']['containerId'] 
            ?? $responseData['data']['id'] 
            ?? null;
        
        return [
            'success' => true,
            'container_id' => $containerId,
            'response' => $responseData
        ];
    } else {
        return [
            'success' => false,
            'error' => "HTTP {$httpCode}: " . ($responseData['error'] ?? $response),
            'response' => $responseData
        ];
    }
}
