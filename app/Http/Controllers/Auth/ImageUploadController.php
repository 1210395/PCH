<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploadController extends Controller
{
    /**
     * Upload image during registration wizard
     * ULTIMATE SOLUTION with comprehensive error handling
     */
    public function uploadRegistrationImage(Request $request)
    {
        // Step 1: Log incoming request and PHP upload settings
        $phpUploadMaxSize = ini_get('upload_max_filesize');
        $phpPostMaxSize = ini_get('post_max_size');
        $phpMaxFileUploads = ini_get('max_file_uploads');

        // Get ALL request data for debugging
        $allInput = $request->all();
        $allFiles = $request->allFiles();

        Log::info('Image upload request received - FULL DEBUG', [
            'method' => $request->method(),
            'url' => $request->url(),
            'type' => $request->input('type'),
            'session_id' => $request->input('session_id'),
            'file_hash' => $request->input('file_hash'),
            'has_file_image' => $request->hasFile('image'),
            'all_input_keys' => array_keys($allInput),
            'all_files_keys' => array_keys($allFiles),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'file_size' => $request->hasFile('image') ? $request->file('image')->getSize() : 0,
            'file_mime' => $request->hasFile('image') ? $request->file('image')->getMimeType() : null,
            'file_client_name' => $request->hasFile('image') ? $request->file('image')->getClientOriginalName() : null,
            'file_error' => $request->hasFile('image') ? $request->file('image')->getError() : null,
            'file_error_message' => $request->hasFile('image') ? $this->getUploadErrorMessage($request->file('image')->getError()) : 'No file',
            'php_upload_max_filesize' => $phpUploadMaxSize,
            'php_post_max_size' => $phpPostMaxSize,
            'php_max_file_uploads' => $phpMaxFileUploads,
            'max_upload_bytes' => $this->parseSize($phpUploadMaxSize),
            'max_post_bytes' => $this->parseSize($phpPostMaxSize),
        ]);

        // Check if NO file was received at all
        if (!$request->hasFile('image')) {
            Log::error('NO FILE RECEIVED - This is the problem!', [
                'has_any_files' => count($allFiles) > 0,
                'all_files_keys' => array_keys($allFiles),
                'content_length' => $request->header('Content-Length'),
                'content_type' => $request->header('Content-Type'),
                'possible_causes' => [
                    '1. PHP upload limits too small',
                    '2. POST data exceeded post_max_size',
                    '3. Web server timeout',
                    '4. Nginx/Apache upload limits',
                    '5. CSRF token issue'
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No file received by server. Possible causes: File too large for PHP settings (upload_max_filesize=' . $phpUploadMaxSize . ', post_max_size=' . $phpPostMaxSize . '), or server upload limit exceeded.',
                'debug_info' => [
                    'php_upload_max_filesize' => $phpUploadMaxSize,
                    'php_post_max_size' => $phpPostMaxSize,
                    'content_length_header' => $request->header('Content-Length'),
                    'file_received' => false,
                ]
            ], 422);
        }

        // Check for PHP upload errors
        $file = $request->file('image');
        $uploadError = $file->getError();

        if ($uploadError !== UPLOAD_ERR_OK) {
            $errorMsg = $this->getUploadErrorMessage($uploadError);

            Log::error('PHP file upload error', [
                'error_code' => $uploadError,
                'error_message' => $errorMsg,
                'file_size' => $file->getSize(),
                'php_limits' => [
                    'upload_max_filesize' => $phpUploadMaxSize,
                    'post_max_size' => $phpPostMaxSize
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMsg . ' (upload_max_filesize=' . $phpUploadMaxSize . ', post_max_size=' . $phpPostMaxSize . ')',
                'debug_info' => [
                    'error_code' => $uploadError,
                    'php_upload_max_filesize' => $phpUploadMaxSize,
                    'php_post_max_size' => $phpPostMaxSize,
                    'file_size_bytes' => $file->getSize(),
                ]
            ], 422);
        }

        try {
            // Step 2: Validate storage directory exists and is writable
            $this->ensureStorageExists();

            // Step 3: Validate request
            try {
                $validated = $request->validate([
                    'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
                    'type' => 'required|in:avatar,profile,cover,product,project,service,marketplace',
                    'session_id' => 'required|string',
                    'file_hash' => 'nullable|string',
                ]);

                Log::info('Validation passed', ['validated_data' => array_keys($validated)]);
            } catch (ValidationException $e) {
                $fileInfo = [];
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $fileInfo = [
                        'client_name' => $file->getClientOriginalName(),
                        'size_bytes' => $file->getSize(),
                        'size_kb' => round($file->getSize() / 1024, 2),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'is_valid' => $file->isValid(),
                    ];
                }

                Log::error('Validation failed - DETAILED INFO', [
                    'errors' => $e->errors(),
                    'input_type' => $request->input('type'),
                    'input_session' => $request->input('session_id'),
                    'has_file' => $request->hasFile('image'),
                    'file_info' => $fileInfo,
                    'validation_rules' => [
                        'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120 (5MB)',
                        'max_size_bytes' => 5120 * 1024,
                        'allowed_mimes' => ['jpeg', 'jpg', 'png', 'gif']
                    ]
                ]);

                $errorMessages = [];
                foreach ($e->errors() as $field => $messages) {
                    $errorMessages[] = implode(' ', $messages);
                }

                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $errorMessages),
                    'errors' => $e->errors(),
                    'debug_info' => $fileInfo
                ], 422);
            }

            // Step 4: Check for duplicate upload using hash
            // Only reuse if same type (don't reuse profile images for products!)
            if ($request->has('file_hash') && !empty($request->file_hash)) {
                $existingPath = $this->findImageByHash($request->file_hash, $request->session_id, $request->type);
                if ($existingPath) {
                    Log::info('Duplicate image detected (same type)', [
                        'path' => $existingPath,
                        'type' => $request->type
                    ]);
                    return response()->json([
                        'success' => true,
                        'path' => $existingPath,
                        'message' => 'Image already uploaded',
                        'duplicate' => true
                    ]);
                }
            }

            // Step 5: Get file and validate it exists
            $file = $request->file('image');
            if (!$file || !$file->isValid()) {
                throw new \Exception('Uploaded file is not valid');
            }

            $type = $request->type;
            $sessionId = $request->session_id;

            Log::info('Processing file upload', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'type' => $type,
                'session_id' => $sessionId
            ]);

            // Step 6: Create folder structure
            // Map type to plural folder name
            $folderMap = [
                'avatar' => 'profiles',
                'profile' => 'profiles',
                'hero' => 'heroes',
                'cover' => 'covers',
                'product' => 'products',
                'project' => 'projects',
                'service' => 'services',
                'marketplace' => 'marketplace',
                'certification' => 'certifications'
            ];
            $folderName = $folderMap[$type] ?? $type . 's';
            $folder = "uploads/temp/{$folderName}/{$sessionId}";
            $fullPath = storage_path('app/public/' . $folder);

            if (!file_exists($fullPath)) {
                Log::info('Creating upload directory', ['path' => $fullPath]);

                if (!mkdir($fullPath, 0755, true)) {
                    $error = error_get_last();
                    Log::error('Failed to create directory', [
                        'path' => $fullPath,
                        'error' => $error
                    ]);
                    throw new \Exception('Failed to create upload directory: ' . ($error['message'] ?? 'Unknown error'));
                }

                // Verify directory was created
                if (!is_dir($fullPath)) {
                    throw new \Exception('Directory was not created successfully');
                }

                // Verify directory is writable
                if (!is_writable($fullPath)) {
                    throw new \Exception('Directory is not writable: ' . $fullPath);
                }

                Log::info('Directory created successfully', ['path' => $fullPath]);
            } else {
                Log::info('Directory already exists', ['path' => $fullPath]);
            }

            // Step 7: Generate unique filename
            $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = 'jpg';
            }
            $filename = Str::uuid() . '.' . strtolower($extension);

            Log::info('Generated filename', ['filename' => $filename]);

            // Step 8: Store file
            try {
                $path = $file->storeAs($folder, $filename, 'public');

                if (!$path) {
                    throw new \Exception('storeAs() returned false');
                }

                // Verify file was actually stored
                $storedFilePath = storage_path('app/public/' . $path);
                if (!file_exists($storedFilePath)) {
                    throw new \Exception('File was not stored at expected location: ' . $storedFilePath);
                }

                $fileSize = filesize($storedFilePath);
                Log::info('File stored successfully', [
                    'path' => $path,
                    'full_path' => $storedFilePath,
                    'size' => $fileSize
                ]);

            } catch (\Exception $e) {
                Log::error('File storage failed', [
                    'error' => $e->getMessage(),
                    'folder' => $folder,
                    'filename' => $filename
                ]);
                throw new \Exception('Failed to store file: ' . $e->getMessage());
            }

            // Step 9: Store metadata for duplicate detection
            // Include type in metadata to prevent cross-type reuse
            try {
                $this->storeUploadMetadata($path, $request->file_hash, $sessionId, $type);
                Log::info('Metadata stored successfully', ['type' => $type]);
            } catch (\Exception $e) {
                // Don't fail the upload if metadata storage fails
                Log::warning('Metadata storage failed', ['error' => $e->getMessage()]);
            }

            // Step 10: Return success response
            $response = [
                'success' => true,
                'path' => $path,
                'message' => 'Image uploaded successfully',
                'duplicate' => false,
                'debug_info' => [
                    'filename' => $filename,
                    'size' => $file->getSize(),
                    'type' => $type
                ]
            ];

            Log::info('Upload completed successfully', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            // Ultimate error logging
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'type' => $request->input('type'),
                    'session_id' => $request->input('session_id'),
                    'file_hash' => $request->input('file_hash'),
                    'has_file' => $request->hasFile('image')
                ],
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'storage_path' => storage_path('app/public'),
                    'storage_exists' => is_dir(storage_path('app/public')),
                    'storage_writable' => is_writable(storage_path('app/public')),
                ]
            ];

            Log::error('Image upload failed', $errorDetails);

            // Write to dedicated log file
            $logMessage = sprintf(
                "\n=== IMAGE UPLOAD ERROR ===\n" .
                "Time: %s\n" .
                "Error: %s\n" .
                "File: %s:%d\n" .
                "Type: %s\n" .
                "Session: %s\n" .
                "Storage Path: %s\n" .
                "Storage Exists: %s\n" .
                "Storage Writable: %s\n" .
                "PHP Version: %s\n" .
                "Trace:\n%s\n" .
                "==========================\n\n",
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $request->input('type', 'unknown'),
                $request->input('session_id', 'unknown'),
                storage_path('app/public'),
                is_dir(storage_path('app/public')) ? 'YES' : 'NO',
                is_writable(storage_path('app/public')) ? 'YES' : 'NO',
                PHP_VERSION,
                $e->getTraceAsString()
            );

            @file_put_contents(storage_path('logs/registration_upload_errors.log'), $logMessage, FILE_APPEND);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'debug_info' => [
                    'storage_path' => storage_path('app/public'),
                    'storage_exists' => is_dir(storage_path('app/public')),
                    'storage_writable' => is_writable(storage_path('app/public')),
                ]
            ], 500);
        }
    }

    /**
     * Ensure storage directory exists and is writable
     */
    private function ensureStorageExists()
    {
        $storagePath = storage_path('app/public');

        if (!is_dir($storagePath)) {
            Log::warning('Storage directory does not exist, creating it', ['path' => $storagePath]);

            if (!mkdir($storagePath, 0755, true)) {
                throw new \Exception('Failed to create storage directory: ' . $storagePath);
            }
        }

        if (!is_writable($storagePath)) {
            throw new \Exception('Storage directory is not writable: ' . $storagePath);
        }

        Log::info('Storage directory verified', [
            'path' => $storagePath,
            'exists' => true,
            'writable' => true
        ]);
    }

    /**
     * Move images from temp to permanent storage after successful registration
     */
    public function moveToPermStorage($tempPath, $type, $userId, $entityId = null, $imageNumber = null)
    {
        // Check if file doesn't exist in temp (already moved as duplicate)
        if (empty($tempPath)) {
            Log::warning('Cannot move image - path is empty', ['type' => $type]);
            return '';
        }

        Log::info('moveToPermStorage called', [
            'temp_path' => $tempPath,
            'type' => $type,
            'user_id' => $userId,
            'entity_id' => $entityId,
            'image_number' => $imageNumber,
            'full_storage_path' => storage_path('app/public/' . $tempPath)
        ]);

        $tempExists = Storage::disk('public')->exists($tempPath);

        Log::info('moveToPermStorage - file existence check', [
            'temp_path' => $tempPath,
            'exists' => $tempExists,
            'storage_disk_path' => Storage::disk('public')->path($tempPath)
        ]);

        // If file doesn't exist, try cleaning up the path and checking again
        if (!$tempExists) {
            // Try trimming whitespace and decoding URL encoding
            $cleanedPath = trim(urldecode($tempPath));
            // Normalize slashes to forward slashes
            $cleanedPath = str_replace('\\', '/', $cleanedPath);

            if ($cleanedPath !== $tempPath) {
                Log::info('moveToPermStorage - trying cleaned path', [
                    'original' => $tempPath,
                    'cleaned' => $cleanedPath
                ]);
                $tempExists = Storage::disk('public')->exists($cleanedPath);
                if ($tempExists) {
                    $tempPath = $cleanedPath;
                }
            }
        }

        // Get extension from temp file
        $extension = pathinfo($tempPath, PATHINFO_EXTENSION);

        // Generate structured filename
        $typeForName = $type === 'cover' ? 'hero' : ($type === 'avatar' ? 'profile' : ($type === 'certification' ? 'cert' : $type));
        if ($entityId !== null) {
            if ($imageNumber !== null) {
                // For products/projects with multiple images: product_123_1.jpg
                $structuredFilename = "{$typeForName}_{$entityId}_{$imageNumber}.{$extension}";
            } else {
                // For profiles/cover/services with single image: profile_45.jpg, hero_45.jpg
                $structuredFilename = "{$typeForName}_{$entityId}.{$extension}";
            }
        } else {
            // Fallback to sanitized original name if no entityId provided
            $structuredFilename = basename($tempPath);
            $structuredFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $structuredFilename);
        }

        // Map type to plural folder name for permanent storage
        $folderMap = [
            'avatar' => 'profiles',
            'profile' => 'profiles',
            'cover' => 'covers',
            'hero' => 'heroes',
            'product' => 'products',
            'project' => 'projects',
            'service' => 'services',
            'marketplace' => 'marketplace',
            'certification' => 'certifications'
        ];
        $folderName = $folderMap[$type] ?? $type . 's';
        $permanentPath = "{$folderName}/{$structuredFilename}";

        // If file doesn't exist in temp, check if it was already moved to permanent storage
        if (!$tempExists) {
            // If file exists in permanent storage, copy it with structured name
            $oldFilename = basename($tempPath);
            $oldFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $oldFilename);
            $oldPermanentPath = "{$folderName}/{$oldFilename}";

            if (Storage::disk('public')->exists($oldPermanentPath)) {
                Log::info('Duplicate image detected - file already in permanent storage, creating copy', [
                    'original_temp_path' => $tempPath,
                    'existing_permanent_path' => $oldPermanentPath,
                    'new_structured_path' => $permanentPath,
                    'type' => $type
                ]);

                try {
                    // Copy the existing file with structured name
                    Storage::disk('public')->copy($oldPermanentPath, $permanentPath);

                    Log::info('Duplicate image copied with structured name', [
                        'from' => $oldPermanentPath,
                        'to' => $permanentPath,
                        'user_id' => $userId
                    ]);

                    return $permanentPath;

                } catch (\Exception $e) {
                    Log::error('Failed to copy duplicate image', [
                        'error' => $e->getMessage(),
                        'permanent_path' => $oldPermanentPath
                    ]);
                    return '';
                }
            }

            Log::warning('Cannot move image - path does not exist in temp or permanent', [
                'temp_path' => $tempPath,
                'type' => $type
            ]);
            return '';
        }

        // Path traversal validation
        $realTempPath = realpath(storage_path('app/public/' . $tempPath));
        $basePath = realpath(storage_path('app/public'));

        if ($realTempPath === false || strpos($realTempPath, $basePath) !== 0) {
            Log::error('Path traversal attempt detected', [
                'temp_path' => $tempPath,
                'real_path' => $realTempPath,
                'base_path' => $basePath
            ]);
            throw new \Exception('Invalid file path detected');
        }

        try {
            $permanentDir = "{$type}s";

            // Ensure permanent directory exists
            $fullPermanentPath = storage_path('app/public/' . $permanentDir);
            if (!file_exists($fullPermanentPath)) {
                mkdir($fullPermanentPath, 0755, true);
            }

            // Move file from temp to permanent with structured name
            Storage::disk('public')->move($tempPath, $permanentPath);

            Log::info('Image moved to permanent storage with structured name', [
                'from' => $tempPath,
                'to' => $permanentPath,
                'entity_id' => $entityId,
                'image_number' => $imageNumber,
                'user_id' => $userId
            ]);

            return $permanentPath;

        } catch (\Exception $e) {
            Log::error('Failed to move image to permanent storage', [
                'error' => $e->getMessage(),
                'temp_path' => $tempPath,
                'type' => $type,
                'user_id' => $userId
            ]);
            return $tempPath; // Return original path if move fails
        }
    }

    /**
     * Find image by hash to prevent duplicate uploads
     */
    private function findImageByHash($hash, $sessionId, $type = null)
    {
        try {
            $metadataPath = "uploads/temp/metadata/{$sessionId}.json";

            if (!Storage::disk('public')->exists($metadataPath)) {
                return null;
            }

            $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);

            if (isset($metadata[$hash])) {
                // Only return if type matches (or no type check for backward compatibility)
                $storedType = $metadata[$hash]['type'] ?? null;
                $path = $metadata[$hash]['path'];

                // If type is specified, it must match
                if ($type !== null && $storedType !== null && $type !== $storedType) {
                    Log::info('Duplicate hash found but type mismatch', [
                        'hash' => $hash,
                        'requested_type' => $type,
                        'stored_type' => $storedType,
                        'path' => $path
                    ]);
                    return null; // Don't reuse if types don't match
                }

                // Verify file still exists
                if (Storage::disk('public')->exists($path)) {
                    return $path;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error finding image by hash', [
                'error' => $e->getMessage(),
                'hash' => $hash,
                'session_id' => $sessionId,
                'type' => $type
            ]);
            return null;
        }
    }

    /**
     * Store upload metadata for duplicate detection
     *
     * BUG-024 Fix: Metadata cleanup recommendation
     * To clean up old metadata files automatically, add a scheduled task:
     * In routes/console.php or app/Console/Kernel.php, add:
     *
     * Schedule::call(function () {
     *     $metadataPath = storage_path('app/public/uploads/temp/metadata');
     *     if (file_exists($metadataPath)) {
     *         $files = glob($metadataPath . '/*.json');
     *         foreach ($files as $file) {
     *             if (filemtime($file) < strtotime('-24 hours')) {
     *                 @unlink($file);
     *             }
     *         }
     *     }
     * })->daily();
     */
    private function storeUploadMetadata($path, $hash, $sessionId, $type = null)
    {
        if (empty($hash)) {
            return;
        }

        try {
            $metadataPath = "uploads/temp/metadata/{$sessionId}.json";
            $metadataDir = "uploads/temp/metadata";

            // Get the full directory path
            $fullMetadataPath = storage_path('app/public/' . $metadataDir);

            // Ensure metadata directory exists using PHP mkdir
            if (!file_exists($fullMetadataPath)) {
                mkdir($fullMetadataPath, 0755, true);
            }

            // Add max entries limit
            $maxEntries = 1000;
            $metadata = json_decode(Storage::disk('public')->get($metadataPath) ?? '{}', true) ?: [];

            // Prune old entries if over limit
            if (count($metadata) > $maxEntries) {
                $metadata = collect($metadata)
                    ->sortByDesc(fn($item) => $item['uploaded_at'] ?? '')
                    ->take($maxEntries)
                    ->toArray();
            }

            // Add type to metadata to prevent cross-type reuse
            $metadata[$hash] = [
                'path' => $path,
                'type' => $type,
                'uploaded_at' => now()->toIso8601String()
            ];

            // Save metadata
            Storage::disk('public')->put($metadataPath, json_encode($metadata));
        } catch (\Exception $e) {
            // Don't throw - metadata is optional
            Log::warning('Failed to store upload metadata', [
                'error' => $e->getMessage(),
                'path' => $path,
                'session_id' => $sessionId
            ]);
        }
    }

    /**
     * Upload PDF during registration wizard (for certifications)
     */
    public function uploadRegistrationPdf(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240', // 10MB
                'type' => 'required|in:certification',
                'session_id' => 'required|string|max:100',
            ]);

            $sessionId = $validated['session_id'];
            $type = $validated['type'];
            $file = $request->file('file');

            // Create temp folder
            $folderName = 'certifications';
            $folder = "uploads/temp/{$folderName}/{$sessionId}";
            $fullPath = storage_path('app/public/' . $folder);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Generate unique filename
            $uuid = Str::uuid()->toString();
            $extension = $file->guessExtension() ?? ($file->getClientOriginalExtension() ?: 'pdf');
            $filename = "{$uuid}.{$extension}";

            // Store file
            $storedPath = $file->storeAs($folder, $filename, 'public');

            if (!$storedPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store PDF file'
                ], 500);
            }

            Log::info('PDF uploaded successfully', [
                'path' => $storedPath,
                'type' => $type,
                'session_id' => $sessionId,
                'original_name' => $file->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => true,
                'path' => $storedPath,
                'message' => 'PDF uploaded successfully',
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(fn($msgs) => implode(', ', $msgs), $e->errors())),
            ], 422);
        } catch (\Exception $e) {
            Log::error('PDF upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the PDF'
            ], 500);
        }
    }

    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errorMessages = [
            UPLOAD_ERR_OK => "No error",
            UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize in php.ini",
            UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE in HTML form",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary upload folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload",
        ];

        return $errorMessages[$errorCode] ?? "Unknown upload error (code: {$errorCode})";
    }

    /**
     * Parse size string (like "2M") to bytes
     */
    private function parseSize($size)
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int) $size;

        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Get thumbnail path for an image.
     * Returns thumbnail if exists, otherwise returns original.
     */
    public static function getThumbnailPath($imagePath)
    {
        if (empty($imagePath)) {
            return $imagePath;
        }

        $pathInfo = pathinfo($imagePath);
        $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . ($pathInfo['extension'] ?? '');

        if (Storage::disk('public')->exists($thumbPath)) {
            return $thumbPath;
        }

        return $imagePath;
    }

    /**
     * Cleanup orphaned uploads (called from scheduled task)
     */
    public function cleanupOrphanedUploads(): array
    {
        $deleted = 0;
        $errors = [];
        $cutoff = now()->subHours(24)->timestamp;

        $tempFolders = ['temp/products', 'temp/projects', 'temp/profiles', 'temp/services', 'temp/marketplace'];

        foreach ($tempFolders as $folder) {
            if (!Storage::disk('public')->exists($folder)) {
                continue;
            }

            try {
                $files = Storage::disk('public')->allFiles($folder);

                // Process in chunks to avoid memory issues
                collect($files)->chunk(100)->each(function($chunk) use (&$deleted, &$errors, $cutoff) {
                    foreach ($chunk as $file) {
                        try {
                            $fileTime = Storage::disk('public')->lastModified($file);
                            if ($fileTime < $cutoff) {
                                Storage::disk('public')->delete($file);
                                $deleted++;
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Failed to process {$file}: " . $e->getMessage();
                        }
                    }
                });
            } catch (\Exception $e) {
                $errors[] = "Failed to list files in {$folder}: " . $e->getMessage();
            }
        }

        return [
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }
}
