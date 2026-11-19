<?php

namespace App\Http\Controllers;

use App\Models\CustomerListing;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class StorefrontProductImageController extends Controller
{
    public function show(Product $product)
    {
        $listing = CustomerListing::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->first();

        abort_unless($listing, 404);

        // Prefer the image uploaded in the Customer Catalog.
        // Fall back to the original vendor product image.
        $imagePath = $listing->image_path ?: $product->image_path;

        abort_unless($imagePath, 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($imagePath), 404);

        $path = $disk->path($imagePath);
        $mimeType = mime_content_type($path);

        abort_unless(
            in_array($mimeType, [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ], true),
            415
        );

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}