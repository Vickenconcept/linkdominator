<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestPhantomBusterBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:phantombuster-batch 
                            {--urls= : Comma-separated LinkedIn profile URLs to test}
                            {--phantom-id= : Override phantom ID from config}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if PhantomBuster Profile Scraper accepts multiple profile URLs directly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config('services.phantombuster.api_key');
        $phantomId = $this->option('phantom-id') ?? config('services.phantombuster.linkedin_profile_scraper_phantom_id');

        if (!$apiKey) {
            $this->error('❌ PHANTOMBUSTER_API_KEY not configured');
            return 1;
        }

        if (!$phantomId) {
            $this->error('❌ PHANTOMBUSTER_LINKEDIN_PROFILE_SCRAPER_PHANTOM_ID not configured');
            return 1;
        }

        // Get test URLs
        $urlsOption = $this->option('urls');
        if ($urlsOption) {
            $testUrls = array_map('trim', explode(',', $urlsOption));
        } else {
            // Default test URLs (use real ones for actual testing)
            $testUrls = [
                'https://www.linkedin.com/in/vicken-concept/',
                'https://www.linkedin.com/in/test-profile-1/',
                'https://www.linkedin.com/in/test-profile-2/',
            ];
        }

        $this->info('🧪 Testing PhantomBuster Profile Scraper API');
        $this->line('==========================================');
        $this->line('API Key: ' . substr($apiKey, 0, 10) . '...');
        $this->line('Phantom ID: ' . $phantomId);
        $this->newLine();

        $this->info('📋 Test URLs:');
        foreach ($testUrls as $index => $url) {
            $this->line('  ' . ($index + 1) . '. ' . $url);
        }
        $this->newLine();

        $results = [];

        // Test 1: Multiple URLs in 'urls' array parameter
        $this->info('🔬 TEST 1: Passing multiple URLs in \'urls\' array parameter');
        $this->line('-----------------------------------------------------------');
        $result1 = $this->testUrlsArray($apiKey, $phantomId, $testUrls);
        $results['urls_array'] = $result1;
        $this->displayResult($result1);
        $this->newLine();

        // Test 2: Multiple URLs in 'spreadsheetUrl' as comma-separated
        $this->info('🔬 TEST 2: Passing multiple URLs as comma-separated in \'spreadsheetUrl\'');
        $this->line('------------------------------------------------------------------------');
        $result2 = $this->testCommaSeparated($apiKey, $phantomId, $testUrls);
        $results['comma_separated'] = $result2;
        $this->displayResult($result2);
        $this->newLine();

        // Test 3: Multiple URLs in 'spreadsheetUrl' as newline-separated
        $this->info('🔬 TEST 3: Passing multiple URLs as newline-separated in \'spreadsheetUrl\'');
        $this->line('-------------------------------------------------------------------------');
        $result3 = $this->testNewlineSeparated($apiKey, $phantomId, $testUrls);
        $results['newline_separated'] = $result3;
        $this->displayResult($result3);
        $this->newLine();

        // Test 4: Baseline - single URL (current working method)
        $this->info('🔬 TEST 4: Using \'spreadsheetUrl\' with single URL (baseline - current working method)');
        $this->line('------------------------------------------------------------------------------------');
        $result4 = $this->testSingleUrl($apiKey, $phantomId, $testUrls[0]);
        $results['single_url'] = $result4;
        $this->displayResult($result4);
        $this->newLine();

        // Summary
        $this->info('📊 TEST SUMMARY');
        $this->line('===============');
        $this->line('Test 1 (urls array): ' . ($results['urls_array']['success'] ? '✅' : '❌'));
        $this->line('Test 2 (comma-separated): ' . ($results['comma_separated']['success'] ? '✅' : '❌'));
        $this->line('Test 3 (newline-separated): ' . ($results['newline_separated']['success'] ? '✅' : '❌'));
        $this->line('Test 4 (single URL baseline): ' . ($results['single_url']['success'] ? '✅' : '❌'));
        $this->newLine();

        // Recommendation
        if ($results['urls_array']['success']) {
            $this->info('✅ RECOMMENDATION: Use \'urls\' array parameter for batch processing');
        } elseif ($results['comma_separated']['success']) {
            $this->info('✅ RECOMMENDATION: Use comma-separated URLs in \'spreadsheetUrl\'');
        } elseif ($results['newline_separated']['success']) {
            $this->info('✅ RECOMMENDATION: Use newline-separated URLs in \'spreadsheetUrl\'');
        } else {
            $this->warn('⚠️  RECOMMENDATION: Multiple URLs not supported directly. Use Google Sheets method.');
        }

        return 0;
    }

    /**
     * Test with 'urls' array parameter
     */
    private function testUrlsArray($apiKey, $phantomId, $urls)
    {
        $payload = [
            'id' => $phantomId,
            'argument' => [
                'urls' => $urls,
                'emailChooser' => 'phantombuster',
                'enrichWithCompanyData' => false,
                'updateMonitoringMetadata' => false,
                'pushResultToCRM' => true,
                'numberOfAddsPerLaunch' => count($urls),
            ]
        ];

        return $this->makeApiCall($apiKey, $payload);
    }

    /**
     * Test with comma-separated URLs in spreadsheetUrl
     */
    private function testCommaSeparated($apiKey, $phantomId, $urls)
    {
        $payload = [
            'id' => $phantomId,
            'argument' => [
                'spreadsheetUrl' => implode(',', $urls),
                'emailChooser' => 'phantombuster',
                'enrichWithCompanyData' => false,
                'updateMonitoringMetadata' => false,
                'pushResultToCRM' => true,
                'numberOfAddsPerLaunch' => count($urls),
            ]
        ];

        return $this->makeApiCall($apiKey, $payload);
    }

    /**
     * Test with newline-separated URLs in spreadsheetUrl
     */
    private function testNewlineSeparated($apiKey, $phantomId, $urls)
    {
        $payload = [
            'id' => $phantomId,
            'argument' => [
                'spreadsheetUrl' => implode("\n", $urls),
                'emailChooser' => 'phantombuster',
                'enrichWithCompanyData' => false,
                'updateMonitoringMetadata' => false,
                'pushResultToCRM' => true,
                'numberOfAddsPerLaunch' => count($urls),
            ]
        ];

        return $this->makeApiCall($apiKey, $payload);
    }

    /**
     * Test with single URL (baseline)
     */
    private function testSingleUrl($apiKey, $phantomId, $url)
    {
        $payload = [
            'id' => $phantomId,
            'argument' => [
                'spreadsheetUrl' => $url,
                'emailChooser' => 'phantombuster',
                'enrichWithCompanyData' => false,
                'updateMonitoringMetadata' => false,
                'pushResultToCRM' => true,
                'numberOfAddsPerLaunch' => 1,
            ]
        ];

        return $this->makeApiCall($apiKey, $payload);
    }

    /**
     * Make API call to PhantomBuster
     */
    private function makeApiCall($apiKey, $payload)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Phantombuster-Key-1' => $apiKey,
                ])
                ->post('https://api.phantombuster.com/api/v2/agents/launch', $payload);

            $httpCode = $response->status();
            $responseData = $response->json();

            if ($httpCode === 200 || $httpCode === 201) {
                $containerId = $responseData['containerId'] 
                    ?? $responseData['data']['containerId'] 
                    ?? $responseData['data']['id'] 
                    ?? null;

                return [
                    'success' => true,
                    'container_id' => $containerId,
                    'response' => $responseData,
                    'http_code' => $httpCode
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP {$httpCode}: " . ($responseData['error'] ?? $response->body()),
                    'response' => $responseData,
                    'http_code' => $httpCode
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null,
                'http_code' => null
            ];
        }
    }

    /**
     * Display test result
     */
    private function displayResult($result)
    {
        if ($result['success']) {
            $this->info('Result: ✅ SUCCESS');
            $this->line('Container ID: ' . ($result['container_id'] ?? 'N/A'));
            if (isset($result['http_code'])) {
                $this->line('HTTP Code: ' . $result['http_code']);
            }
        } else {
            $this->error('Result: ❌ FAILED');
            $this->error('Error: ' . $result['error']);
            if (isset($result['http_code'])) {
                $this->line('HTTP Code: ' . $result['http_code']);
            }
            if ($result['response']) {
                $this->line('Response: ' . json_encode($result['response'], JSON_PRETTY_PRINT));
            }
        }
    }
}
