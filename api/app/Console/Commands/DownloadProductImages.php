<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadProductImages extends Command
{
    protected $signature = 'products:download-images
                            {--source= : Path to products.json (default: base_path(scraper/products.json))}
                            {--force : Re-download even if already present}';

    protected $description = 'Download product images from sota.store and save to local storage';

    public function handle(): int
    {
        $source = $this->option('source') ?: base_path('scraper/products.json');

        if (! file_exists($source)) {
            $this->error("File not found: {$source}");
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($source), true);
        $force = (bool) $this->option('force');

        $total = array_sum(array_map(fn ($c) => count($c['products']), $data));
        $this->info("Processing {$total} products...");

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $bar->start();

        $downloaded = 0;
        $skipped = 0;
        $filtered = 0;
        $errors = 0;

        foreach ($data as $cat) {
            foreach ($cat['products'] as $pData) {
                $slug = preg_replace('/\.html$/', '', $pData['slug'] ?? '');
                $imageUrls = $pData['images'] ?? [];

                $bar->setMessage($slug);
                $bar->advance();

                if (empty($imageUrls) || empty($slug)) {
                    $skipped++;
                    continue;
                }

                $product = Product::where('slug', $slug)->with('variants')->first();
                if (! $product) {
                    $skipped++;
                    continue;
                }

                $variant = $product->variants->first();
                if (! $variant) {
                    $skipped++;
                    continue;
                }

                $dims = $variant->dimensions ?? [];
                $existingImages = $dims['images'] ?? [];

                // Skip if already has local images and not forcing
                if (! $force && ! empty($existingImages)) {
                    $skipped++;
                    continue;
                }

                $localImages = [];
                $isPrimary = true;

                foreach ($imageUrls as $url) {
                    if ($this->isPaymentImage($url)) {
                        $filtered++;
                        continue;
                    }
                    $localPath = $this->downloadImage($url, $slug);
                    if ($localPath) {
                        $localImages[] = [
                            'url' => Storage::disk('public')->url($localPath),
                            'isPrimary' => $isPrimary,
                        ];
                        $isPrimary = false;
                        $downloaded++;
                    } else {
                        $errors++;
                    }
                }

                if (! empty($localImages)) {
                    $dims['images'] = $localImages;
                    $variant->dimensions = $dims;
                    $variant->save();
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Downloaded: {$downloaded} | Filtered: {$filtered} | Skipped: {$skipped} | Errors: {$errors}");

        return self::SUCCESS;
    }

    private const SKIP_PATTERNS = ['bank', 'privat', 'mono', 'oplata', 'payment', 'visa', 'mastercard', 'sota_icon', 'logo'];

    private function isPaymentImage(string $url): bool
    {
        $lower = strtolower($url);
        foreach (self::SKIP_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'webp';
            $basename = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
            $basename = preg_replace('/[^a-zA-Z0-9._-]/', '-', $basename);
            // Normalize to 600x600
            $basename = preg_replace('/-\d+x\d+$/', '', $basename);
            $filename = "{$basename}.{$ext}";
            $storagePath = "products/{$slug}/{$filename}";

            if (Storage::disk('public')->exists($storagePath)) {
                return $storagePath;
            }

            // Try 600x600, fallback to 300x300
            $urls = [
                preg_replace('/-\d+x\d+\.(webp|jpg|jpeg|png)$/', '-600x600.$1', $url),
                preg_replace('/-\d+x\d+\.(webp|jpg|jpeg|png)$/', '-300x300.$1', $url),
                $url,
            ];

            foreach (array_unique($urls) as $tryUrl) {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent'  => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                        'Referer'     => 'https://sota.store/',
                        'Accept'      => 'image/webp,image/*,*/*',
                    ])
                    ->get($tryUrl);

                if (! $response->successful()) {
                    continue;
                }

                $contentType = $response->header('Content-Type') ?? '';
                if (! str_starts_with($contentType, 'image/')) {
                    continue;
                }

                Storage::disk('public')->put($storagePath, $response->body());
                return $storagePath;
            }

            return null;

        } catch (\Throwable $e) {
            $this->warn(" Error {$slug}: {$e->getMessage()}");
            return null;
        }
    }
}
