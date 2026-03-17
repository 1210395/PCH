<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Designer;
use App\Models\ProductImage;
use App\Models\ProjectImage;
use App\Models\Service;

/**
 * One-off utility controller for migrating image file paths between storage layouts.
 *
 * Used after changing the storage directory structure to update paths stored in
 * the database (designer avatars, product images, project images, service images)
 * so they point to the new locations. Protected by auth:designer + admin middleware.
 */
class ImageMigrationController extends Controller
{
    /**
     * Show migration dashboard
     */
    public function index()
    {
        // Count images that need migration
        $stats = [
            'profiles_to_migrate' => $this->countProfilesToMigrate(),
            'products_to_migrate' => $this->countProductsToMigrate(),
            'projects_to_migrate' => $this->countProjectsToMigrate(),
            'services_to_migrate' => $this->countServicesToMigrate(),
        ];

        $stats['total'] = array_sum($stats);

        return view('admin.image-migration', compact('stats'));
    }

    /**
     * Run the migration
     */
    public function migrate(Request $request)
    {
        // Simple password check
        $password = $request->input('password');

        if ($password !== 'TechnoPark2025Migration!') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 403);
        }

        $dryRun = $request->input('dry_run', false);

        $results = [
            'profiles' => $this->migrateProfileImages($dryRun),
            'products' => $this->migrateProductImages($dryRun),
            'projects' => $this->migrateProjectImages($dryRun),
            'services' => $this->migrateServiceImages($dryRun),
        ];

        $totalRenamed = array_sum(array_column($results, 'renamed'));

        return response()->json([
            'success' => true,
            'dry_run' => $dryRun,
            'total_renamed' => $totalRenamed,
            'results' => $results
        ]);
    }

    /**
     * Count profiles that need migration
     */
    private function countProfilesToMigrate()
    {
        return Designer::whereNotNull('avatar')
            ->where('avatar', '!=', '')
            ->where('avatar', 'NOT REGEXP', '^profiles/profile_[0-9]+\\.')
            ->count();
    }

    /**
     * Count products that need migration
     */
    private function countProductsToMigrate()
    {
        return ProductImage::where('image_path', 'NOT REGEXP', '^products/product_[0-9]+_[0-9]+\\.')
            ->count();
    }

    /**
     * Count projects that need migration
     */
    private function countProjectsToMigrate()
    {
        return ProjectImage::where('image_path', 'NOT REGEXP', '^projects/project_[0-9]+_[0-9]+\\.')
            ->count();
    }

    /**
     * Count services that need migration
     */
    private function countServicesToMigrate()
    {
        return Service::whereNotNull('image')
            ->where('image', '!=', '')
            ->where('image', 'NOT REGEXP', '^services/service_[0-9]+\\.')
            ->count();
    }

    /**
     * Migrate profile images
     */
    private function migrateProfileImages($dryRun)
    {
        $renamed = 0;
        $errors = [];

        try {
            $designers = Designer::whereNotNull('avatar')
                ->where('avatar', '!=', '')
                ->get();

            foreach ($designers as $designer) {
                $oldPath = $designer->avatar;

                // Skip if already using structured naming
                if (preg_match('/^profiles\/profile_\d+\.\w+$/', $oldPath)) {
                    continue;
                }

                if (!Storage::disk('public')->exists($oldPath)) {
                    $errors[] = "Profile image not found: {$oldPath} (Designer {$designer->id})";
                    continue;
                }

                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = "profile_{$designer->id}.{$extension}";
                $newPath = "profiles/{$newFilename}";

                if (!$dryRun) {
                    if ($this->renameImage($oldPath, $newPath)) {
                        $designer->update(['avatar' => $newPath]);
                        $renamed++;
                    } else {
                        $errors[] = "Failed to rename: {$oldPath}";
                    }
                } else {
                    $renamed++;
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Error: {$e->getMessage()}";
        }

        return ['renamed' => $renamed, 'errors' => $errors];
    }

    /**
     * Migrate product images
     */
    private function migrateProductImages($dryRun)
    {
        $renamed = 0;
        $errors = [];

        try {
            $productImages = ProductImage::orderBy('product_id')
                ->orderBy('display_order')
                ->get();

            $groupedImages = $productImages->groupBy('product_id');

            foreach ($groupedImages as $productId => $images) {
                $imageNumber = 1;

                foreach ($images as $productImage) {
                    $oldPath = $productImage->image_path;

                    if (preg_match('/^products\/product_\d+_\d+\.\w+$/', $oldPath)) {
                        $imageNumber++;
                        continue;
                    }

                    if (!Storage::disk('public')->exists($oldPath)) {
                        $errors[] = "Product image not found: {$oldPath} (Product {$productId})";
                        $imageNumber++;
                        continue;
                    }

                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = "product_{$productId}_{$imageNumber}.{$extension}";
                    $newPath = "products/{$newFilename}";

                    if (!$dryRun) {
                        if ($this->renameImage($oldPath, $newPath)) {
                            $productImage->update(['image_path' => $newPath]);
                            $renamed++;
                        } else {
                            $errors[] = "Failed to rename: {$oldPath}";
                        }
                    } else {
                        $renamed++;
                    }

                    $imageNumber++;
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Error: {$e->getMessage()}";
        }

        return ['renamed' => $renamed, 'errors' => $errors];
    }

    /**
     * Migrate project images
     */
    private function migrateProjectImages($dryRun)
    {
        $renamed = 0;
        $errors = [];

        try {
            $projectImages = ProjectImage::orderBy('project_id')
                ->orderBy('display_order')
                ->get();

            $groupedImages = $projectImages->groupBy('project_id');

            foreach ($groupedImages as $projectId => $images) {
                $imageNumber = 1;

                foreach ($images as $projectImage) {
                    $oldPath = $projectImage->image_path;

                    if (preg_match('/^projects\/project_\d+_\d+\.\w+$/', $oldPath)) {
                        $imageNumber++;
                        continue;
                    }

                    if (!Storage::disk('public')->exists($oldPath)) {
                        $errors[] = "Project image not found: {$oldPath} (Project {$projectId})";
                        $imageNumber++;
                        continue;
                    }

                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = "project_{$projectId}_{$imageNumber}.{$extension}";
                    $newPath = "projects/{$newFilename}";

                    if (!$dryRun) {
                        if ($this->renameImage($oldPath, $newPath)) {
                            $projectImage->update(['image_path' => $newPath]);
                            $renamed++;
                        } else {
                            $errors[] = "Failed to rename: {$oldPath}";
                        }
                    } else {
                        $renamed++;
                    }

                    $imageNumber++;
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Error: {$e->getMessage()}";
        }

        return ['renamed' => $renamed, 'errors' => $errors];
    }

    /**
     * Migrate service images
     */
    private function migrateServiceImages($dryRun)
    {
        $renamed = 0;
        $errors = [];

        try {
            $services = Service::whereNotNull('image')
                ->where('image', '!=', '')
                ->get();

            foreach ($services as $service) {
                $oldPath = $service->image;

                if (preg_match('/^services\/service_\d+\.\w+$/', $oldPath)) {
                    continue;
                }

                if (!Storage::disk('public')->exists($oldPath)) {
                    $errors[] = "Service image not found: {$oldPath} (Service {$service->id})";
                    continue;
                }

                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFilename = "service_{$service->id}.{$extension}";
                $newPath = "services/{$newFilename}";

                if (!$dryRun) {
                    if ($this->renameImage($oldPath, $newPath)) {
                        $service->update(['image' => $newPath]);
                        $renamed++;
                    } else {
                        $errors[] = "Failed to rename: {$oldPath}";
                    }
                } else {
                    $renamed++;
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Error: {$e->getMessage()}";
        }

        return ['renamed' => $renamed, 'errors' => $errors];
    }

    /**
     * Rename an image file
     */
    private function renameImage($oldPath, $newPath)
    {
        try {
            if (Storage::disk('public')->exists($newPath)) {
                return false;
            }

            Storage::disk('public')->move($oldPath, $newPath);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to rename image', [
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
