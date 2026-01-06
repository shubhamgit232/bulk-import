<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Upload;
use App\Jobs\LinkProductImage;

class ChunkUploadController extends Controller
{
    /**
     * Receive a single chunk
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_id'   => 'required|string',   // SKU
            'chunk_index' => 'required|integer',
            'chunk'       => 'required|file',
        ]);

        $chunkDir = storage_path('app/chunks/' . $request->upload_id);

        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0777, true);
        }

        $chunkPath = $chunkDir . '/' . $request->chunk_index;

        // overwrite-safe (resume support)
        file_put_contents(
            $chunkPath,
            file_get_contents($request->file('chunk'))
        );

        return response()->json([
            'status' => 'chunk_received',
            'chunk'  => $request->chunk_index
        ]);
    }

    /**
     * Merge chunks + auto-link image to product (SKU based)
     */
    public function completeUpload(Request $request)
    {
        $request->validate([
            'upload_id'    => 'required|string',   // SKU
            'total_chunks' => 'required|integer|min:1',
        ]);

        $sku = $request->upload_id;

        $chunkDir  = storage_path('app/chunks/' . $sku);
        $finalPath = storage_path('app/uploads/' . $sku . '.jpg');

        // 🔹 Merge chunks
        $output = fopen($finalPath, 'wb');

        for ($i = 0; $i < $request->total_chunks; $i++) {
            $chunkPath = $chunkDir . '/' . $i;

            if (!file_exists($chunkPath)) {
                fclose($output);
                abort(400, "Missing chunk {$i} for SKU {$sku}");
            }

            fwrite($output, file_get_contents($chunkPath));
        }

        fclose($output);

        // 🔹 Save upload record (idempotent)
        Upload::updateOrCreate(
            ['original_name' => $sku],
            [
                'path'      => 'uploads/' . $sku . '.jpg',
                'completed' => true,
            ]
        );

        // 🔹 Cleanup chunks
        array_map('unlink', glob($chunkDir . '/*'));
        rmdir($chunkDir);

        // 🔥 AUTO-LINK IMAGE TO PRODUCT USING SKU
        $product = Product::where('sku', $sku)->first();

        if ($product) {
            // async, safe, idempotent
            LinkProductImage::dispatch($product->id, $sku);
        }

        return response()->json([
            'status' => 'upload_completed',
            'sku'    => $sku,
            'linked' => $product ? true : false
        ]);
    }
}
