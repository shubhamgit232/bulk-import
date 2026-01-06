<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


class ProcessProductCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        $filePath = storage_path('app/private/' . $this->path);

        // 1️⃣ Check file exists
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found at {$filePath}");
        }

        $file = fopen($filePath, 'r');

        // 2️⃣ Read header safely
        $header = fgetcsv($file);

        if ($header === false) {
            fclose($file);
            throw new \Exception("CSV header missing or file is empty");
        }

        $summary = [
            'total'      => 0,
            'imported'   => 0,
            'updated'    => 0,
            'invalid'    => 0,
            'duplicates' => 0,
        ];

        $seenSkus = [];

        while (($row = fgetcsv($file)) !== false) {
            $summary['total']++;

            // 3️⃣ Column count mismatch protection
            if (count($row) !== count($header)) {
                $summary['invalid']++;
                continue;
            }

            $data = array_combine($header, $row);

            // 4️⃣ Required column validation
            if (
                empty($data['sku']) ||
                empty($data['name']) ||
                !isset($data['price'])
            ) {
                $summary['invalid']++;
                continue;
            }

            DB::transaction(function () use (&$summary, &$seenSkus, $data) {

                // 5️⃣ Duplicate SKU inside same CSV
                if (in_array($data['sku'], $seenSkus)) {
                    $summary['duplicates']++;
                    return;
                }

                $seenSkus[] = $data['sku'];

                $exists = Product::where('sku', $data['sku'])->exists();

                // 6️⃣ SKU-based UPSERT
                Product::updateOrInsert(
                    ['sku' => $data['sku']],
                    [
                        'name'  => $data['name'],
                        'price' => $data['price'],
                    ]
                );

                $product = Product::where('sku', $data['sku'])->first();

                // 7️⃣ Image linking (safe, async)
                // if (!empty($data['image_ref']) && $product) {
                //     \App\Jobs\LinkProductImage::dispatch(
                //         $product->id,
                //         $data['image_ref']
                //     );
                // }

                $exists ? $summary['updated']++ : $summary['imported']++;
            });
        }

        fclose($file);

        Import::create($summary);
    }


}
