<?php

namespace App\Http\Controllers\Api\Diffusions;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiffusionResource;
use App\Models\Diffusion;

class IndexController extends Controller
{
    public function __invoke()
    {
        $diffusions = Diffusion::where('status', Diffusion::STATUS_COMPLETED)
            ->orderByDesc('updated_at')
            ->take(25)
            ->get();

        return DiffusionResource::collection($diffusions);
    }
}
