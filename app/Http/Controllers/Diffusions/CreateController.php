<?php

namespace App\Http\Controllers\Diffusions;

use App\Http\Controllers\Controller;
use App\Jobs\Diffusions\ProcessDiffusion;
use App\Models\Diffusion;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $count = Diffusion::whereNotIn('status', [Diffusion::STATUS_COMPLETED, Diffusion::STATUS_FAILED])
            ->where('created_at', '>', now()->subtract('minute', 5))
            ->count();
        if ($count) {
            return response()->json(
                ['message' => 'Please wait until the previous request is fulfilled.'],
                429
            );
        }

        $style = $request->string('style');

        switch ($style) {
            case 'sdxl':
                $validated = $request->validate([
                    'style' => ['required', 'string'],
                    'prompt' => ['required', 'string', 'max:500'],
                    'negative_prompt' => ['string', 'max:500'],
                    'seed' => ['integer', 'max:9007199254740991'],
                ]);

                $input = [
                    'prompt' => $validated['prompt'],
                    'negative_prompt' => Arr::get($validated, 'negative_prompt', ''),
                    'width' => 1024,
                    'height' => 1024,
                    'num_outputs' => 1,
                    'scheduler' => 'K_EULER',
                    'num_inference_steps' => 50,
                    'guidance_scale' => 7.5,
                    'seed' => Arr::get($validated, 'seed', 9007199254740991),
                    'refine' => 'no_refiner',
                    'high_noise_frac' => 0.8,
                    'disable_safety_checker' => true
                ];

                break;
            default:
                return response()->json([
                    'message' => 'The style field is invalid.',
                    'errors' => [
                        'style' => [
                            'The style field is invalid.'
                        ]
                    ],
                ], 422);
        }

        $diffusion = Diffusion::create([
            'user_id' => Auth::id(),
            'style' => $style,
            'input' => $input,
        ]);

        $diffusion = $diffusion->fresh();

        ProcessDiffusion::dispatch($diffusion);

        return response()->json($diffusion, 201);
    }
}
