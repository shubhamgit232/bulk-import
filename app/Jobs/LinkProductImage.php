<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Upload;
use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LinkProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;
    protected $sku;

    public function __construct($productId, $sku)
    {
        $this->productId = $productId;
        $this->sku       = $sku;
    }

    public function handle()
    {
        $product = Product::find($this->productId);
        if (!$product) return;

        // find uploaded image
        $upload = Upload::where('original_name', $this->sku)->first();
        if (!$upload) return;

        $sourcePath = storage_path('app/' . $upload->path);
        if (!file_exists($sourcePath)) return;

        // generate variants
        $imageIds = ImageService::generateVariants(
            $product->id,
            $sourcePath
        );

        // set primary image (idempotent)
        if (!$product->primary_image_id && count($imageIds)) {
            $product->update([
                'primary_image_id' => $imageIds[0]
            ]);
        }
    }
}
