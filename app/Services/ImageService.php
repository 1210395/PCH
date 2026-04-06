<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Standard aspect ratios and their max dimensions.
     *
     * SQUARE  (1:1)  - avatars, logos, thumbnails
     * CARD    (4:3)  - product/project/marketplace cards, fablabs, trainings
     * BANNER  (16:9) - cover images, hero banners, tender carousels
     */
    public const SQUARE = 'square';   // 1:1
    public const CARD   = 'card';     // 4:3
    public const BANNER = 'banner';   // 16:9

    protected static array $dimensions = [
        self::SQUARE => ['width' => 800,  'height' => 800],
        self::CARD   => ['width' => 1200, 'height' => 900],
        self::BANNER => ['width' => 1600, 'height' => 900],
    ];

    protected static int $quality = 85;

    /**
     * Process an uploaded image file: center-crop to ratio, resize, convert to WebP.
     *
     * @param  UploadedFile  $file       The uploaded image file
     * @param  string        $ratio      One of SQUARE, CARD, BANNER
     * @param  string        $folder     Storage folder (e.g. 'products', 'profiles')
     * @param  string        $filename   Desired filename WITHOUT extension (e.g. 'product_123_1')
     * @param  string        $disk       Storage disk name
     * @return string|null   The stored path relative to disk root, or null on failure
     */
    public static function process(
        UploadedFile $file,
        string $ratio,
        string $folder,
        string $filename,
        string $disk = 'public'
    ): ?string {
        try {
            $dim = self::$dimensions[$ratio] ?? self::$dimensions[self::CARD];

            $image = ImageManager::gd()->read($file->getRealPath());

            // Center-crop to target aspect ratio, then resize down
            $image->cover($dim['width'], $dim['height']);

            // Encode as WebP
            $encoded = $image->toWebp(self::$quality);

            $path = rtrim($folder, '/') . '/' . $filename . '.webp';

            Storage::disk($disk)->put($path, (string) $encoded);

            return $path;
        } catch (\Throwable $e) {
            \Log::error('ImageService::process failed', [
                'error' => $e->getMessage(),
                'file'  => $file->getClientOriginalName(),
                'ratio' => $ratio,
            ]);
            return null;
        }
    }

    /**
     * Process an image that already exists on disk (for batch migration).
     *
     * @param  string  $existingPath  Path relative to disk root
     * @param  string  $ratio         One of SQUARE, CARD, BANNER
     * @param  string  $disk          Storage disk name
     * @return string|null  The new path (with .webp extension), or null on failure
     */
    public static function processExisting(
        string $existingPath,
        string $ratio,
        string $disk = 'public'
    ): ?string {
        try {
            $fullPath = Storage::disk($disk)->path($existingPath);

            if (!file_exists($fullPath)) {
                return null;
            }

            $dim = self::$dimensions[$ratio] ?? self::$dimensions[self::CARD];

            $image = ImageManager::gd()->read($fullPath);
            $image->cover($dim['width'], $dim['height']);
            $encoded = $image->toWebp(self::$quality);

            // Replace extension with .webp
            $newPath = preg_replace('/\.[^.]+$/', '.webp', $existingPath);

            Storage::disk($disk)->put($newPath, (string) $encoded);

            // Delete old file if different path
            if ($newPath !== $existingPath) {
                Storage::disk($disk)->delete($existingPath);
            }

            return $newPath;
        } catch (\Throwable $e) {
            \Log::error('ImageService::processExisting failed', [
                'error' => $e->getMessage(),
                'path'  => $existingPath,
                'ratio' => $ratio,
            ]);
            return null;
        }
    }

    /**
     * Process a temp file path (from ImageUploadController) and move to permanent storage.
     * Used when the file is already stored as a temp upload.
     *
     * @param  string  $tempPath   Temp path relative to disk (e.g. 'uploads/temp/products/abc/uuid.jpg')
     * @param  string  $ratio      One of SQUARE, CARD, BANNER
     * @param  string  $folder     Permanent storage folder
     * @param  string  $filename   Desired filename WITHOUT extension
     * @param  string  $disk       Storage disk
     * @return string|null  The permanent path, or null on failure
     */
    public static function processFromTemp(
        string $tempPath,
        string $ratio,
        string $folder,
        string $filename,
        string $disk = 'public'
    ): ?string {
        try {
            $fullPath = Storage::disk($disk)->path($tempPath);

            if (!file_exists($fullPath)) {
                return null;
            }

            $dim = self::$dimensions[$ratio] ?? self::$dimensions[self::CARD];

            $image = ImageManager::gd()->read($fullPath);
            $image->cover($dim['width'], $dim['height']);
            $encoded = $image->toWebp(self::$quality);

            $path = rtrim($folder, '/') . '/' . $filename . '.webp';

            Storage::disk($disk)->put($path, (string) $encoded);

            // Clean up temp file
            Storage::disk($disk)->delete($tempPath);

            return $path;
        } catch (\Throwable $e) {
            \Log::error('ImageService::processFromTemp failed', [
                'error' => $e->getMessage(),
                'temp'  => $tempPath,
                'ratio' => $ratio,
            ]);
            return null;
        }
    }

    /**
     * Get the ratio type for a given entity context.
     */
    public static function ratioFor(string $type): string
    {
        return match ($type) {
            'avatar', 'profile', 'logo', 'thumbnail', 'instructor' => self::SQUARE,
            'cover', 'banner', 'hero'                               => self::BANNER,
            default                                                  => self::CARD,
        };
    }
}
