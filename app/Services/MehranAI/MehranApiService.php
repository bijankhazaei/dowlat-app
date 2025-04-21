<?php

namespace App\Services\MehranAI;

use App\Models\Prompt;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class MehranApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.mehran_ai.base_url');
    }

    /**
     * @throws Exception
     */
    public function getAllPrompts(): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/prompts/");

            if (!$response->successful()) {
                Log::error('Mehran AI API error: ' . $response->body(), [
                    'status' => $response->status(),
                    'endpoint' => "{$this->baseUrl}/prompts/"
                ]);

                throw new Exception('Failed to fetch data from Mehran AI API: ' . $response->status());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('External API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Unable to connect to external service: ' . $e->getMessage());
        }
    }

    /**
     * @param $item_id
     * @return array
     * @throws Exception
     */
    public function getPrompt($item_id): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/prompts/{$item_id}");
            if (!$response->successful()) {
                Log::error('Mehran AI API error: ' . $response->body(), [
                    'status' => $response->status(),
                    'endpoint' => "{$this->baseUrl}/prompts/{$item_id}"
                ]);

                throw new Exception('Failed to fetch data from Mehran AI API: ' . $response->status());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('External API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Unable to connect to external service: ' . $e->getMessage());
        }
    }

    /**
     * @param array $data
     * @return Prompt
     * @throws Exception
     */
    public function createPrompt(array $data): Prompt
    {
        try {
            $data['ver'] = 0;

            $response = Http::post("{$this->baseUrl}/prompts/", $data);

            if (!$response->successful()) {
                Log::error('Mehran AI API error: ' . $response->body(), [
                    'status' => $response->status(),
                    'endpoint' => "{$this->baseUrl}/prompts/"
                ]);

                throw new Exception('Failed to fetch data from Mehran AI API: '.$response->status() . $response->body());
            }

            return Prompt::query()->create($response->json());
        } catch (Exception $e) {
            Log::error('External API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Unable to connect to external service: ' . $e->getMessage());
        }
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getAgents(): mixed
    {
        try {
            $response = Http::get("{$this->baseUrl}/agents");
            if (!$response->successful()) {
                Log::error('Mehran AI API error: ' . $response->body(), [
                    'status' => $response->status(),
                    'endpoint' => "{$this->baseUrl}/agents"
                ]);

                throw new Exception('Failed to fetch data from Mehran AI API: ' . $response->status());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('External API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception('Unable to connect to external service: ' . $e->getMessage());
        }
    }
}
