<script>
// Toast notification function
function showToast(message, type = 'info') {
    // For now, use console and alert
    if (type === 'error' || type === 'warning') {
        alert(message);
    }
}

function signupWizard() {
    return {
        currentStep: 1,
        completedSteps: [],
        selectedSkill: '',
        customSkill: '',
        profileImagePreview: null,
        heroImagePreview: null,
        errors: {},
        isValidating: false,
        isNavigating: false, // Lock navigation during validation
        autoSaveTimer: null,
        showPassword: false,
        showConfirmPassword: false,
        passwordStrength: { score: 0, label: '' },
        passwordChecks: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        },

        // Progressive upload tracking
        uploadSession: null, // Unique session ID for this registration
        uploading: false, // Global upload state
        uploadMutex: false, // Prevent concurrent uploads
        uploadProgress: {}, // Track upload progress per image
        uploadedPaths: {
            profile: null,
            cover: null,
            products: {}, // productId -> path
            projects: {}, // projectId -> path
            services: {}, // serviceId -> path
            certifications: [] // Array of temp paths
        },

        // Terms & Privacy acceptance (step 7)
        termsAccepted: false,

        // Publish confirmation modal state
        showPublishConfirmModal: false,
        mediaOwnershipConfirmed: false,
        policiesConfirmed: false,
        showPoliciesModal: false,

        // Initialize component
        init() {
            // Mark that page is loading (detect refresh vs close)
            sessionStorage.setItem('pageIsRefreshing', 'true');

            // Generate unique upload session ID
            this.uploadSession = this.getOrCreateUploadSession();

            // Check for Laravel validation errors and navigate to appropriate step
            this.handleBackendErrors();

            // Load saved data from localStorage
            this.loadFromLocalStorage();

            // Watch for changes and auto-save
            this.$watch('formData', () => {
                this.scheduleAutoSave();
            }, { deep: true });

            // Prevent accidental page leave
            window.addEventListener('beforeunload', (e) => {
                // Always save progress before leaving
                this.saveToLocalStorage();

                // Only show confirmation dialog if user has progressed past step 1
                // and this is not a language switch (language switch sets pageIsRefreshing)
                if (this.currentStep > 1 && this.currentStep < 7 && !sessionStorage.getItem('pageIsRefreshing')) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Handle page unload - detect tab close vs refresh
            window.addEventListener('unload', () => {
                // If pageIsRefreshing is still set, it's a refresh - keep data
                // If not set, it's a real tab close - delete data
                if (!sessionStorage.getItem('pageIsRefreshing')) {
                    // Tab is being closed, not refreshed - delete all cached data
                    this.clearLocalStorage();
                }
            });

            // Clear the refresh flag after page loads (must be in next tick)
            setTimeout(() => {
                sessionStorage.removeItem('pageIsRefreshing');
            }, 100);
        },

        handleBackendErrors() {
            // Check for Laravel validation errors
            const errors = {!! json_encode($errors->messages()) !!} || {};

            // Get old input values from Laravel
            const oldInput = {
                first_name: {!! json_encode(old('first_name')) !!},
                last_name: {!! json_encode(old('last_name')) !!},
                email: {!! json_encode(old('email')) !!},
                sector: {!! json_encode(old('sector')) !!},
                sub_sector: {!! json_encode(old('sub_sector')) !!},
                company_name: {!! json_encode(old('company_name')) !!},
                position: {!! json_encode(old('position')) !!},
                phone_number: {!! json_encode(old('phone_number')) !!},
                city: {!! json_encode(old('city')) !!},
                address: {!! json_encode(old('address')) !!},
                years_of_experience: {!! json_encode(old('years_of_experience')) !!},
                bio: {!! json_encode(old('bio')) !!},
                skills: {!! json_encode(old('skills')) !!}
            };

            // Restore old input values if they exist
            if (oldInput.first_name) this.formData.firstName = oldInput.first_name;
            if (oldInput.last_name) this.formData.lastName = oldInput.last_name;
            if (oldInput.email) this.formData.email = oldInput.email;
            if (oldInput.sector) this.formData.sector = oldInput.sector;
            if (oldInput.sub_sector) this.formData.subSector = oldInput.sub_sector;
            if (oldInput.company_name) this.formData.companyName = oldInput.company_name;
            if (oldInput.position) this.formData.position = oldInput.position;
            if (oldInput.phone_number) this.formData.phoneNumber = oldInput.phone_number;
            if (oldInput.city) this.formData.city = oldInput.city;
            if (oldInput.address) this.formData.address = oldInput.address;
            if (oldInput.years_of_experience) this.formData.yearsOfExperience = oldInput.years_of_experience;
            if (oldInput.bio) this.formData.bio = oldInput.bio;
            if (oldInput.skills) this.formData.skills = oldInput.skills;

            if (Object.keys(errors).length > 0) {

                // Map errors to frontend error state
                Object.keys(errors).forEach(field => {
                    const message = errors[field][0]; // Get first error message

                    // Map backend field names to frontend field names
                    if (field === 'first_name') this.setError('firstName', message);
                    else if (field === 'last_name') this.setError('lastName', message);
                    else if (field === 'email') this.setError('email', message);
                    else if (field === 'password') this.setError('password', message);
                    else if (field === 'sector') this.setError('sector', message);
                    else if (field === 'sub_sector') this.setError('subSector', message);
                    else if (field === 'profile_image') this.setError('profile_image', message);
                    else if (field === 'cover_image') this.setError('cover_image', message);
                    else if (field === 'company_name') this.setError('companyName', message);
                    else if (field === 'position') this.setError('position', message);
                    else if (field === 'years_of_experience') this.setError('yearsOfExperience', message);
                    else if (field === 'bio') this.setError('bio', message);
                });

                // Navigate to the step with errors
                if (errors.first_name || errors.last_name || errors.email || errors.password) {
                    this.currentStep = 1;
                } else if (errors.sector || errors.sub_sector) {
                    this.currentStep = 2;
                } else if (errors.profile_image || errors.cover_image || errors.company_name || errors.position || errors.years_of_experience || errors.bio || errors.skills) {
                    this.currentStep = 3;
                } else if (errors.error) {
                    // General error - stay on step 1 to show the error alert
                    this.currentStep = 1;
                }

                // Scroll to top to show errors
                window.scrollTo(0, 0);
            }
        },

        // ============================================================
        // UPLOAD SESSION MANAGEMENT
        // ============================================================

        getOrCreateUploadSession() {
            let sessionId = localStorage.getItem('uploadSessionId');
            if (!sessionId) {
                sessionId = this.generateUUID();
                localStorage.setItem('uploadSessionId', sessionId);
            }
            return sessionId;
        },

        generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },

        // ============================================================
        // PROGRESSIVE IMAGE UPLOAD
        // ============================================================

        // ============================================================
        // IMAGE VALIDATION
        // ============================================================

        validateImageFile(file) {
            // Check if file exists
            if (!file) {
                return { valid: false, error: '{{ __("No file selected.") }}' };
            }

            // Check if file is empty
            if (file.size === 0) {
                return { valid: false, error: '{{ __("The selected file is empty or corrupted.") }}' };
            }

            // Check file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                return {
                    valid: false,
                    error: `{{ __("File size is") }} ${fileSizeMB}{{ __("MB, which exceeds the 5MB limit.") }}\n\n{{ __("Please choose a smaller image or compress it before uploading.") }}`
                };
            }

            // Match uploadImage validation exactly (no webp)
            // Check file type - must be an image
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                const fileExtension = file.name.split('.').pop().toUpperCase();
                return {
                    valid: false,
                    error: `{{ __("Invalid file type:") }} ${fileExtension}\n\n{{ __("Only JPG, PNG, and GIF images are allowed.") }}\n\n{{ __("Selected file type:") }} ${file.type || '{{ __("Unknown") }}'}`
                };
            }

            // Check file extension matches type
            const fileName = file.name.toLowerCase();
            if (!fileName.endsWith('.jpg') && !fileName.endsWith('.jpeg') &&
                !fileName.endsWith('.png') && !fileName.endsWith('.gif')) {
                return {
                    valid: false,
                    error: `{{ __("Invalid file extension.") }}\n\n{{ __("Please upload a file with .jpg, .png, or .gif extension.") }}`
                };
            }

            // All validations passed
            return { valid: true };
        },

        async calculateFileHash(file) {
            // Simple hash using file name, size, and last modified
            const str = file.name + file.size + file.lastModified;
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return hash.toString(36);
        },

        async uploadImage(file, type, itemId = null) {
            // Allow multiple simultaneous uploads for products/projects with multiple images
            // Only use mutex for profile images to prevent duplicate uploads of the same profile picture
            const useMutex = (type === 'profile');

            if (useMutex) {
                // Check upload mutex to prevent concurrent uploads (profile only)
                if (this.uploadMutex) {
                    showToast('{{ __("Please wait for the current upload to complete before uploading another image.") }}', 'warning');
                    return;
                }

                // Acquire mutex lock
                this.uploadMutex = true;
            } else {
            }

            // Create AbortController for timeout
            const abortController = new AbortController();
            const timeoutId = setTimeout(() => abortController.abort(), 30000); // 30 seconds timeout

            try {
                // Validate file before upload
                if (!file || file.size === 0) {
                    throw new Error('{{ __("File is empty or invalid") }}');
                }

                if (file.size > 5 * 1024 * 1024) {
                    throw new Error('{{ __("File size exceeds 5MB limit") }}');
                }

                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    throw new Error('{{ __("Invalid file type. Please upload JPG, PNG, or GIF images only.") }}');
                }

                // Calculate file hash for duplicate detection
                // BUG FIX: For products/projects, add unique identifier to hash to force separate uploads
                // This prevents reusing cover/profile image paths which causes "file not found" errors
                let fileHash = await this.calculateFileHash(file);
                if (type === 'product' || type === 'project') {
                    // Add itemId to hash to make each product/project image unique
                    fileHash = fileHash + '_' + (itemId || Date.now());
                }

                // Prepare form data
                const formData = new FormData();
                formData.append('image', file);
                formData.append('type', type);
                formData.append('session_id', this.uploadSession);
                formData.append('file_hash', fileHash);

                // Track progress
                const progressKey = itemId || type;
                this.uploadProgress[progressKey] = 0;
                this.uploading = true;

                const uploadUrl = '{{ route("upload.registration.image", ["locale" => app()->getLocale()]) }}';

                // Upload with timeout support
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData,
                    signal: abortController.signal
                });

                //     status: response.status,
                //     statusText: response.statusText,
                //     ok: response.ok,
                //     headers: {
                //         contentType: response.headers.get('content-type')
                //     }
                // });

                // Check if response is OK (status 200-299)
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Server error response (Status ' + response.status + '):', errorText);

                    // Try to extract meaningful error
                    let errorMsg = `{{ __("Server error") }} (${response.status})`;
                    let validationErrors = null;

                    try {
                        const errorData = JSON.parse(errorText);
                        console.error('❌ Parsed error data:', errorData);

                        // Handle validation errors (422)
                        if (response.status === 422 && errorData.errors) {
                            validationErrors = errorData.errors;
                            console.error('❌ Validation errors:', validationErrors);

                            // Extract all validation error messages
                            let errorMessages = [];
                            for (let field in validationErrors) {
                                if (validationErrors[field] && Array.isArray(validationErrors[field])) {
                                    errorMessages = errorMessages.concat(validationErrors[field]);
                                }
                            }

                            if (errorMessages.length > 0) {
                                errorMsg = '{{ __("VALIDATION ERROR:") }}\n\n' + errorMessages.join('\n');
                            }
                        } else if (errorData.message) {
                            errorMsg = errorData.message;
                        }

                        if (errorData.debug_info) {
                            console.error('Debug info:', errorData.debug_info);
                            errorMsg += '\n\nDebug Info:\n' + JSON.stringify(errorData.debug_info, null, 2);
                        }
                    } catch (e) {
                        // Error text is not JSON
                        console.error('Failed to parse error as JSON:', e);
                        console.error('Raw error text:', errorText.substring(0, 500));
                        errorMsg = `{{ __("Server error") }} (${response.status}). {{ __("Check console for details.") }}`;
                    }

                    throw new Error(errorMsg);
                }

                // Try to parse JSON response
                let data;
                try {
                    const responseText = await response.text();
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('❌ Failed to parse server response as JSON:', parseError);
                    throw new Error('{{ __("Server returned an invalid response. Check console and logs.") }}');
                }

                if (data.success) {
                    // Store uploaded path
                    if (type === 'profile') {
                        this.uploadedPaths.profile = data.path;
                    } else if (type === 'cover') {
                        this.uploadedPaths.cover = data.path;
                    } else if (itemId) {
                        this.uploadedPaths[type + 's'][itemId] = data.path;
                    }

                    this.uploadProgress[progressKey] = 100;

                    if (data.duplicate) {
                    }

                    return data.path;
                } else {
                    throw new Error(data.message || '{{ __("Upload failed") }}');
                }

            } catch (error) {
                console.error('❌ Upload error:', error);
                console.error('Error stack:', error.stack);

                // Clear preview on error
                if (type === 'profile') {
                    this.profileImagePreview = null;
                }

                // More user-friendly error message
                let errorMessage = '{{ __("Image upload failed.") }}\n\n';

                // Handle timeout error
                if (error.name === 'AbortError') {
                    errorMessage += '{{ __("Upload timed out after 30 seconds. Please check your internet connection and try again.") }}';
                } else if (error.message.includes('status')) {
                    errorMessage += '{{ __("Server error occurred. Check the console and server logs for details.") }}';
                } else if (error.message.includes('invalid response')) {
                    errorMessage += '{{ __("Server configuration issue. Check storage/logs/registration_upload_errors.log") }}';
                } else {
                    errorMessage += error.message;
                }

                errorMessage += '\n\n{{ __("Please check browser console (F12) for detailed error information.") }}';

                showToast(errorMessage, 'error');
                return null;
            } finally {
                // Clear timeout
                clearTimeout(timeoutId);

                // Only release mutex if we acquired it (profile images only)
                if (useMutex) {
                    this.uploadMutex = false;
                }
                this.uploading = false;
            }
        },

        steps: [
            { number: 1, title: '{{ __("Account") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>' },
            { number: 2, title: '{{ __("Profile Type") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' },
            { number: 3, title: '{{ __("Profile Details") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>' },
            { number: 4, title: '{{ __("Products") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>' },
            { number: 5, title: '{{ __("Projects") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>' },
            { number: 6, title: '{{ __("Services") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>' },
            { number: 7, title: '{{ __("Review") }}', icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>' }
        ],

        sectorOptions: @json(\App\Helpers\DropdownHelper::sectorsForJs()),

        skillOptions: @json(\App\Helpers\DropdownHelper::skills()),

        formData: {
            firstName: '',
            lastName: '',
            email: '',
            password: '',
            confirmPassword: '',
            sector: '',
            subSector: '',
            hasShowroom: '',
            companyName: '',
            position: '',
            phoneNumber: '',
            phoneCountry: 'PS',
            city: '',
            address: '',
            yearsOfExperience: '',
            bio: '',
            skills: [],
            certifications: [], // Array of {id, name, path, uploading}
            products: [],
            projects: [],
            services: []
        },

        handleSectorChange() {
            // Clear sub-sector when sector changes
            this.formData.subSector = '';

            // Dispatch event to notify sub-sector dropdown to clear its display
            this.$el.dispatchEvent(new CustomEvent('sector-changed', { bubbles: true }));

            // If guest is selected, skip to review step (step 7)
            if (this.formData.sector === 'guest') {
                // Guest doesn't need sub-sector
                this.formData.subSector = 'Guest';
                // Auto-advance to step 7 after a brief delay
                setTimeout(() => {
                    this.currentStep = 7;
                    window.scrollTo(0, 0);
                }, 300);
            }
        },

        validatePhoneNumber() {
            const phone = this.formData.phoneNumber;

            // Clear any non-digit characters
            this.formData.phoneNumber = phone.replace(/\D/g, '');

            // Country-specific validation rules
            const validationRules = {
                // Palestine & Levant
                'PS': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                'JO': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                'LB': { minLength: 7, maxLength: 8, pattern: /^[0-9]{7,8}$/ },
                'SY': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                'IL': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                // Gulf Countries
                'SA': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                'AE': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                'KW': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'QA': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'BH': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'OM': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                // North Africa
                'EG': { minLength: 10, maxLength: 10, pattern: /^1[0-9]{9}$/ },
                'MA': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                'DZ': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                'TN': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'LY': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                // Other Arab Countries
                'IQ': { minLength: 10, maxLength: 10, pattern: /^7[0-9]{9}$/ },
                'YE': { minLength: 9, maxLength: 9, pattern: /^7[0-9]{8}$/ },
                'SD': { minLength: 9, maxLength: 9, pattern: /^9[0-9]{8}$/ },
                'SO': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                'DJ': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'MR': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                'KM': { minLength: 7, maxLength: 7, pattern: /^[0-9]{7}$/ },
                // Other Countries
                'US': { minLength: 10, maxLength: 10, pattern: /^[0-9]{10}$/ },
                'GB': { minLength: 10, maxLength: 10, pattern: /^[0-9]{10}$/ },
                'DE': { minLength: 10, maxLength: 11, pattern: /^[0-9]{10,11}$/ },
                'FR': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                'TR': { minLength: 10, maxLength: 10, pattern: /^5[0-9]{9}$/ }
            };

            const rule = validationRules[this.formData.phoneCountry] || validationRules['PS'];

            // Validate format
            if (this.formData.phoneNumber.length > 0) {
                if (this.formData.phoneNumber.length < rule.minLength) {
                    this.setError('phoneNumber', `{{ __("Phone number must be at least") }} ${rule.minLength} {{ __("digits") }}`);
                } else if (this.formData.phoneNumber.length > rule.maxLength) {
                    this.formData.phoneNumber = this.formData.phoneNumber.slice(0, rule.maxLength);
                } else if (rule.pattern.test(this.formData.phoneNumber)) {
                    // Valid phone number, clear error
                    if (this.errors.phoneNumber) {
                        delete this.errors.phoneNumber;
                    }
                } else {
                    // Pattern doesn't match
                    if (this.formData.phoneCountry === 'PS') {
                        this.setError('phoneNumber', '{{ __("Phone number must start with 5 and be 9 digits (e.g., 599123456)") }}');
                    } else {
                        this.setError('phoneNumber', '{{ __("Invalid phone number format") }}');
                    }
                }
            }
        },

        getPhonePlaceholder() {
            const placeholders = {
                // Palestine & Levant
                'PS': '599123456',
                'JO': '791234567',
                'LB': '71123456',
                'SY': '911234567',
                'IL': '501234567',
                // Gulf Countries
                'SA': '501234567',
                'AE': '501234567',
                'KW': '50012345',
                'QA': '33123456',
                'BH': '36123456',
                'OM': '92123456',
                // North Africa
                'EG': '1001234567',
                'MA': '612345678',
                'DZ': '551234567',
                'TN': '20123456',
                'LY': '912345678',
                // Other Arab Countries
                'IQ': '7901234567',
                'YE': '712345678',
                'SD': '912345678',
                'SO': '612345678',
                'DJ': '77123456',
                'MR': '22123456',
                'KM': '3212345',
                // Other Countries
                'US': '2025551234',
                'GB': '7700900123',
                'DE': '15112345678',
                'FR': '612345678',
                'TR': '5321234567'
            };
            return placeholders[this.formData.phoneCountry] || '599123456';
        },

        getPhoneHint() {
            const hints = {
                // Palestine & Levant
                'PS': '{{ __("Enter 9 digits starting with 5 (e.g., 599123456)") }}',
                'JO': '{{ __("Enter 9-10 digits (without country code)") }}',
                'LB': '{{ __("Enter 7-8 digits (without country code)") }}',
                'SY': '{{ __("Enter 9-10 digits (without country code)") }}',
                'IL': '{{ __("Enter 9-10 digits (without country code)") }}',
                // Gulf Countries
                'SA': '{{ __("Enter 9 digits starting with 5 (e.g., 501234567)") }}',
                'AE': '{{ __("Enter 9 digits starting with 5 (e.g., 501234567)") }}',
                'KW': '{{ __("Enter 8 digits (without country code)") }}',
                'QA': '{{ __("Enter 8 digits (without country code)") }}',
                'BH': '{{ __("Enter 8 digits (without country code)") }}',
                'OM': '{{ __("Enter 8 digits (without country code)") }}',
                // North Africa
                'EG': '{{ __("Enter 10 digits starting with 1 (e.g., 1001234567)") }}',
                'MA': '{{ __("Enter 9 digits (without country code)") }}',
                'DZ': '{{ __("Enter 9 digits (without country code)") }}',
                'TN': '{{ __("Enter 8 digits (without country code)") }}',
                'LY': '{{ __("Enter 9-10 digits (without country code)") }}',
                // Other Arab Countries
                'IQ': '{{ __("Enter 10 digits starting with 7 (e.g., 7901234567)") }}',
                'YE': '{{ __("Enter 9 digits starting with 7 (e.g., 712345678)") }}',
                'SD': '{{ __("Enter 9 digits starting with 9 (e.g., 912345678)") }}',
                'SO': '{{ __("Enter 9 digits (without country code)") }}',
                'DJ': '{{ __("Enter 8 digits (without country code)") }}',
                'MR': '{{ __("Enter 8 digits (without country code)") }}',
                'KM': '{{ __("Enter 7 digits (without country code)") }}',
                // Other Countries
                'US': '{{ __("Enter 10 digits (without country code)") }}',
                'GB': '{{ __("Enter 10 digits (without country code)") }}',
                'DE': '{{ __("Enter 10-11 digits (without country code)") }}',
                'FR': '{{ __("Enter 9 digits (without country code)") }}',
                'TR': '{{ __("Enter 10 digits starting with 5 (e.g., 5321234567)") }}'
            };
            return hints[this.formData.phoneCountry] || '{{ __("Enter phone number without country code") }}';
        },

        getSubSectors() {
            const sector = this.sectorOptions.find(s => s.value === this.formData.sector);
            return sector ? sector.subSectors : [];
        },

        getSectorLabel() {
            const sector = this.sectorOptions.find(s => s.value === this.formData.sector);
            let label = sector ? sector.label : '';

            // Append "with showroom" for manufacturers who have showroom
            if (this.formData.sector === 'manufacturer' && this.formData.hasShowroom === 'yes') {
                label += ' {{ __("with showroom") }}';
            }

            return label;
        },

        getInitials() {
            const first = this.formData.firstName ? this.formData.firstName[0] : '';
            const last = this.formData.lastName ? this.formData.lastName[0] : '';
            return (first + last).toUpperCase() || 'U';
        },

        availableSkills() {
            return this.skillOptions.filter(skill => !this.formData.skills.includes(skill));
        },

        addSkill() {
            // Get skill from either dropdown or custom input
            let skillToAdd = '';

            if (this.selectedSkill) {
                skillToAdd = this.selectedSkill;
            } else if (this.customSkill && this.customSkill.trim().length > 0) {
                // Validate max length
                const trimmed = this.customSkill.trim();
                if (trimmed.length > 50) {
                    showToast('{{ __("Skill name is too long. Maximum 50 characters allowed.") }}', 'error');
                    return;
                }

                // Trim and capitalize first letter of custom skill
                skillToAdd = trimmed;
                skillToAdd = skillToAdd.charAt(0).toUpperCase() + skillToAdd.slice(1);
            }

            // Case-insensitive duplicate check
            const skillLowerCase = skillToAdd.toLowerCase();
            const isDuplicate = this.formData.skills.some(s => s.toLowerCase() === skillLowerCase);

            // Add skill if it's not empty and not already in the list
            if (skillToAdd && !isDuplicate) {
                this.formData.skills.push(skillToAdd);

                // Clear both inputs
                this.selectedSkill = '';
                this.customSkill = '';

                // Dispatch event to clear the searchable select dropdown
                this.$el.dispatchEvent(new CustomEvent('skill-added', { bubbles: true }));
            } else if (skillToAdd && isDuplicate) {
                showToast('{{ __("This skill has already been added!") }}', 'warning');
            }
        },

        removeSkill(skill) {
            this.formData.skills = this.formData.skills.filter(s => s !== skill);
        },

        // Certification PDF upload handling
        async handleCertificationUpload(event) {
            const files = Array.from(event.target.files);

            // Check max limit
            if (this.formData.certifications.length + files.length > 3) {
                showToast('{{ __("Maximum 3 certification PDFs allowed.") }}', 'error');
                event.target.value = '';
                return;
            }

            for (const file of files) {
                // Validate: PDF only, max 10MB
                if (file.type !== 'application/pdf') {
                    showToast('{{ __("Only PDF files are accepted for certifications.") }}', 'error');
                    continue;
                }
                if (file.size > 10 * 1024 * 1024) {
                    showToast('{{ __("Each PDF must be under 10MB.") }}', 'error');
                    continue;
                }

                const certId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const cert = { id: certId, name: file.name, path: null, uploading: true };
                this.formData.certifications.push(cert);

                // Upload via AJAX
                const uploadedPath = await this.uploadPdf(file);

                const certIndex = this.formData.certifications.findIndex(c => c.id === certId);
                if (certIndex !== -1) {
                    if (uploadedPath) {
                        this.formData.certifications[certIndex].path = uploadedPath;
                        this.formData.certifications[certIndex].uploading = false;
                        this.uploadedPaths.certifications.push(uploadedPath);
                    } else {
                        this.formData.certifications.splice(certIndex, 1);
                    }
                }
            }
            event.target.value = '';
        },

        async uploadPdf(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'certification');
            formData.append('session_id', this.uploadSession);

            const uploadUrl = '{{ route("upload.registration.pdf", ["locale" => app()->getLocale()]) }}';

            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || '{{ __("Upload failed") }}');
                }

                const data = await response.json();
                if (data.success) return data.path;
                throw new Error(data.message || '{{ __("Upload failed") }}');
            } catch (error) {
                showToast('{{ __("Failed to upload certification:") }} ' + error.message, 'error');
                return null;
            }
        },

        removeCertification(certId) {
            const cert = this.formData.certifications.find(c => c.id === certId);
            if (cert && cert.path) {
                this.uploadedPaths.certifications = this.uploadedPaths.certifications.filter(p => p !== cert.path);
            }
            this.formData.certifications = this.formData.certifications.filter(c => c.id !== certId);
        },

        async handleProfileImageChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file BEFORE upload
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                showToast('{{ __("Profile Image Error:") }} ' + validation.error, 'error');
                event.target.value = ''; // Clear the file input
                return;
            }

            // Upload FIRST, then show preview only if upload succeeds
            const uploadedPath = await this.uploadImage(file, 'profile');

            // Only add preview if upload succeeded
            if (uploadedPath) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    this.profileImagePreview = reader.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async handleHeroImageChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file BEFORE upload
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                showToast('{{ __("Cover Image Error:") }} ' + validation.error, 'error');
                event.target.value = ''; // Clear the file input
                return;
            }

            // Upload FIRST, then show preview only if upload succeeds
            const uploadedPath = await this.uploadImage(file, 'cover');

            // Only add preview if upload succeeded
            if (uploadedPath) {
                const reader = new FileReader();
                reader.onloadend = () => {
                    this.heroImagePreview = reader.result;
                };
                reader.readAsDataURL(file);
            }
        },

        // ============================================================
        // PASSWORD STRENGTH METHODS
        // ============================================================

        updatePasswordStrength() {
            const password = this.formData.password;

            // Update individual checks
            this.passwordChecks.length = password.length >= 8;
            this.passwordChecks.uppercase = /[A-Z]/.test(password);
            this.passwordChecks.lowercase = /[a-z]/.test(password);
            this.passwordChecks.number = /[0-9]/.test(password);
            this.passwordChecks.special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            // Calculate strength score (1-4)
            let score = 0;
            if (this.passwordChecks.length) score++;
            if (this.passwordChecks.uppercase) score++;
            if (this.passwordChecks.lowercase) score++;
            if (this.passwordChecks.number) score++;
            if (this.passwordChecks.special) score++;

            // Determine overall score (must have at least 8 chars to count)
            if (!this.passwordChecks.length) {
                this.passwordStrength = { score: 0, label: '' };
            } else if (score <= 2) {
                this.passwordStrength = { score: 1, label: '{{ __("Weak") }}' };
            } else if (score === 3) {
                this.passwordStrength = { score: 2, label: '{{ __("Fair") }}' };
            } else if (score === 4) {
                this.passwordStrength = { score: 3, label: '{{ __("Good") }}' };
            } else if (score === 5) {
                this.passwordStrength = { score: 4, label: '{{ __("Strong") }}' };
            }
        },

        isPasswordStrong() {
            return this.passwordChecks.length &&
                   this.passwordChecks.uppercase &&
                   this.passwordChecks.lowercase &&
                   this.passwordChecks.number &&
                   this.passwordChecks.special;
        },

        // Products
        addProduct() {
            // Limit to maximum 5 products
            if (this.formData.products.length >= 5) {
                showToast('{{ __("Maximum 5 products allowed. Please remove an existing product to add a new one.") }}', 'warning');
                return;
            }

            // Check if there are any incomplete products
            const incompleteProduct = this.formData.products.find(product =>
                !product.name || product.name.trim().length === 0 ||
                !product.description || product.description.trim().length === 0 ||
                !product.category || product.category.trim().length === 0
            );

            if (incompleteProduct) {
                showToast('{{ __("Please fill in all required fields (Name, Description, Category) for the current product before adding a new one.") }}', 'error');
                return;
            }

            this.formData.products.push({
                id: Date.now().toString(),
                name: '',
                description: '',
                category: '',
                customCategory: '',
                images: [], // Array to store multiple image previews
                imagePaths: [] // Array to store uploaded paths
            });
        },

        removeProduct(id) {
            this.formData.products = this.formData.products.filter(p => p.id !== id);
            // Also clean up uploaded path if exists
            if (this.uploadedPaths.products[id]) {
                delete this.uploadedPaths.products[id];
            }
        },

        async handleProductImageChange(productId, event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            const product = this.formData.products.find(p => p.id === productId);
            if (!product) return;

            // Initialize images array if not exists
            if (!product.images) product.images = [];
            if (!product.imagePaths) product.imagePaths = [];

            // Check maximum 6 images limit
            if (product.images.length + files.length > 6) {
                showToast(`{{ __("Maximum 6 images allowed per product. You currently have") }} ${product.images.length} {{ __("image(s). You can only add") }} ${6 - product.images.length} {{ __("more.") }}`, 'warning');
                event.target.value = '';
                return;
            }

            // Set loading state
            product.isUploadingImage = true;

            // Process each file

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Validate file BEFORE upload
                const validation = this.validateImageFile(file);
                if (!validation.valid) {
                    console.error(`❌ Product Image ${i + 1} validation FAILED:`, validation.error);
                    showToast(`{{ __("Product Image") }} ${i + 1} {{ __("Error:") }} ` + validation.error, 'error');
                    continue; // Skip this file but continue with others
                }

                // Generate imageId and upload FIRST, then add preview only if upload succeeds
                const imageId = Date.now().toString() + '_' + i;

                // Upload image to server progressively
                const uploadedPath = await this.uploadImage(file, 'product', productId + '_' + imageId);

                // Only add preview if upload succeeded
                if (uploadedPath) {
                    // Read file and show preview
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        product.images.push({
                            id: imageId,
                            preview: reader.result
                        });
                    };
                    reader.readAsDataURL(file);

                    product.imagePaths.push(uploadedPath);
                } else {
                    console.error(`❌ Product Image ${i + 1} SKIPPED - Upload returned empty path`);
                }
            }

            // Clear loading state
            product.isUploadingImage = false;

            // Clear the input to allow re-uploading
            event.target.value = '';
        },

        removeProductImage(productId, imageId) {
            const product = this.formData.products.find(p => p.id === productId);
            if (!product) return;

            // Remove from images array
            product.images = product.images.filter(img => img.id !== imageId);

            // Remove from uploaded paths
            const imageIndex = product.imagePaths.findIndex(path => path.includes(imageId));
            if (imageIndex !== -1) {
                product.imagePaths.splice(imageIndex, 1);
            }
        },

        // Projects
        addProject() {
            // Limit to maximum 5 projects
            if (this.formData.projects.length >= 5) {
                showToast('{{ __("Maximum 5 projects allowed. Please remove an existing project to add a new one.") }}', 'warning');
                return;
            }

            // Check if there are any incomplete projects
            const incompleteProject = this.formData.projects.find(project =>
                !project.title || project.title.trim().length === 0 ||
                !project.description || project.description.trim().length === 0 ||
                !project.role || project.role.trim().length === 0 ||
                !project.category || project.category.trim().length === 0
            );

            if (incompleteProject) {
                showToast('{{ __("Please fill in all required fields (Title, Category, Description, Role) for the current project before adding a new one.") }}', 'error');
                return;
            }

            this.formData.projects.push({
                id: Date.now().toString(),
                title: '',
                description: '',
                role: '',
                category: '',
                customRole: '',
                customCategory: '',
                images: [], // Array to store multiple image previews
                imagePaths: [] // Array to store uploaded paths
            });
        },

        removeProject(id) {
            this.formData.projects = this.formData.projects.filter(p => p.id !== id);
            // Also clean up uploaded path if exists
            if (this.uploadedPaths.projects[id]) {
                delete this.uploadedPaths.projects[id];
            }
        },

        async handleProjectImageChange(projectId, event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            const project = this.formData.projects.find(p => p.id === projectId);
            if (!project) return;

            // Initialize images array if not exists
            if (!project.images) project.images = [];
            if (!project.imagePaths) project.imagePaths = [];

            // Check maximum 6 images limit
            if (project.images.length + files.length > 6) {
                showToast(`{{ __("Maximum 6 images allowed per project. You currently have") }} ${project.images.length} {{ __("image(s). You can only add") }} ${6 - project.images.length} {{ __("more.") }}`, 'warning');
                event.target.value = '';
                return;
            }

            // Set loading state
            project.isUploadingImage = true;

            // Process each file

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Validate file BEFORE upload
                const validation = this.validateImageFile(file);
                if (!validation.valid) {
                    console.error(`❌ Project Image ${i + 1} validation FAILED:`, validation.error);
                    showToast(`{{ __("Project Image") }} ${i + 1} {{ __("Error:") }} ` + validation.error, 'error');
                    continue; // Skip this file but continue with others
                }

                // Generate imageId and upload FIRST, then add preview only if upload succeeds
                const imageId = Date.now().toString() + '_' + i;

                // Upload image to server progressively
                const uploadedPath = await this.uploadImage(file, 'project', projectId + '_' + imageId);

                // Only add preview if upload succeeded
                if (uploadedPath) {
                    // Read file and show preview
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        project.images.push({
                            id: imageId,
                            preview: reader.result
                        });
                    };
                    reader.readAsDataURL(file);

                    project.imagePaths.push(uploadedPath);
                } else {
                    console.error(`❌ Project Image ${i + 1} SKIPPED - Upload returned empty path`);
                }
            }

            // Clear loading state
            project.isUploadingImage = false;

            // Clear the input to allow re-uploading
            event.target.value = '';
        },

        removeProjectImage(projectId, imageId) {
            const project = this.formData.projects.find(p => p.id === projectId);
            if (!project) return;

            // Remove from images array
            project.images = project.images.filter(img => img.id !== imageId);

            // Remove from uploaded paths
            const imageIndex = project.imagePaths.findIndex(path => path.includes(imageId));
            if (imageIndex !== -1) {
                project.imagePaths.splice(imageIndex, 1);
            }
        },

        // Services
        addService() {
            // Check if there are any incomplete services
            const incompleteService = this.formData.services.find(service =>
                !service.serviceName || service.serviceName.trim().length === 0 ||
                !service.description || service.description.trim().length === 0 ||
                !service.category || service.category.trim().length === 0
            );

            if (incompleteService) {
                showToast('{{ __("Please fill in all required fields (Service Name, Description, Category) for the current service before adding a new one.") }}', 'error');
                return;
            }

            this.formData.services.push({
                id: Date.now().toString(),
                serviceName: '',
                description: '',
                category: '',
                customCategory: '',
                imagePreview: null
            });
        },

        removeService(id) {
            this.formData.services = this.formData.services.filter(s => s.id !== id);
            // Also clean up uploaded path if exists
            if (this.uploadedPaths.services[id]) {
                delete this.uploadedPaths.services[id];
            }
        },


        clearErrors() {
            this.errors = {};
        },

        setError(field, message) {
            this.errors[field] = message;
        },

        async checkEmailAvailability() {
            if (!this.formData.email) {
                this.setError('email', '{{ __("Email is required") }}');
                return false;
            }

            // Basic email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.formData.email)) {
                this.setError('email', '{{ __("Please enter a valid email address") }}');
                return false;
            }

            this.isValidating = true;
            try {
                const response = await fetch('{{ route("validate.email", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ email: this.formData.email })
                });

                const data = await response.json();

                if (!data.available) {
                    this.setError('email', data.message);
                    return false;
                }

                return true;
            } catch (error) {
                // console.error('Email validation error:', error);
                this.setError('email', '{{ __("Could not verify email. Please try again.") }}');
                return false;
            } finally {
                this.isValidating = false;
            }
        },

        async validateStep() {
            this.clearErrors();

            switch (this.currentStep) {
                case 1:
                    // Validate first name
                    if (!this.formData.firstName || this.formData.firstName.trim().length === 0) {
                        this.setError('firstName', '{{ __("First name is required") }}');
                        return false;
                    }

                    // Validate last name
                    if (!this.formData.lastName || this.formData.lastName.trim().length === 0) {
                        this.setError('lastName', '{{ __("Last name is required") }}');
                        return false;
                    }

                    // Validate email
                    if (!this.formData.email || this.formData.email.trim().length === 0) {
                        this.setError('email', '{{ __("Email is required") }}');
                        return false;
                    }

                    // Check email availability (AJAX)
                    const emailAvailable = await this.checkEmailAvailability();
                    if (!emailAvailable) {
                        return false;
                    }

                    // Validate password
                    if (!this.formData.password) {
                        this.setError('password', '{{ __("Password is required") }}');
                        return false;
                    }

                    // Check minimum length
                    if (!this.passwordChecks.length) {
                        this.setError('password', '{{ __("Password must be at least 8 characters") }}');
                        return false;
                    }

                    // Check uppercase letter
                    if (!this.passwordChecks.uppercase) {
                        this.setError('password', '{{ __("Password must contain at least one uppercase letter (A-Z)") }}');
                        return false;
                    }

                    // Check lowercase letter
                    if (!this.passwordChecks.lowercase) {
                        this.setError('password', '{{ __("Password must contain at least one lowercase letter (a-z)") }}');
                        return false;
                    }

                    // Check number
                    if (!this.passwordChecks.number) {
                        this.setError('password', '{{ __("Password must contain at least one number (0-9)") }}');
                        return false;
                    }

                    // Check special character
                    if (!this.passwordChecks.special) {
                        this.setError('password', '{{ __("Password must contain at least one special character (!@#$%^&*)") }}');
                        return false;
                    }

                    // Overall password strength check
                    if (!this.isPasswordStrong()) {
                        this.setError('password', '{{ __("Please create a stronger password with all required criteria") }}');
                        return false;
                    }

                    // Validate confirm password
                    if (!this.formData.confirmPassword) {
                        this.setError('confirmPassword', '{{ __("Please confirm your password") }}');
                        return false;
                    }

                    if (this.formData.password !== this.formData.confirmPassword) {
                        this.setError('confirmPassword', '{{ __("Passwords do not match") }}');
                        return false;
                    }

                    return true;

                case 2:
                    if (!this.formData.sector) {
                        this.setError('sector', '{{ __("Please select your sector") }}');
                        return false;
                    }

                    if (!this.formData.subSector) {
                        this.setError('subSector', '{{ __("Please select your specialization") }}');
                        return false;
                    }

                    return true;

                case 3:
                    // Guest users skip this step (they go directly to step 7)
                    if (this.formData.sector === 'guest') {
                        return true;
                    }

                    // Check if image upload is in progress
                    if (this.uploading) {
                        this.setError('profile_image', '{{ __("Please wait while your image uploads...") }}');
                        return false;
                    }

                    // Check if profile image was successfully uploaded
                    const hasUploadedImage = this.uploadedPaths && this.uploadedPaths.profile;
                    const hasCoverImage = this.uploadedPaths && this.uploadedPaths.cover;

                    if (!hasUploadedImage) {
                        this.setError('profile_image', '{{ __("Profile picture is required. Please upload your profile image.") }}');
                        return false;
                    }
                    if (!hasCoverImage) {
                        this.setError('cover_image', '{{ __("Cover image is required. Please upload your cover image.") }}');
                        return false;
                    }
                   if (!this.formData.companyName || this.formData.companyName.trim().length === 0) {
                     this.setError('companyName', '{{ __("Company/Business name is required") }}');
                     return false;
                    }
                    if (!this.formData.position || this.formData.position.trim().length === 0) {
                        this.setError('position', '{{ __("Position/Title is required") }}');
                        return false;
                    }

                    // Validate phone number with country-specific rules
                    if (!this.formData.phoneNumber || this.formData.phoneNumber.trim().length === 0) {
                        this.setError('phoneNumber', '{{ __("Phone number is required") }}');
                        return false;
                    }

                    // Use country-specific validation rules
                    const validationRules = {
                        'PS': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                        'JO': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                        'LB': { minLength: 7, maxLength: 8, pattern: /^[0-9]{7,8}$/ },
                        'SY': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                        'IL': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                        'SA': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                        'AE': { minLength: 9, maxLength: 9, pattern: /^5[0-9]{8}$/ },
                        'KW': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'QA': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'BH': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'OM': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'EG': { minLength: 10, maxLength: 10, pattern: /^1[0-9]{9}$/ },
                        'MA': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                        'DZ': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                        'TN': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'LY': { minLength: 9, maxLength: 10, pattern: /^[0-9]{9,10}$/ },
                        'IQ': { minLength: 10, maxLength: 10, pattern: /^7[0-9]{9}$/ },
                        'YE': { minLength: 9, maxLength: 9, pattern: /^7[0-9]{8}$/ },
                        'SD': { minLength: 9, maxLength: 9, pattern: /^9[0-9]{8}$/ },
                        'SO': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                        'DJ': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'MR': { minLength: 8, maxLength: 8, pattern: /^[0-9]{8}$/ },
                        'KM': { minLength: 7, maxLength: 7, pattern: /^[0-9]{7}$/ },
                        'US': { minLength: 10, maxLength: 10, pattern: /^[0-9]{10}$/ },
                        'GB': { minLength: 10, maxLength: 10, pattern: /^[0-9]{10}$/ },
                        'DE': { minLength: 10, maxLength: 11, pattern: /^[0-9]{10,11}$/ },
                        'FR': { minLength: 9, maxLength: 9, pattern: /^[0-9]{9}$/ },
                        'TR': { minLength: 10, maxLength: 10, pattern: /^5[0-9]{9}$/ }
                    };

                    const country = this.formData.phoneCountry || 'PS';
                    const rules = validationRules[country];
                    const cleanPhone = this.formData.phoneNumber.replace(/\D/g, '');

                    if (rules) {
                        if (cleanPhone.length < rules.minLength || cleanPhone.length > rules.maxLength) {
                            if (country === 'PS') {
                                this.setError('phoneNumber', '{{ __("Phone number must be exactly 9 digits") }}');
                            } else {
                                this.setError('phoneNumber', `{{ __("Phone number must be between") }} ${rules.minLength} {{ __("and") }} ${rules.maxLength} {{ __("digits") }}`);
                            }
                            return false;
                        }
                        if (!rules.pattern.test(cleanPhone)) {
                            if (country === 'PS') {
                                this.setError('phoneNumber', '{{ __("Phone number must start with 5 and be 9 digits (e.g., 599123456)") }}');
                            } else {
                                this.setError('phoneNumber', '{{ __("Invalid phone number format") }}');
                            }
                            return false;
                        }
                    }

                    // Validate city
                    if (!this.formData.city || this.formData.city.trim().length === 0) {
                        this.setError('city', '{{ __("City/Governorate is required") }}');
                        return false;
                    }

                    // Validate address
                    if (!this.formData.address || this.formData.address.trim().length === 0) {
                        this.setError('address', '{{ __("Address is required") }}');
                        return false;
                    }
                    if (this.formData.address.trim().length < 10) {
                        this.setError('address', '{{ __("Address must be at least 10 characters") }}');
                        return false;
                    }
                    if (this.formData.address.trim().length > 200) {
                        this.setError('address', '{{ __("Address must not exceed 200 characters") }}');
                        return false;
                    }

                    if (!this.formData.yearsOfExperience) {
                        this.setError('yearsOfExperience', '{{ __("Years of experience is required") }}');
                        return false;
                    }

                    if (!this.formData.bio || this.formData.bio.trim().length === 0) {
                        this.setError('bio', '{{ __("Professional bio is required") }}');
                        return false;
                    }

                    if (this.formData.bio.trim().length < 50) {
                        this.setError('bio', '{{ __("Bio must be at least 50 characters") }}');
                        return false;
                    }

                    if (this.formData.bio.trim().length > 500) {
                        this.setError('bio', '{{ __("Bio must not exceed 500 characters") }}');
                        return false;
                    }

                    // Validate certifications for designer sector
                    if (this.formData.sector === 'designer') {
                        const uploadedCerts = this.formData.certifications.filter(c => c.path && !c.uploading);
                        if (uploadedCerts.length === 0) {
                            this.setError('certifications', '{{ __("At least one Education & Certification PDF is required for designers.") }}');
                            return false;
                        }
                        // Check if any certs are still uploading
                        const uploadingCerts = this.formData.certifications.filter(c => c.uploading);
                        if (uploadingCerts.length > 0) {
                            this.setError('certifications', '{{ __("Please wait while your certifications finish uploading.") }}');
                            return false;
                        }
                    }

                    return true;

                case 4:
                    // Step 4: Products - validate categories are from the allowed list
                    const productCategories = @json(\App\Helpers\DropdownHelper::productCategories());
                    for (let i = 0; i < this.formData.products.length; i++) {
                        const product = this.formData.products[i];
                        // If product has ANY data, category is REQUIRED
                        if (product.name || product.description) {
                            // Check if category is missing
                            if (!product.category || !product.category.trim()) {
                                this.setError('products', `{{ __("Product") }} ${i + 1}: {{ __("Category is required. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const productElements = document.querySelectorAll('[x-data*="searchableSelectProductCategory"]');
                                    if (productElements[i]) {
                                        productElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                            // Check if category is invalid
                            if (!productCategories.includes(product.category.trim())) {
                                this.setError('products', `{{ __("Product") }} ${i + 1}: {{ __("Category") }} "${product.category}" {{ __("is not valid. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const productElements = document.querySelectorAll('[x-data*="searchableSelectProductCategory"]');
                                    if (productElements[i]) {
                                        productElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                        }
                    }
                    return true;

                case 5:
                    // Step 5: Projects - validate categories and roles are from the allowed list
                    const projectCategories = @json(\App\Helpers\DropdownHelper::projectCategories());
                    const projectRoles = @json(\App\Helpers\DropdownHelper::projectRoles());
                    for (let i = 0; i < this.formData.projects.length; i++) {
                        const project = this.formData.projects[i];
                        // If project has ANY data, category and role are REQUIRED
                        if (project.title || project.description) {
                            // Check if category is missing
                            if (!project.category || !project.category.trim()) {
                                this.setError('projects', `{{ __("Project") }} ${i + 1}: {{ __("Category is required. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const categoryElements = document.querySelectorAll('[x-data*="searchableSelectProjectCategory"]');
                                    if (categoryElements[i]) {
                                        categoryElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                            // Check if category is invalid
                            if (!projectCategories.includes(project.category.trim())) {
                                this.setError('projects', `{{ __("Project") }} ${i + 1}: {{ __("Category") }} "${project.category}" {{ __("is not valid. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const categoryElements = document.querySelectorAll('[x-data*="searchableSelectProjectCategory"]');
                                    if (categoryElements[i]) {
                                        categoryElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                            // Check if role is missing
                            if (!project.role || !project.role.trim()) {
                                this.setError('projects', `{{ __("Project") }} ${i + 1}: {{ __("Role is required. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const roleElements = document.querySelectorAll('[x-data*="searchableSelectProjectRole"]');
                                    if (roleElements[i]) {
                                        roleElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                            // Check if role is invalid
                            if (!projectRoles.includes(project.role.trim())) {
                                this.setError('projects', `{{ __("Project") }} ${i + 1}: {{ __("Role") }} "${project.role}" {{ __("is not valid. Please select from the dropdown list.") }}`);
                                setTimeout(() => {
                                    const roleElements = document.querySelectorAll('[x-data*="searchableSelectProjectRole"]');
                                    if (roleElements[i]) {
                                        roleElements[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                }, 100);
                                return false;
                            }
                        }
                    }
                    return true;

                case 6:
                    // Step 6: Services - validate categories are from the allowed list
                    // Use the same dynamic list from DropdownHelper (locale-aware)
                    const serviceCategories = @json(\App\Helpers\DropdownHelper::serviceCategories());
                    for (let i = 0; i < this.formData.services.length; i++) {
                        const service = this.formData.services[i];
                        // If service has ANY data, category is REQUIRED
                        if (service.serviceName || service.description) {
                            // Check if category is missing
                            if (!service.category || !service.category.trim()) {
                                this.setError('services', `{{ __("Service") }} ${i + 1}: {{ __("Category is required. Please select from the dropdown list.") }}`);
                                return false;
                            }
                            // Check if category is invalid
                            if (!serviceCategories.includes(service.category.trim())) {
                                this.setError('services', `{{ __("Service") }} ${i + 1}: {{ __("Category") }} "${service.category}" {{ __("is not valid. Please select from the dropdown list.") }}`);
                                return false;
                            }
                        }
                    }
                    return true;

                case 7:
                    // Step 7 is optional, no validation needed
                    return true;

                default:
                    return true;
            }
        },

        async nextStep() {
            // Prevent concurrent navigation
            if (this.isNavigating) return;

            this.isNavigating = true;
            try {
                const isValid = await this.validateStep();
                if (isValid && this.currentStep < 7) {
                    // Mark current step as completed
                    if (!this.completedSteps.includes(this.currentStep)) {
                        this.completedSteps.push(this.currentStep);
                    }

                    // Clean up empty items before leaving steps 4, 5, or 6
                    if (this.currentStep === 4 || this.currentStep === 5 || this.currentStep === 6) {
                        this.cleanupEmptyItems();
                    }

                    this.currentStep++;

                    // Focus management after step change
                    setTimeout(() => {
                        const firstInput = document.querySelector('input:not([type="hidden"]), textarea, select');
                        if (firstInput) firstInput.focus();
                    }, 100);

                    window.scrollTo(0, 0);
                    this.saveToLocalStorage();
                }
            } finally {
                this.isNavigating = false;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                // Guest users go back from Review (step 7) to Profile Type (step 2)
                if (this.formData.sector === 'guest' && this.currentStep === 7) {
                    // Guest users skip steps 4-6 (Products, Projects, Services)
                    // Jump directly from Review (7) to Profile Type (2)
                    this.currentStep = 2;

                    // Clear sector and sub-sector so they can choose something else
                    this.formData.sector = '';
                    this.formData.subSector = '';
                    this.formData.hasShowroom = '';

                    // Dispatch event to clear the combobox displays
                    this.$nextTick(() => {
                        this.$el.dispatchEvent(new CustomEvent('sector-cleared', { bubbles: true }));
                    });

                    this.clearErrors();
                    window.scrollTo(0, 0);
                    this.saveToLocalStorage();
                    return;
                }

                // Clean up empty items before going back from Review step
                if (this.currentStep === 7) {
                    this.cleanupEmptyItems();
                }
                this.currentStep--;
                this.clearErrors(); // Clear errors when going back
                window.scrollTo(0, 0);
                this.saveToLocalStorage();
            }
        },

        async handleSubmit() {
            // Check if any uploads are in progress
            if (this.uploading) {
                showToast('{{ __("Please wait while your images finish uploading...") }}', 'warning');
                return;
            }

            // Filter out empty products/projects/services before submitting
            this.cleanupEmptyItems();

            // Final check: Ensure profile and cover images were uploaded (not required for guests)
            if (this.formData.sector !== 'guest') {
                if (!this.uploadedPaths.profile) {
                    this.setError('profile_image', '{{ __("Profile picture is required. Please upload your profile image.") }}');
                    this.currentStep = 3; // Go back to step 3 to upload image
                    window.scrollTo(0, 0);
                    return;
                }
                if (!this.uploadedPaths.cover) {
                    this.setError('cover_image', '{{ __("Cover image is required. Please upload your cover image.") }}');
                    this.currentStep = 3; // Go back to step 3 to upload image
                    window.scrollTo(0, 0);
                    return;
                }
            }

            // Check Terms & Privacy acceptance
            if (!this.termsAccepted) {
                this.errors.terms = true;
                showToast('{{ __("Please accept the Terms of Service & Privacy Policy to continue.") }}', 'warning');
                return;
            }

            const isValid = await this.validateStep();

            if (isValid) {
                // Show confirmation modal instead of submitting directly
                this.showPublishConfirmModal = true;
                this.mediaOwnershipConfirmed = false;
                this.policiesConfirmed = false;
            }
        },

        // Proceed with actual form submission after confirmation
        proceedWithPublish() {
            // Check if both checkboxes are confirmed
            if (!this.mediaOwnershipConfirmed || !this.policiesConfirmed) {
                showToast('{{ __("Please confirm both checkboxes to continue") }}', 'warning');
                return;
            }

            // Close the modal
            this.showPublishConfirmModal = false;

            // Add uploaded paths as hidden inputs to the form
            this.addUploadedPathsToForm();

            // Clear localStorage before submitting (success)
            this.clearLocalStorage();

            // Clear upload session after successful submit
            localStorage.removeItem('uploadSessionId');

            // Submit the form
            document.getElementById('registrationForm').submit();
        },

        // Check if both confirmations are valid
        canPublish() {
            return this.mediaOwnershipConfirmed && this.policiesConfirmed;
        },

        addUploadedPathsToForm() {
            const form = document.getElementById('registrationForm');

            // Add profile image path
            if (this.uploadedPaths.profile) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'profile_image_path';
                input.value = this.uploadedPaths.profile;
                form.appendChild(input);
            }

            // Add cover image path
            if (this.uploadedPaths.cover) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cover_image_path';
                input.value = this.uploadedPaths.cover;
                form.appendChild(input);
            }

            // Add product image paths (multiple images support)
            this.formData.products.forEach((product, index) => {
                // Legacy single image path (deprecated but kept for backward compatibility)
                if (this.uploadedPaths.products[product.id]) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `products[${index}][image_path]`;
                    input.value = this.uploadedPaths.products[product.id];
                    form.appendChild(input);
                }

                // Submit multiple image paths array
                if (product.imagePaths && Array.isArray(product.imagePaths) && product.imagePaths.length > 0) {
                    product.imagePaths.forEach((imagePath, imgIndex) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `products[${index}][image_paths][${imgIndex}]`;
                        input.value = imagePath;
                        form.appendChild(input);
                    });
                }
            });

            // Add project image paths (multiple images support)
            this.formData.projects.forEach((project, index) => {
                // Legacy single image path (deprecated but kept for backward compatibility)
                if (this.uploadedPaths.projects[project.id]) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `projects[${index}][image_path]`;
                    input.value = this.uploadedPaths.projects[project.id];
                    form.appendChild(input);
                }

                // Submit multiple image paths array
                if (project.imagePaths && Array.isArray(project.imagePaths) && project.imagePaths.length > 0) {
                    project.imagePaths.forEach((imagePath, imgIndex) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `projects[${index}][image_paths][${imgIndex}]`;
                        input.value = imagePath;
                        form.appendChild(input);
                    });
                }
            });

            // Services don't use images, so no image paths to add

            // Add certification paths
            this.uploadedPaths.certifications.forEach((certPath, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `certification_paths[${index}]`;
                input.value = certPath;
                form.appendChild(input);
            });

            // Add upload session ID for backend to link images
            const sessionInput = document.createElement('input');
            sessionInput.type = 'hidden';
            sessionInput.name = 'upload_session_id';
            sessionInput.value = this.uploadSession;
            form.appendChild(sessionInput);
        },

        cleanupEmptyItems() {
            // Store IDs of items to keep
            const keepProductIds = new Set();
            const keepProjectIds = new Set();
            const keepServiceIds = new Set();

            // Remove products with empty required fields
            this.formData.products = this.formData.products.filter(product => {
                const keep = product.name && product.name.trim().length > 0 &&
                    product.description && product.description.trim().length > 0 &&
                    product.category && product.category.trim().length > 0;
                if (keep) keepProductIds.add(product.id);
                return keep;
            });

            // Remove projects with empty required fields
            this.formData.projects = this.formData.projects.filter(project => {
                const keep = project.title && project.title.trim().length > 0 &&
                    project.description && project.description.trim().length > 0 &&
                    project.role && project.role.trim().length > 0;
                if (keep) keepProjectIds.add(project.id);
                return keep;
            });

            // Remove services with empty required fields
            this.formData.services = this.formData.services.filter(service => {
                const keep = service.serviceName && service.serviceName.trim().length > 0 &&
                    service.description && service.description.trim().length > 0 &&
                    service.category && service.category.trim().length > 0;
                if (keep) keepServiceIds.add(service.id);
                return keep;
            });

            // Clean up uploadedPaths for removed items
            Object.keys(this.uploadedPaths.products).forEach(id => {
                if (!keepProductIds.has(id)) {
                    delete this.uploadedPaths.products[id];
                }
            });

            Object.keys(this.uploadedPaths.projects).forEach(id => {
                if (!keepProjectIds.has(id)) {
                    delete this.uploadedPaths.projects[id];
                }
            });

            Object.keys(this.uploadedPaths.services).forEach(id => {
                if (!keepServiceIds.has(id)) {
                    delete this.uploadedPaths.services[id];
                }
            });
        },

        // ============================================================
        // LOCALSTORAGE & PERSISTENCE METHODS
        // ============================================================

        scheduleAutoSave() {
            // Optimize debouncing (wait 2 seconds to reduce saves)
            if (this.autoSaveTimer) {
                clearTimeout(this.autoSaveTimer);
            }
            this.autoSaveTimer = setTimeout(() => {
                this.saveToLocalStorage();
            }, 2000);
        },

        saveToLocalStorage() {
            try {
                // Create a clean copy of formData without image previews (they're too large for localStorage)
                const cleanFormData = {
                    firstName: this.formData.firstName,
                    lastName: this.formData.lastName,
                    email: this.formData.email,
                    password: this.formData.password,
                    confirmPassword: this.formData.confirmPassword,
                    sector: this.formData.sector,
                    subSector: this.formData.subSector,
                    hasShowroom: this.formData.hasShowroom,
                    companyName: this.formData.companyName,
                    position: this.formData.position,
                    phoneNumber: this.formData.phoneNumber,
                    phoneCountry: this.formData.phoneCountry,
                    city: this.formData.city,
                    address: this.formData.address,
                    yearsOfExperience: this.formData.yearsOfExperience,
                    bio: this.formData.bio,
                    skills: [...this.formData.skills],
                    // Save only text data for products/projects (services don't use images)
                    products: this.formData.products.map(p => ({
                        id: p.id,
                        name: p.name,
                        description: p.description,
                        category: p.category
                    })),
                    projects: this.formData.projects.map(p => ({
                        id: p.id,
                        title: p.title,
                        description: p.description,
                        role: p.role
                    })),
                    services: this.formData.services.map(s => ({
                        id: s.id,
                        serviceName: s.serviceName,
                        description: s.description,
                        category: s.category
                    }))
                };

                const dataToSave = {
                    currentStep: this.currentStep,
                    completedSteps: [...this.completedSteps],
                    formData: cleanFormData,
                    uploadedPaths: this.uploadedPaths, // Save uploaded file paths
                    timestamp: new Date().toISOString()
                };
                localStorage.setItem('signupWizardData', JSON.stringify(dataToSave));
            } catch (e) {
                // Handle localStorage quota exceeded
                if (e.name === 'QuotaExceededError' || e.code === 22) {

                    // Try to clear old cached data and retry
                    try {
                        localStorage.removeItem('signupWizardData');
                        const dataToSave = {
                            currentStep: this.currentStep,
                            completedSteps: [...this.completedSteps],
                            formData: cleanFormData,
                            uploadedPaths: this.uploadedPaths,
                            timestamp: new Date().toISOString()
                        };
                        localStorage.setItem('signupWizardData', JSON.stringify(dataToSave));
                    } catch (retryError) {
                        console.error('❌ Still failed after clearing. Auto-save disabled.');
                        showToast('{{ __("Warning: Auto-save is disabled due to browser storage limits. Please complete registration without refreshing.") }}', 'warning');
                    }
                } else {
                    console.error('Failed to save to localStorage:', e);
                }
            }
        },

        loadFromLocalStorage() {
            try {
                const saved = localStorage.getItem('signupWizardData');
                if (saved) {
                    const data = JSON.parse(saved);

                    // Check if data is not too old (10 minutes)
                    const savedTime = new Date(data.timestamp);
                    const now = new Date();
                    const minutesDiff = (now - savedTime) / 1000 / 60;

                    if (minutesDiff < 10) {
                        // Restore step and completion data
                        this.currentStep = data.currentStep || 1;
                        this.completedSteps = Array.isArray(data.completedSteps) ? [...data.completedSteps] : [];

                        // Restore formData with proper deep copy
                        if (data.formData) {
                            // Use setTimeout to ensure AlpineJS detects changes
                            setTimeout(() => {
                                this.formData.firstName = data.formData.firstName || '';
                                this.formData.lastName = data.formData.lastName || '';
                                this.formData.email = data.formData.email || '';
                                this.formData.password = data.formData.password || '';
                                this.formData.confirmPassword = data.formData.confirmPassword || '';
                                this.formData.sector = data.formData.sector || '';
                                this.formData.subSector = data.formData.subSector || '';
                                this.formData.hasShowroom = data.formData.hasShowroom || '';
                                this.formData.companyName = data.formData.companyName || '';
                                this.formData.position = data.formData.position || '';
                                this.formData.phoneNumber = data.formData.phoneNumber || '';
                                this.formData.phoneCountry = data.formData.phoneCountry || 'PS';
                                this.formData.city = data.formData.city || '';
                                this.formData.address = data.formData.address || '';
                                this.formData.yearsOfExperience = data.formData.yearsOfExperience || '';
                                this.formData.bio = data.formData.bio || '';
                                this.formData.skills = Array.isArray(data.formData.skills) ? [...data.formData.skills] : [];

                                // Restore products with imagePreview property initialized
                                this.formData.products = Array.isArray(data.formData.products)
                                    ? data.formData.products.map(p => ({
                                        id: p.id,
                                        name: p.name || '',
                                        description: p.description || '',
                                        category: p.category || '',
                                        imagePreview: null
                                    }))
                                    : [];

                                // Restore projects with imagePreview property initialized
                                this.formData.projects = Array.isArray(data.formData.projects)
                                    ? data.formData.projects.map(p => ({
                                        id: p.id,
                                        title: p.title || '',
                                        description: p.description || '',
                                        role: p.role || '',
                                        imagePreview: null
                                    }))
                                    : [];

                                // Restore services (services don't use images)
                                this.formData.services = Array.isArray(data.formData.services)
                                    ? data.formData.services.map(s => ({
                                        id: s.id,
                                        serviceName: s.serviceName || '',
                                        description: s.description || '',
                                        category: s.category || ''
                                    }))
                                    : [];
                            }, 50);
                        }

                        // Restore uploaded paths
                        if (data.uploadedPaths) {
                            this.uploadedPaths = {
                                profile: data.uploadedPaths.profile || null,
                                products: data.uploadedPaths.products || {},
                                projects: data.uploadedPaths.projects || {},
                                services: data.uploadedPaths.services || {}
                            };

                            // Restore image previews from uploaded paths
                            this.restoreImagePreviews();

                            // Show indicator that images are already uploaded
                            // if (this.uploadedPaths.profile) {
                            // }
                            // if (Object.keys(this.uploadedPaths.products).length > 0) {
                            // }
                            // if (Object.keys(this.uploadedPaths.projects).length > 0) {
                            // }
                            // if (Object.keys(this.uploadedPaths.services).length > 0) {
                            // }
                        }

                        // Log what was restored for debugging
                        //     step: this.currentStep,
                        //     fieldsRestored: [
                        //         this.formData.firstName ? 'firstName' : null,
                        //         this.formData.lastName ? 'lastName' : null,
                        //         this.formData.email ? 'email' : null,
                        //         this.formData.password ? 'password' : null,
                        //         this.formData.sector ? 'sector' : null,
                        //         this.formData.subSector ? 'subSector' : null,
                        //         this.formData.companyName ? 'companyName' : null,
                        //         this.formData.position ? 'position' : null,
                        //         this.formData.yearsOfExperience ? 'yearsOfExperience' : null,
                        //         this.formData.bio ? 'bio' : null,
                        //         this.formData.skills.length > 0 ? `skills(${this.formData.skills.length})` : null
                        //     ].filter(f => f !== null),
                        //     products: this.formData.products.length,
                        //     projects: this.formData.projects.length,
                        //     services: this.formData.services.length,
                        //     uploadedImages: {
                        //         profile: !!this.uploadedPaths.profile,
                        //         products: Object.keys(this.uploadedPaths.products).length,
                        //         projects: Object.keys(this.uploadedPaths.projects).length,
                        //         services: Object.keys(this.uploadedPaths.services).length
                        //     }
                        // });

                        // Recalculate password strength if password was loaded
                        if (this.formData.password) {
                            this.updatePasswordStrength();
                        }

                        // Auto-restore without asking - better UX
                        if (this.currentStep > 1) {
                        }
                    } else {
                        // Data too old, clear it
                        this.clearLocalStorage();
                    }
                }
            } catch (e) {
                console.error('Failed to load from localStorage:', e);
                this.clearLocalStorage();
            }
        },

        resetForm() {
            this.clearLocalStorage();
            this.currentStep = 1;
            this.completedSteps = [];
            this.formData = {
                firstName: '',
                lastName: '',
                email: '',
                password: '',
                confirmPassword: '',
                sector: '',
                subSector: '',
                companyName: '',
                position: '',
                yearsOfExperience: '',
                bio: '',
                skills: [],
                products: [],
                projects: [],
                services: []
            };
            this.profileImagePreview = null;
            this.errors = {};

            // Reset upload-related state
            this.uploadedPaths = {
                profile: null,
                products: {},
                projects: {},
                services: {}
            };
            this.uploadProgress = {};
            this.uploading = false;

            // Generate new upload session
            this.uploadSession = this.getOrCreateUploadSession();
        },

        clearLocalStorage() {
            try {
                localStorage.removeItem('signupWizardData');
                localStorage.removeItem('uploadSessionId'); // Also clear upload session
            } catch (e) {
                console.error('Failed to clear localStorage:', e);
            }
        },

        restoreImagePreviews() {
            // Restore profile image preview
            if (this.uploadedPaths.profile) {
                // Use direct storage path to access uploaded temp files
                this.profileImagePreview = '{{ asset("") }}storage/app/public/' + this.uploadedPaths.profile;
            }

            // Restore cover image preview
            if (this.uploadedPaths.cover) {
                this.heroImagePreview = '{{ asset("") }}storage/app/public/' + this.uploadedPaths.cover;
            }

            // Restore product image previews
            this.formData.products.forEach(product => {
                if (this.uploadedPaths.products[product.id]) {
                    product.imagePreview = '{{ asset("") }}storage/app/public/' + this.uploadedPaths.products[product.id];
                }
            });

            // Restore project image previews
            this.formData.projects.forEach(project => {
                if (this.uploadedPaths.projects[project.id]) {
                    project.imagePreview = '{{ asset("") }}storage/app/public/' + this.uploadedPaths.projects[project.id];
                }
            });

            // Services don't use images, so no image previews to restore

        },

        // ============================================================
        // STEP NAVIGATION METHODS
        // ============================================================

        async goToStep(targetStep) {
            // Can only go back to completed steps
            if (targetStep > this.currentStep) {
                showToast('{{ __("Please complete the current step first before moving forward.") }}', 'warning');
                return;
            }

            // Clean up empty items when leaving product/project/service steps
            if (this.currentStep === 4 || this.currentStep === 5 || this.currentStep === 6) {
                this.cleanupEmptyItems();
            }

            // Clear errors and go to step
            this.clearErrors();
            this.currentStep = targetStep;
            window.scrollTo(0, 0);
            this.saveToLocalStorage();
        }
    }
}

// Searchable Select Component
function searchableSelect(config) {
    return {
        options: config.options || [],
        selectedValue: config.value || '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,
        placeholder: config.placeholder || '{{ __("Select an option") }}',

        init() {
            this.$nextTick(() => {
                // Find the wizard component using DOM traversal
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData && config.name) {
                            this.selectedValue = wizardData.formData[config.name] || config.value || '';
                            this.searchQuery = this.selectedValue;
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            // If search query matches the current selection, show all options
            if (query === currentValue) {
                return this.options;
            }

            if (!query) {
                return this.options;
            }
            return this.options.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            // Update parent formData using DOM traversal
            if (config.name) {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            wizardData.formData[config.name] = option;
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Sector (Profile Type) - Uses database options
function searchableSelectSectorWithOptions() {
    const sectorOptions = @json(\App\Helpers\DropdownHelper::sectorOptions());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                // Find the wizard component
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            this.selectedValue = wizardData.formData.sector || '';
                            // Initialize searchQuery with selected label for display
                            this.searchQuery = this.selectedLabel;
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });

            // Listen for sector-cleared event (when guest goes back and wants to change)
            this.$el.addEventListener('sector-cleared', () => {
                this.selectedValue = '';
                this.searchQuery = '';
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentLabel = this.selectedLabel.toLowerCase();

            // If search query matches the current selection, show all options
            if (query === currentLabel) {
                return sectorOptions;
            }

            if (!query) {
                return sectorOptions;
            }
            return sectorOptions.filter(option =>
                option.label.toLowerCase().includes(query)
            );
        },

        get selectedLabel() {
            const selected = sectorOptions.find(opt => opt.value === this.selectedValue);
            return selected ? selected.label : '';
        },

        selectOption(option) {
            this.selectedValue = option.value;

            // Find the wizard component by traversing up the DOM
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.handleSectorChange) {
                        wizardData.formData.sector = option.value;
                        wizardData.handleSectorChange();
                        break;
                    }
                }
                element = element.parentElement;
            }

            // Set searchQuery to the selected label to show it in black
            this.searchQuery = option.label;
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// OLD - Searchable Select for Sector (Profile Type)
function searchableSelectSector() {
    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                const root = this.getRoot();
                if (root && root.formData) {
                    this.selectedValue = root.formData.sector || '';
                    this.$watch(() => this.getRoot()?.formData?.sector, (value) => {
                        if (value !== this.selectedValue) {
                            this.selectedValue = value;
                            this.searchQuery = '';
                        }
                    });
                }
            });
        },

        getRoot() {
            let current = this.$root;
            // Try $root first, fallback to $parent
            return current || this.$parent;
        },

        get filteredOptions() {
            // Try to get options from sectorOptionsData (defined in template) or fall back to root
            const options = this.sectorOptionsData || (this.getRoot() && this.getRoot().sectorOptions) || [];
            const query = this.searchQuery.toLowerCase();
            if (!query) {
                return options;
            }
            return options.filter(option =>
                option.label.toLowerCase().includes(query)
            );
        },

        get selectedLabel() {
            const options = this.sectorOptionsData || (this.getRoot() && this.getRoot().sectorOptions) || [];
            const selected = options.find(opt => opt.value === this.selectedValue);
            return selected ? selected.label : '';
        },

        selectOption(option) {
            this.selectedValue = option.value;
            const root = this.getRoot();
            if (root && root.formData) {
                root.formData.sector = option.value;
                if (root.handleSectorChange) {
                    root.handleSectorChange();
                }
            }
            this.searchQuery = '';
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for SubSector (Specialization) - Uses database options
function searchableSelectSubSector() {
    const subSectorsByType = @json(\App\Helpers\DropdownHelper::subsectorsByType());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                // Find the wizard component
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            this.selectedValue = wizardData.formData.subSector || '';
                            this.searchQuery = this.selectedValue;

                            // Watch for sector changes and clear sub-sector
                            this.$watch(() => {
                                let el = this.$el;
                                while (el) {
                                    if (el._x_dataStack && el._x_dataStack.length > 0) {
                                        const wd = el._x_dataStack[0];
                                        if (wd && wd.formData) {
                                            return wd.formData.sector;
                                        }
                                    }
                                    el = el.parentElement;
                                }
                                return null;
                            }, (newSector, oldSector) => {
                                // When sector changes, clear the sub-sector selection
                                if (oldSector && newSector !== oldSector) {
                                    this.searchQuery = '';
                                    this.selectedValue = '';
                                }
                            });
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });

            // Listen for sector changes to clear sub-sector display
            this.$el.addEventListener('sector-changed', () => {
                this.searchQuery = '';
                this.selectedValue = '';

                // Also clear the wizard's formData.subSector
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            wizardData.formData.subSector = '';
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });

            // Listen for sector-cleared event (when guest goes back and wants to change)
            this.$el.addEventListener('sector-cleared', () => {
                this.searchQuery = '';
                this.selectedValue = '';
            });
        },

        getSubSectorOptions() {
            // Find current sector from wizard
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.sector) {
                        return subSectorsByType[wizardData.formData.sector] || [];
                    }
                }
                element = element.parentElement;
            }
            return [];
        },

        get filteredOptions() {
            const options = this.getSubSectorOptions();
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            // If search query matches the current selection, show all options
            if (query === currentValue) {
                return options;
            }

            if (!query) {
                return options;
            }
            return options.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;

            // Find the wizard component
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData) {
                        wizardData.formData.subSector = option;
                        break;
                    }
                }
                element = element.parentElement;
            }

            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for City - Uses database options
function searchableSelectCity() {
    const cityOptions = @json(\App\Helpers\DropdownHelper::cities());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                // Find the wizard component
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            this.selectedValue = wizardData.formData.city || '';
                            this.searchQuery = this.selectedValue;
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            // If search query matches the current selection, show all options
            if (query === currentValue) {
                return cityOptions;
            }

            if (!query) {
                return cityOptions;
            }
            return cityOptions.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;

            // Find the wizard component
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData) {
                        wizardData.formData.city = option;
                        break;
                    }
                }
                element = element.parentElement;
            }

            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Years of Experience - Uses database options
function searchableSelectYearsOfExperience() {
    const experienceOptions = @json(\App\Helpers\DropdownHelper::yearsOfExperience());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                // Find the wizard component
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData) {
                            this.selectedValue = wizardData.formData.yearsOfExperience || '';
                            this.searchQuery = this.selectedValue;
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            // If search query matches the current selection, show all options
            if (query === currentValue) {
                return experienceOptions;
            }

            if (!query) {
                return experienceOptions;
            }
            return experienceOptions.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;

            // Find the wizard component
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData) {
                        wizardData.formData.yearsOfExperience = option;
                        break;
                    }
                }
                element = element.parentElement;
            }

            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Skills - Uses database options
function searchableSelectSkills() {
    const allSkills = @json(\App\Helpers\DropdownHelper::skills());

    return {
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            // Listen for skill-added event to clear the search
            this.$el.addEventListener('skill-added', () => {
                this.searchQuery = '';
                this.isOpen = false;
                this.highlightedIndex = -1;
            });

            // Watch for when parent's selectedSkill is cleared and clear searchQuery
            this.$nextTick(() => {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData && wizardData.hasOwnProperty('selectedSkill')) {
                            // Watch selectedSkill and clear searchQuery when it's emptied
                            this.$watch(() => {
                                let el = this.$el;
                                while (el) {
                                    if (el._x_dataStack && el._x_dataStack.length > 0) {
                                        const wd = el._x_dataStack[0];
                                        if (wd && wd.hasOwnProperty('selectedSkill')) {
                                            return wd.selectedSkill;
                                        }
                                    }
                                    el = el.parentElement;
                                }
                                return null;
                            }, (value) => {
                                if (value === '') {
                                    this.searchQuery = '';
                                }
                            });
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        getAvailableSkills() {
            // Get already selected skills from wizard
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.skills) {
                        const selected = wizardData.formData.skills;
                        return allSkills.filter(skill => !selected.includes(skill));
                    }
                }
                element = element.parentElement;
            }
            return allSkills;
        },

        get filteredOptions() {
            const available = this.getAvailableSkills();
            const query = this.searchQuery.toLowerCase();
            if (!query) {
                return available;
            }
            return available.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            // Find the wizard component and set selectedSkill
            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData && wizardData.formData && wizardData.hasOwnProperty('selectedSkill')) {
                        wizardData.selectedSkill = option;
                        wizardData.customSkill = '';
                        break;
                    }
                }
                element = element.parentElement;
            }

            // Show the selected skill in the search field
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            const filtered = this.filteredOptions;
            if (this.highlightedIndex >= 0 && this.highlightedIndex < filtered.length) {
                this.selectOption(filtered[this.highlightedIndex]);
            } else if (this.searchQuery) {
                // If no option is highlighted but there's text, set it as custom skill
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData) {
                            wizardData.customSkill = this.searchQuery;
                            wizardData.selectedSkill = '';
                            break;
                        }
                    }
                    element = element.parentElement;
                }
                this.searchQuery = '';
                this.isOpen = false;
                this.highlightedIndex = -1;
            }
        }
    };
}

// Searchable Select for Product Category
function searchableSelectProductCategory(productId) {
    const categories = @json(\App\Helpers\DropdownHelper::productCategories());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData && wizardData.formData.products) {
                            const product = wizardData.formData.products.find(p => p.id === productId);
                            if (product) {
                                this.selectedValue = product.category || '';
                                this.searchQuery = this.selectedValue;
                            }
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            if (query === currentValue) {
                return categories;
            }

            if (!query) {
                return categories;
            }
            return categories.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.products) {
                        const product = wizardData.formData.products.find(p => p.id === productId);
                        if (product) {
                            product.category = option;
                            if (option !== 'Other') {
                                product.customCategory = '';
                            }
                        }
                        // Clear products error when a valid category is selected
                        if (wizardData.clearError) {
                            wizardData.clearError('products');
                        }
                        break;
                    }
                }
                element = element.parentElement;
            }
        },

        validateAndUpdateCategory() {
            // Check if searchQuery matches a valid category
            const matchedCategory = categories.find(cat => cat.toLowerCase() === this.searchQuery.trim().toLowerCase());

            if (matchedCategory) {
                // Valid category - update it
                this.selectOption(matchedCategory);
            } else if (this.searchQuery.trim() && !this.selectedValue) {
                // Invalid text entered and no valid selection - clear it
                this.searchQuery = '';
            } else {
                // Restore to selected value
                this.searchQuery = this.selectedValue;
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Project Category
function searchableSelectProjectCategory(projectId) {
    const categories = @json(\App\Helpers\DropdownHelper::projectCategories());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData && wizardData.formData.projects) {
                            const project = wizardData.formData.projects.find(p => p.id === projectId);
                            if (project) {
                                this.selectedValue = project.category || '';
                                this.searchQuery = this.selectedValue;
                            }
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            if (query === currentValue) {
                return categories;
            }

            if (!query) {
                return categories;
            }
            return categories.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.projects) {
                        const project = wizardData.formData.projects.find(p => p.id === projectId);
                        if (project) {
                            project.category = option;
                            if (option !== 'Other') {
                                project.customCategory = '';
                            }
                        }
                        // Clear projects error when a valid category is selected
                        if (wizardData.clearError) {
                            wizardData.clearError('projects');
                        }
                        break;
                    }
                }
                element = element.parentElement;
            }
        },

        validateAndUpdateCategory() {
            // Check if searchQuery matches a valid category
            const matchedCategory = categories.find(cat => cat.toLowerCase() === this.searchQuery.trim().toLowerCase());

            if (matchedCategory) {
                // Valid category - update it
                this.selectOption(matchedCategory);
            } else if (this.searchQuery.trim() && !this.selectedValue) {
                // Invalid text entered and no valid selection - clear it
                this.searchQuery = '';
            } else {
                // Restore to selected value
                this.searchQuery = this.selectedValue;
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Project Role
function searchableSelectProjectRole(projectId) {
    const roles = @json(\App\Helpers\DropdownHelper::projectRoles());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData && wizardData.formData.projects) {
                            const project = wizardData.formData.projects.find(p => p.id === projectId);
                            if (project) {
                                this.selectedValue = project.role || '';
                                this.searchQuery = this.selectedValue;
                            }
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            if (query === currentValue) {
                return roles;
            }

            if (!query) {
                return roles;
            }
            return roles.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.projects) {
                        const project = wizardData.formData.projects.find(p => p.id === projectId);
                        if (project) {
                            project.role = option;
                            if (option !== 'Other') {
                                project.customRole = '';
                            }
                        }
                        // Clear projects error when a valid role is selected
                        if (wizardData.clearError) {
                            wizardData.clearError('projects');
                        }
                        break;
                    }
                }
                element = element.parentElement;
            }
        },

        validateAndUpdateRole() {
            // Check if searchQuery matches a valid role
            const matchedRole = roles.find(role => role.toLowerCase() === this.searchQuery.trim().toLowerCase());

            if (matchedRole) {
                // Valid role - update it
                this.selectOption(matchedRole);
            } else if (this.searchQuery.trim() && !this.selectedValue) {
                // Invalid text entered and no valid selection - clear it
                this.searchQuery = '';
            } else {
                // Restore to selected value
                this.searchQuery = this.selectedValue;
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}

// Searchable Select for Service Category
function searchableSelectServiceCategory(serviceId) {
    const categories = @json(\App\Helpers\DropdownHelper::serviceCategories());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            this.$nextTick(() => {
                let element = this.$el;
                while (element) {
                    if (element._x_dataStack && element._x_dataStack.length > 0) {
                        const wizardData = element._x_dataStack[0];
                        if (wizardData.formData && wizardData.formData.services) {
                            const service = wizardData.formData.services.find(s => s.id === serviceId);
                            if (service) {
                                this.selectedValue = service.category || '';
                                this.searchQuery = this.selectedValue;
                            }
                            break;
                        }
                    }
                    element = element.parentElement;
                }
            });
        },

        get filteredOptions() {
            const query = this.searchQuery.toLowerCase();
            const currentValue = this.selectedValue.toLowerCase();

            if (query === currentValue) {
                return categories;
            }

            if (!query) {
                return categories;
            }
            return categories.filter(option =>
                option.toLowerCase().includes(query)
            );
        },

        selectOption(option) {
            this.selectedValue = option;
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            let element = this.$el;
            while (element) {
                if (element._x_dataStack && element._x_dataStack.length > 0) {
                    const wizardData = element._x_dataStack[0];
                    if (wizardData.formData && wizardData.formData.services) {
                        const service = wizardData.formData.services.find(s => s.id === serviceId);
                        if (service) {
                            service.category = option;
                            if (option !== 'Other') {
                                service.customCategory = '';
                            }
                        }
                        break;
                    }
                }
                element = element.parentElement;
            }
        },

        validateAndUpdateCategory() {
            // Check if searchQuery matches a valid category
            const matchedCategory = categories.find(cat => cat.toLowerCase() === this.searchQuery.trim().toLowerCase());

            if (matchedCategory) {
                // Valid category - update it
                this.selectOption(matchedCategory);
            } else if (this.searchQuery.trim() && !this.selectedValue) {
                // Invalid text entered and no valid selection - clear it
                this.searchQuery = '';
            } else {
                // Restore to selected value
                this.searchQuery = this.selectedValue;
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else if (this.highlightedIndex === -1) {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
}
</script>
