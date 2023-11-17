<?php

namespace App\Http\Controllers\Api\Diffusions;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiffusionResource;
use App\Models\Diffusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailController extends Controller
{
    public function __invoke(Request $request, Diffusion $diffusion)
    {
        if ($diffusion->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Not found'
            ], 404);
        }

        return new DiffusionResource($diffusion);
    }
}
