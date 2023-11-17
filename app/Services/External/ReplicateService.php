<?php

namespace App\Services\External;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReplicateService
{
    /**
     * @throws \Exception
     */
    public function createPrediction(string $model, string $version, mixed $input): PredictionReply
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('services.replicate.token'),
            'Content-Type' => 'application/json'
        ])->post('https://api.replicate.com/v1/predictions', [
            'model' => $model,
            'version' => $version,
            'input' => $input,
        ]);

        if (!$response->created()) {
            Log::error(sprintf('POST /v1/predictions %s', $response->body()));

            throw new \Exception(sprintf('Unexpected status code (%d)', $response->status()));
        }

        return new PredictionReply($response->json());
    }

    /**
     * @throws \Exception
     */
    public function getPrediction(string $replicateId): PredictionReply
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('services.replicate.token'),
            'Content-Type' => 'application/json'
        ])->get('https://api.replicate.com/v1/predictions/' . $replicateId);

        if (!$response->ok()) {
            Log::error(sprintf('GET /v1/predictions/%s %s', $replicateId, $response->body()));

            throw new \Exception(sprintf('Unexpected status code (%d)', $response->status()));
        }

        return new PredictionReply($response->json());
    }
}

class PredictionReply
{
    public string $id;
    public string $status;
    public mixed $output;
    public mixed $error;
    public string $createdAt;
    public ?string $startedAt;
    public ?string $completedAt;

    public function __construct(array $payload)
    {
        $this->id = $payload['id'];
        $this->status = $payload['status'];
        $this->output = $payload['output'] ?? null;
        $this->error = $payload['error'] ?? null;
        $this->createdAt = $payload['created_at'];
        $this->startedAt = $payload['started_at'] ?? null;
        $this->completedAt = $payload['completed_at'] ?? null;
    }
}
