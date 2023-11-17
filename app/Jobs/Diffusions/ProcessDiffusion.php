<?php

namespace App\Jobs\Diffusions;

use App\Models\Diffusion;
use App\Services\External\RemoteImageService;
use App\Services\External\ReplicateService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

define('PROCESS_DIFFUSION_TIMEOUT_SECONDS', 60);

class ProcessDiffusion implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Diffusion $diffusion)
    {
        //
    }

    public function handle(
        RemoteImageService $remoteImageService,
        ReplicateService   $replicateService,
    ): void
    {
        $timeout = PROCESS_DIFFUSION_TIMEOUT_SECONDS;

        if ($this->diffusion->status !== Diffusion::STATUS_PENDING) {
            Log::error(
                sprintf('Unexpected status. expected=%s; actual=%s', Diffusion::STATUS_PENDING, $this->diffusion->status),
                ['diffusion' => $this->diffusion]
            );

            return;
        }

        $this->diffusion->status = Diffusion::STATUS_STARTING;
        $this->diffusion->save();

        switch ($this->diffusion->style) {
            case 'sdxl':
                try {
                    $prediction = $replicateService->createPrediction(
                        'stability-ai/sdxl',
                        '39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b',
                        $this->diffusion->input,
                    );

                    while (true) {
                        $response = $replicateService->getPrediction($prediction->id);

                        if ($response->error) {
                            Log::error($response->error, ['diffusion' => $this->diffusion]);

                            $this->diffusion->status = Diffusion::STATUS_FAILED;
                            $this->diffusion->error = $response->error;
                            $this->diffusion->save();

                            break;
                        }

                        if ($response->output) {
                            $this->diffusion->status = Diffusion::STATUS_COMPLETED;
                            $this->diffusion->output = $response->output;
                            $this->diffusion->save();

                            $url = is_array($response->output)
                                ? $response->output[0]
                                : $response->output;

                            $attachment = $remoteImageService->storeRemoteImage($url);

                            $this->diffusion->attachment = $attachment->attachment->path;
                            $this->diffusion->attachment_width = $attachment->attachment->width;
                            $this->diffusion->attachment_height = $attachment->attachment->height;
                            $this->diffusion->attachment_file_size = $attachment->attachment->fileSize;

                            if ($attachment->thumbnail) {
                                $this->diffusion->thumbnail = $attachment->thumbnail->path;
                                $this->diffusion->thumbnail_width = $attachment->thumbnail->width;
                                $this->diffusion->thumbnail_height = $attachment->thumbnail->height;
                                $this->diffusion->thumbnail_file_size = $attachment->thumbnail->fileSize;
                            }

                            $this->diffusion->save();

                            break;
                        }

                        if ($timeout < 0) {
                            Log::error('Deadline exceeded.', ['diffusion' => $this->diffusion]);

                            $this->diffusion->status = Diffusion::STATUS_FAILED;
                            $this->diffusion->error = 'Deadline exceeded.';
                            $this->diffusion->save();

                            break;
                        }

                        $timeout -= 1;

                        sleep(1);
                    }
                } catch (Exception $exception) {
                    Log::error($exception->getMessage(), ['diffusion' => $this->diffusion]);

                    $this->diffusion->status = Diffusion::STATUS_FAILED;
                    $this->diffusion->output = $exception->getMessage();
                    $this->diffusion->save();
                }

                break;
            default:
                Log::error(
                    sprintf('The style value is not supported. style=%s', $this->diffusion->status),
                    ['diffusion' => $this->diffusion],
                );

                $this->diffusion->status = Diffusion::STATUS_FAILED;
                $this->diffusion->output = sprintf('The style value is not supported. style=%s', $this->diffusion->status);
                $this->diffusion->save();
        }
    }
}
