<?php

namespace App\Http\Resources;

use App\Models\Diffusion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class DiffusionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Diffusion $diffusion */
        $diffusion = $this;

        $result = [
            'id' => $this->id,
            'style' => $this->style,
            'status' => $this->status,
            'input' => [
                'prompt' => Arr::get($diffusion->input, 'prompt'),
                'negative_prompt' => Arr::get($diffusion->input, 'negative_prompt'),
                'seed' => Arr::get($diffusion->input, 'seed'),
            ],
            'created_at' => $diffusion->created_at,
            'updated_at' => $diffusion->updated_at,
        ];

        if ($diffusion->attachment) {
            $result = array_merge($result, [
                'attachment' => Storage::url($diffusion->attachment),
                'attachment_width' => $diffusion->attachment_width,
                'attachment_height' => $diffusion->attachment_height,
                'attachment_file_size' => $diffusion->attachment_file_size,
            ]);
        }

        if ($diffusion->thumbnail) {
            $result = array_merge($result, [
                'thumbnail' => Storage::url($diffusion->thumbnail),
                'thumbnail_width' => $diffusion->thumbnail_width,
                'thumbnail_height' => $diffusion->thumbnail_height,
                'thumbnail_file_size' => $diffusion->thumbnail_file_size,
            ]);
        }

        return $result;
    }
}
