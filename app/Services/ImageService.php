<?php

namespace App\Services;

use App\Models\Image;
use Intervention\Image\Facades\Image as Img;

class ImageService
{
    public static function generateVariants($productId, $sourcePath)
    {
        $sizes = [256, 512, 1024];
        $imageIds = [];

        foreach ($sizes as $size) {
            $img = Img::make($sourcePath)
                ->resize($size, null, function ($c) {
                    $c->aspectRatio();
                });

            $path = "products/{$productId}_{$size}.jpg";

            $img->save(storage_path("app/public/".$path));

            $image = Image::firstOrCreate(
                ['product_id' => $productId, 'size' => $size],
                ['path' => $path]
            );

            $imageIds[] = $image->id;
        }

        return $imageIds;
    }
}
