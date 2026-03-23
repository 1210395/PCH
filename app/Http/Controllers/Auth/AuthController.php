<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\AcademicAccount;
use App\Models\Skill;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Service;
use App\Models\DesignCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Handles designer and academic account login/logout and the multi-step registration wizard.
 * Registration is wrapped in a DB transaction; temp images are moved to permanent storage on success.
 * Supports both designer and academic guards; directs admins to the admin dashboard on login.
 */
class AuthController extends Controller
{
    /**
     * Display the login form, preserving the intended redirect URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm(Request $request)
    {
        // If user is already logged in, redirect appropriately
        if (Auth::guard('designer')->check()) {
            return redirect()->route('profile', ['locale' => app()->getLocale()]);
        }

        if (Auth::guard('academic')->check()) {
            return redirect()->route('academic.dashboard', ['locale' => app()->getLocale()]);
        }

        // Store the intended URL from query parameter or referer header
        // This allows proper redirect after login when clicking login links on detail pages
        if ($request->has('redirect')) {
            session()->put('url.intended', $request->get('redirect'));
        } elseif (!session()->has('url.intended') && $request->headers->has('referer')) {
            $referer = $request->headers->get('referer');
            // Only store referer if it's from the same domain and not the login/register page itself
            $appUrl = config('app.url');
            if (str_starts_with($referer, $appUrl) &&
                !str_contains($referer, '/login') &&
                !str_contains($referer, '/register')) {
                session()->put('url.intended', $referer);
            }
        }

        return view('auth.login');
    }

    /**
     * Authenticate the user against both designer and academic guards, enforcing email verification and active status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate email and password
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Prepare credentials for authentication
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $locale = app()->getLocale();

        // First, try to authenticate as a designer
        if (Auth::guard('designer')->attempt($credentials, $request->boolean('remember'))) {
            // Get the authenticated designer
            $designer = Auth::guard('designer')->user();

            // Check if email is verified
            if (! $designer->hasVerifiedEmail()) {
                Auth::guard('designer')->logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => __('Please verify your email address before logging in. Check your inbox for the verification link.'),
                    'unverified' => true,
                ])->withInput($request->only('email'));
            }

            // Check if account is active (approved by admin)
            if (!$designer->is_active && !$designer->is_admin) {
                Auth::guard('designer')->logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your account is pending admin approval. This usually takes 1-12 hours. Please try again later.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Redirect admins to admin dashboard
            if ($designer->is_admin) {
                return redirect()->route('admin.dashboard', ['locale' => $locale]);
            }

            // Redirect regular users to their public portfolio page
            return redirect()->intended(route('designer.portfolio', [
                'locale' => $locale,
                'id' => $designer->id
            ]));
        }

        // If designer login failed, try academic account login
        if (Auth::guard('academic')->attempt($credentials, $request->boolean('remember'))) {
            $account = Auth::guard('academic')->user();

            // Check if account is active
            if (!$account->is_active) {
                Auth::guard('academic')->logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Redirect to academic dashboard
            return redirect()->route('academic.dashboard', ['locale' => $locale]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Display the multi-step registration form for new designer accounts.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRegistrationForm()
    {
        // If user is already logged in, redirect to their profile
        if (Auth::guard('designer')->check()) {
            return redirect()->route('profile', ['locale' => app()->getLocale()]);
        }

        return view('auth.register');
    }

    /**
     * Process the multi-step registration wizard, creating a Designer with optional products, projects, services, and certifications.
     * The entire operation runs inside a DB transaction; temp images are moved to permanent storage on success and cleaned up on failure.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // DEBUG: Log incoming request details
        if (config('app.debug')) {
            Log::debug('Registration request received', [
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'current_locale' => app()->getLocale(),
                'route_locale' => $request->route('locale'),
                'sector_from_request' => $request->input('sector'),
                'sub_sector_from_request' => $request->input('sub_sector'),
                'has_showroom' => $request->input('has_showroom'),
                'all_request_data' => $request->except(['password', 'password_confirmation'])
            ]);
        }

        // Check if user is a guest
        $isGuest = $request->input('sector') === 'guest';

        // Debug: Log incoming request for guests
        if ($isGuest && config('app.debug')) {
            Log::debug('Guest registration attempt', [
                'sector' => $request->input('sector'),
                'sub_sector' => $request->input('sub_sector'),
                'has_profile_image' => $request->has('profile_image_path'),
                'has_cover_image' => $request->has('cover_image_path'),
                'company_name' => $request->input('company_name'),
                'phone_number' => $request->input('phone_number'),
            ]);
        }

        // Validate all wizard steps
        try {
            if (config('app.debug')) {
                Log::debug('Starting validation', [
                    'has_sector' => $request->has('sector'),
                    'sector_value' => $request->input('sector'),
                    'has_sub_sector' => $request->has('sub_sector'),
                    'sub_sector_value' => $request->input('sub_sector'),
                    'has_showroom' => $request->has('has_showroom'),
                    'showroom_value' => $request->input('has_showroom')
                ]);
            }

            $validated = $request->validate([
                // Step 1: Account Creation
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:designers'],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()      // Require at least one uppercase and one lowercase letter
                        ->numbers()         // Require at least one number
                        ->symbols()         // Require at least one special character
                ],

                // Step 2: Profile Type
                'sector' => ['required', 'string', 'max:255'],
                'sub_sector' => ['required', 'string', 'max:255'],

                // Step 3: Profile Details (optional for guests)
                'profile_image' => ['nullable', 'image', 'max:5120'], // Optional if path provided
                'profile_image_path' => ['nullable', 'string'], // Pre-uploaded image path
                'cover_image' => ['nullable', 'image', 'max:5120'], // Optional if path provided
                'cover_image_path' => ['nullable', 'string'], // Pre-uploaded cover image path
                'company_name' => [$isGuest ? 'nullable' : 'required', 'string', 'max:255'],
                'position' => [$isGuest ? 'nullable' : 'required', 'string', 'max:255'],
                'phone_country' => ['nullable', 'string', 'max:2'], // Country code for phone
                'phone_number' => [$isGuest ? 'nullable' : 'required', 'string', 'min:7', 'max:12'],
                'city' => [$isGuest ? 'nullable' : 'required', 'string', 'max:100'],
                'address' => [$isGuest ? 'nullable' : 'required', 'string', 'min:10', 'max:200'],
                'years_of_experience' => [$isGuest ? 'nullable' : 'required', 'string', 'max:50'],
                'bio' => [$isGuest ? 'nullable' : 'required', 'string', 'min:50', 'max:500'],
                'skills' => ['nullable', 'string'], // JSON string

                // Step 4-6: Products, Projects, Services (optional)
                'products' => ['nullable', 'array'],
                'products.*.name' => ['nullable', 'string', 'max:255'],
                'products.*.description' => ['nullable', 'string', 'max:500'],
                'products.*.category' => ['nullable', 'string', 'max:255'],
                'products.*.image' => ['nullable', 'image', 'max:5120'],
                'products.*.image_path' => ['nullable', 'string'], // Deprecated - single image
                'products.*.image_paths' => ['nullable', 'array', 'max:6'], // Multiple images support
                'products.*.image_paths.*' => ['nullable', 'string'],

                'projects' => ['nullable', 'array'],
                'projects.*.title' => ['nullable', 'string', 'max:255'],
                'projects.*.description' => ['nullable', 'string', 'max:500'],
                'projects.*.role' => ['nullable', 'string', 'max:255'],
                'projects.*.category' => ['nullable', 'string', 'max:255'],
                'projects.*.image' => ['nullable', 'image', 'max:5120'],
                'projects.*.image_path' => ['nullable', 'string'], // Deprecated - single image
                'projects.*.image_paths' => ['nullable', 'array', 'max:6'], // Multiple images support
                'projects.*.image_paths.*' => ['nullable', 'string'],

                'services' => ['nullable', 'array'],
                'services.*.name' => ['nullable', 'string', 'max:255'],
                'services.*.description' => ['nullable', 'string', 'max:500'],
                'services.*.category' => ['nullable', 'string', 'max:255'],
                'services.*.image' => ['nullable', 'image', 'max:5120'],
                'services.*.image_path' => ['nullable', 'string'],

                // Education & Certifications (PDFs)
                'certification_paths' => ['nullable', 'array', 'max:3'],
                'certification_paths.*' => ['nullable', 'string'],

                'upload_session_id' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::error('Registration validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'sector' => $request->input('sector'),
                'sub_sector' => $request->input('sub_sector'),
                'has_showroom' => $request->input('has_showroom'),
            ]);
            throw $e;
        }

        // Additional validation: phone number format (only if provided)
        if (!$isGuest && !empty($request->phone_number)) {
            // For Palestine, validate the format (9 digits starting with 5, no leading 0)
            if ($request->phone_country === 'PS' || empty($request->phone_country)) {
                $request->validate([
                    'phone_number' => ['regex:/^5[0-9]{8}$/', 'size:9'],
                ]);
            }
        }

        // Validate that profile image was provided (either upload or pre-uploaded path) - except for guests
        if (!$isGuest && !$request->hasFile('profile_image') && !$request->has('profile_image_path')) {
            return back()->withErrors(['profile_image' => 'Profile picture is required.'])->withInput();
        }

        // Validate that cover image was provided (either upload or pre-uploaded path) - except for guests
        if (!$isGuest && !$request->hasFile('cover_image') && !$request->has('cover_image_path')) {
            return back()->withErrors(['cover_image' => 'Cover image is required.'])->withInput();
        }

        // Validate certifications required for designer sector
        if ($request->input('sector') === 'designer') {
            $certPaths = array_filter($request->input('certification_paths', []) ?? []);
            if (empty($certPaths)) {
                return back()->withErrors(['certifications' => 'At least one Education & Certification PDF is required for designers.'])->withInput();
            }
        }

        // Wrap entire registration in a database transaction
        try {
            DB::beginTransaction();

            // Handle profile image - use pre-uploaded path or upload now
            // Parse skills from JSON (handle multiple encoding levels)
            $skills = [];
            if (!empty($validated['skills'])) {
                $skillsData = $validated['skills'];

                // If it's already an array, use it
                if (is_array($skillsData)) {
                    $skills = $skillsData;
                } else {
                    // Decode JSON, handling multiple encoding levels
                    while (is_string($skillsData) && !empty($skillsData)) {
                        $decoded = json_decode($skillsData, true);
                        if ($decoded === null || $decoded === $skillsData) {
                            // Decoding failed or didn't change anything
                            break;
                        }
                        $skillsData = $decoded;
                    }

                    // Final result should be an array
                    $skills = is_array($skillsData) ? $skillsData : [];
                }
            }

            // Create the designer first (without profile picture)
            // New accounts are inactive by default unless auto-accept is enabled
            $designer = Designer::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'sector' => $validated['sector'],
                'sub_sector' => $validated['sub_sector'],
                'avatar' => null, // Will be updated after moving to permanent storage
                'company_name' => $validated['company_name'] ?? null,
                'position' => $validated['position'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'phone_country' => $validated['phone_country'] ?? 'PS',
                'city' => $validated['city'] ?? null,
                'address' => $validated['address'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'title' => $validated['position'] ?? null, // Use position as title
                // email_verified_at left null - user must verify via email link
                // is_active is set by Designer model boot method based on admin auto-accept setting
            ]);

            // DEBUG: Log what was actually saved
            if (config('app.debug')) {
                Log::debug('Designer created', [
                    'designer_id' => $designer->id,
                    'sector_saved' => $designer->sector,
                    'sub_sector_saved' => $designer->sub_sector,
                    'sector_from_validated' => $validated['sector'],
                    'sub_sector_from_validated' => $validated['sub_sector']
                ]);
            }

            // Move profile image to permanent storage with structured naming
            $avatarPath = null;
            if ($request->has('profile_image_path')) {
                // Use pre-uploaded image and move from temp to permanent storage
                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
                // Structured naming: profile_123.jpg
                $avatarPath = $imageUploader->moveToPermStorage(
                    $request->profile_image_path,
                    'profile',
                    $designer->id,
                    $designer->id // entityId = designerId for profiles
                );
            } elseif ($request->hasFile('profile_image')) {
                // Fallback: upload now if not pre-uploaded
                $file = $request->file('profile_image');
                $extension = ($file->guessExtension() ?? $file->getClientOriginalExtension());
                $filename = "profile_{$designer->id}.{$extension}";
                $avatarPath = $file->storeAs('profiles', $filename, 'public');
            }

            // Update designer with avatar path
            if ($avatarPath) {
                $designer->update(['avatar' => $avatarPath]);
            }

            // Handle cover image upload
            $coverPath = null;
            if (!isset($imageUploader)) {
                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
            }
            if ($request->has('cover_image_path')) {
                // Move from temp to permanent storage with structured naming
                $coverPath = $imageUploader->moveToPermStorage(
                    $request->cover_image_path,
                    'cover',
                    $designer->id,
                    $designer->id // entityId = designerId for cover images
                );
            } elseif ($request->hasFile('cover_image')) {
                // Fallback: upload now if not pre-uploaded
                $file = $request->file('cover_image');
                $extension = ($file->guessExtension() ?? $file->getClientOriginalExtension());
                $filename = "hero_{$designer->id}.{$extension}";
                $coverPath = $file->storeAs('covers', $filename, 'public');
            }

            // Update designer with cover image path
            if ($coverPath) {
                $designer->update(['cover_image' => $coverPath]);
            }

            // Attach skills efficiently using bulk operations
            if (!empty($skills)) {
                // First, get all existing skills in one query
                $skillSlugs = array_map(fn($name) => Str::slug($name), $skills);
                $existingSkills = Skill::whereIn('slug', $skillSlugs)->get()->keyBy('slug');

                // Prepare skills to create
                $skillsToCreate = [];
                $skillIds = [];

                foreach ($skills as $skillName) {
                    $slug = Str::slug($skillName);

                    if (isset($existingSkills[$slug])) {
                        // Skill already exists
                        $skillIds[] = $existingSkills[$slug]->id;
                    } else {
                        // Prepare for bulk insert
                        $skillsToCreate[] = [
                            'name' => $skillName,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Bulk insert new skills if any
                if (!empty($skillsToCreate)) {
                    Skill::insert($skillsToCreate);
                    // Get the newly created skill IDs
                    $newSkillSlugs = array_column($skillsToCreate, 'slug');
                    $newSkills = Skill::whereIn('slug', $newSkillSlugs)->get();
                    foreach ($newSkills as $skill) {
                        $skillIds[] = $skill->id;
                    }
                }

                // Attach all skills in one operation
                $designer->skills()->attach($skillIds);
            }

            // Handle certification PDFs
            $certificationPaths = [];
            if (!empty($request->input('certification_paths'))) {
                $certPaths = array_filter($request->input('certification_paths') ?? []);
                $certPaths = array_slice($certPaths, 0, 3); // Max 3

                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
                foreach ($certPaths as $index => $certPath) {
                    if (!empty($certPath)) {
                        $permanentPath = $imageUploader->moveToPermStorage(
                            $certPath,
                            'certification',
                            $designer->id,
                            $designer->id,
                            $index + 1
                        );
                        if (!empty($permanentPath)) {
                            $certificationPaths[] = $permanentPath;
                        }
                    }
                }
            }
            if (!empty($certificationPaths)) {
                $designer->update(['certifications' => $certificationPaths]);
            }

            // Track counts for updating designer
            $productsCount = 0;
            $projectsCount = 0;
            $servicesCount = 0;

            // Handle products upload
            if (!empty($validated['products'])) {
                foreach ($validated['products'] as $index => $product) {
                    if (!empty($product['name']) && !empty($product['description']) && !empty($product['category'])) {
                        // Wrap each product creation in try-catch to prevent AUTO_INCREMENT gaps
                        try {
                            $productImage = '';

                            // Handle backward compatibility for single image
                            if (!empty($product['image_path'])) {
                                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
                                $productImage = $imageUploader->moveToPermStorage(
                                    $product['image_path'],
                                    'product',
                                    $designer->id
                                );
                            } elseif ($request->hasFile("products.{$index}.image")) {
                                $productImage = $request->file("products.{$index}.image")->store('products', 'public');
                            }

                            // Create product
                            $createdProduct = Product::create([
                                'designer_id' => $designer->id,
                                'title' => $product['name'],
                                'description' => $product['description'],
                                'category' => $product['category'],
                                'image' => $productImage,
                                'featured' => false,
                            ]);

                            // Save multiple images to product_images table
                            // Only proceed if product was created successfully
                            if (!empty($product['image_paths']) && is_array($product['image_paths'])) {
                                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();

                                // Reindex array to ensure sequential 0-based keys
                                $imagePaths = array_values(array_filter($product['image_paths'], function ($path) {
                                    return !empty($path);
                                }));

                                
                                $displayOrder = 0;

                                foreach ($imagePaths as $imgIndex => $imagePath) {
                                    // Image number starts from 1 for user-friendly naming
                                    $imageNumber = $displayOrder + 1;


                                    // Move image with structured naming
                                    $permanentPath = $imageUploader->moveToPermStorage(
                                        $imagePath,
                                        'product',
                                        $designer->id,
                                        $createdProduct->id,
                                        $imageNumber
                                    );


                                    // Only save if move was successful (returns non-empty path)
                                    if (!empty($permanentPath)) {
                                        $created = ProductImage::create([
                                            'product_id' => $createdProduct->id,
                                            'image_path' => $permanentPath,
                                            'display_order' => $displayOrder,
                                            'is_primary' => $displayOrder === 0 ? 1 : 0, // First image is primary
                                        ]);


                                        $displayOrder++; // Increment for next image
                                    } else {
                                        Log::error('Failed to move image to permanent storage', [
                                            'imgIndex' => $imgIndex,
                                            'tempPath' => $imagePath
                                        ]);
                                    }
                                }

                            }

                            $productsCount++;

                        } catch (\Exception $e) {
                            // Log failure but continue with other products
                            Log::error('Failed to create product during registration', [
                                'product_index' => $index,
                                'product_name' => $product['name'] ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            // Continue to next product instead of failing entire registration
                            continue;
                        }
                    }
                }
            }

            // Handle projects upload
            if (!empty($validated['projects'])) {
                // Get or create a default "General" category for projects
                $defaultCategory = DesignCategory::firstOrCreate(
                    ['slug' => 'general'],
                    ['name' => 'General', 'description' => 'General projects']
                );

                foreach ($validated['projects'] as $index => $project) {
                    if (!empty($project['title']) && !empty($project['description']) && !empty($project['role'])) {
                        // Wrap each project creation in try-catch to prevent AUTO_INCREMENT gaps
                        try {
                            $projectImage = '';

                            // Handle backward compatibility for single image
                            if (!empty($project['image_path'])) {
                                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
                                $projectImage = $imageUploader->moveToPermStorage(
                                    $project['image_path'],
                                    'project',
                                    $designer->id
                                );
                            } elseif ($request->hasFile("projects.{$index}.image")) {
                                $projectImage = $request->file("projects.{$index}.image")->store('projects', 'public');
                            }

                            // Determine category - use user selected category or default to General
                            $projectCategory = !empty($project['category']) ? $project['category'] : 'General';

                            // Find or create the category in design_categories table
                            $categorySlug = \Illuminate\Support\Str::slug($projectCategory);
                            $designCategory = DesignCategory::firstOrCreate(
                                ['slug' => $categorySlug],
                                ['name' => $projectCategory, 'description' => $projectCategory . ' projects']
                            );

                            // Create project
                            $createdProject = Project::create([
                                'designer_id' => $designer->id,
                                'category_id' => $designCategory->id,
                                'category' => $projectCategory,
                                'title' => $project['title'],
                                'description' => $project['description'],
                                'role' => $project['role'],
                                'image' => $projectImage ?? '',
                                'featured' => false,
                            ]);

                            // Save multiple images to project_images table
                            // Only proceed if project was created successfully
                            if (!empty($project['image_paths']) && is_array($project['image_paths'])) {
                                $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();

                                // Reindex array to ensure sequential 0-based keys
                                $imagePaths = array_values(array_filter($project['image_paths'], function ($path) {
                                    return !empty($path);
                                }));

                                
                                $displayOrder = 0;

                                foreach ($imagePaths as $imgIndex => $imagePath) {
                                    // Image number starts from 1 for user-friendly naming
                                    $imageNumber = $displayOrder + 1;


                                    // Move image with structured naming
                                    $permanentPath = $imageUploader->moveToPermStorage(
                                        $imagePath,
                                        'project',
                                        $designer->id,
                                        $createdProject->id,
                                        $imageNumber
                                    );


                                    // Only save if move was successful (returns non-empty path)
                                    if (!empty($permanentPath)) {
                                        $created = ProjectImage::create([
                                            'project_id' => $createdProject->id,
                                            'image_path' => $permanentPath,
                                            'display_order' => $displayOrder,
                                            'is_primary' => $displayOrder === 0 ? 1 : 0, // First image is primary
                                        ]);


                                        $displayOrder++; // Increment for next image
                                    } else {
                                        Log::error('Failed to move image to permanent storage', [
                                            'imgIndex' => $imgIndex,
                                            'tempPath' => $imagePath
                                        ]);
                                    }
                                }

                            }

                            $projectsCount++;

                        } catch (\Exception $e) {
                            // Log failure but continue with other projects
                            Log::error('Failed to create project during registration', [
                                'project_index' => $index,
                                'project_title' => $project['title'] ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            // Continue to next project instead of failing entire registration
                            continue;
                        }
                    }
                }
            }

            // Handle services upload
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $index => $service) {
                    if (!empty($service['name']) && !empty($service['description']) && !empty($service['category'])) {
                        // Create service first to get ID
                        $createdService = Service::create([
                            'designer_id' => $designer->id,
                            'name' => $service['name'],
                            'description' => $service['description'],
                            'category' => $service['category'],
                            'image' => '', // Will be updated after image processing
                        ]);

                        $serviceImage = '';

                        // Use pre-uploaded path or upload now with structured naming
                        if (!empty($service['image_path'])) {
                            $imageUploader = new \App\Http\Controllers\Auth\ImageUploadController();
                            // Structured naming: service_123.jpg
                            $serviceImage = $imageUploader->moveToPermStorage(
                                $service['image_path'],
                                'service',
                                $designer->id,
                                $createdService->id // Use service ID for structured naming
                            );
                        } elseif ($request->hasFile("services.{$index}.image")) {
                            $file = $request->file("services.{$index}.image");
                            $extension = ($file->guessExtension() ?? $file->getClientOriginalExtension());
                            $filename = "service_{$createdService->id}.{$extension}";
                            $serviceImage = $file->storeAs('services', $filename, 'public');
                        }

                        // Update service with image path
                        if ($serviceImage) {
                            $createdService->update(['image' => $serviceImage]);
                        }

                        $servicesCount++;
                    }
                }
            }

            // Update designer's projects_count counter
            if ($projectsCount > 0) {
                $designer->update(['projects_count' => $projectsCount]);
            }

            // Commit the transaction - all data saved successfully
            DB::commit();

            // Cleanup temp files on success
            if ($request->has('upload_session_id')) {
                $this->cleanupTempFiles($request->upload_session_id);
            }

            // Log what was created for debugging
            Log::debug('Registration completed', [
                'designer_id' => $designer->id,
                'designer_email' => $designer->email,
                'products_created' => $productsCount,
                'projects_created' => $projectsCount,
                'services_created' => $servicesCount,
                'products_submitted' => count($validated['products'] ?? []),
                'projects_submitted' => count($validated['projects'] ?? []),
                'services_submitted' => count($validated['services'] ?? []),
            ]);

            // Send email verification notification
            try {
                $designer->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::error('Failed to send verification email', [
                    'designer_id' => $designer->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Redirect to success page with registration complete message
            $locale = app()->getLocale();

            return redirect()->route('register.success', ['locale' => $locale])
                ->with('registration_stats', [
                    'products' => $productsCount,
                    'projects' => $projectsCount,
                    'services' => $servicesCount,
                ])
                ->with('verification_email', $designer->email);

        } catch (\Exception $e) {
            // Rollback the transaction on any error
            DB::rollBack();

            // Cleanup temp files on failure
            if ($request->has('upload_session_id')) {
                $this->cleanupTempFiles($request->upload_session_id);
            }

            // Log the error
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $validated['email'] ?? 'unknown'
            ]);

            // DEBUG: Show actual error message in development
            $errorMessage = 'Registration failed. Please try again.';
            if (config('app.debug')) {
                $errorMessage = 'Registration failed: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')';
            }

            // Return error to user
            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    /**
     * Cleanup temporary uploaded files after registration
     */
    private function cleanupTempFiles($sessionId)
    {
        try {
            // Clean old temp_uploads path (legacy)
            $oldTempPath = storage_path('app/temp_uploads/' . $sessionId);
            if (file_exists($oldTempPath) && is_dir($oldTempPath)) {
                $files = glob($oldTempPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
                @rmdir($oldTempPath);
            }

            // Clean new uploads/temp structure (current)
            $types = ['profiles', 'products', 'projects', 'services', 'certifications'];
            foreach ($types as $type) {
                $typePath = "uploads/temp/{$type}/{$sessionId}";
                if (\Storage::disk('public')->exists($typePath)) {
                    \Storage::disk('public')->deleteDirectory($typePath);
                }
            }

            // Clean metadata file
            $metadataPath = "uploads/temp/metadata/{$sessionId}.json";
            if (\Storage::disk('public')->exists($metadataPath)) {
                \Storage::disk('public')->delete($metadataPath);
            }

        } catch (\Exception $e) {
            // Log cleanup failure but don't interrupt registration flow
            Log::warning('Failed to cleanup temp files', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the registration success page after a new account is created and the verification email is sent.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationSuccess()
    {
        return view('auth.register-success');
    }

    /**
     * Log out the authenticated designer, clear the intended URL, and invalidate the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('designer')->logout();

        // Clear intended URL to prevent redirect issues when logging in with different account
        $request->session()->forget('url.intended');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to home page instead of login to clear referer
        return redirect(route('home', ['locale' => app()->getLocale()]));
    }

}
