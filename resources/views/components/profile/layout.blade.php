@props(['designer', 'projectsData', 'productsData', 'servicesData', 'marketplaceData' => [], 'certificationsData' => []])

<script>
// Global Alpine function for searchable sector select
window.searchableSelectSector = function() {
    const sectorOptions = @json(\App\Helpers\DropdownHelper::sectorOptions());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            // Get the parent Alpine component (the main profile component)
            const parentComponent = Alpine.$data(this.$el.closest('[x-data]').parentElement.closest('[x-data]'));
            if (parentComponent && parentComponent.form && parentComponent.form.sector) {
                this.selectedValue = parentComponent.form.sector;
                const selectedOption = sectorOptions.find(opt => opt.value === this.selectedValue);
                this.searchQuery = selectedOption ? selectedOption.label : '';
            }
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.highlightedIndex = -1;
            }
        },

        openDropdown() {
            if (!this.isOpen) {
                this.isOpen = true;
                this.highlightedIndex = -1;
            }
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
            const previousSector = this.selectedValue;
            this.selectedValue = option.value;
            this.searchQuery = option.label;
            this.isOpen = false;
            this.highlightedIndex = -1;

            // Update parent component's sector
            const parentComponent = Alpine.$data(this.$el.closest('[x-data]').parentElement.closest('[x-data]'));
            if (parentComponent && parentComponent.form) {
                parentComponent.form.sector = option.value;
                // Clear subsector when sector changes
                if (previousSector !== option.value) {
                    parentComponent.form.subSector = '';
                    // Dispatch global event to notify subsector component
                    window.dispatchEvent(new CustomEvent('sector-changed', { detail: { sector: option.value } }));
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
};

// Global Alpine function for searchable subsector select
window.searchableSelectSubSector = function() {
    const subSectorsByType = @json(\App\Helpers\DropdownHelper::subsectorsByType());

    return {
        selectedValue: '',
        searchQuery: '',
        isOpen: false,
        highlightedIndex: -1,

        init() {
            // Get the parent Alpine component (the main profile component)
            const parentComponent = Alpine.$data(this.$el.closest('[x-data]').parentElement.closest('[x-data]'));
            if (parentComponent && parentComponent.form && parentComponent.form.subSector) {
                this.selectedValue = parentComponent.form.subSector;
                this.searchQuery = parentComponent.form.subSector;
            }

            // Listen for global sector changes to clear subsector
            const self = this;
            window.addEventListener('sector-changed', function() {
                self.searchQuery = '';
                self.selectedValue = '';
                self.isOpen = false;
            });
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.highlightedIndex = -1;
            }
        },

        openDropdown() {
            if (!this.isOpen) {
                this.isOpen = true;
                this.highlightedIndex = -1;
            }
        },

        getSubSectorOptions() {
            // Get sector from parent component
            const parentComponent = Alpine.$data(this.$el.closest('[x-data]').parentElement.closest('[x-data]'));
            if (parentComponent && parentComponent.form && parentComponent.form.sector) {
                return subSectorsByType[parentComponent.form.sector] || [];
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
            this.searchQuery = option;
            this.isOpen = false;
            this.highlightedIndex = -1;

            // Update parent component's subsector
            const parentComponent = Alpine.$data(this.$el.closest('[x-data]').parentElement.closest('[x-data]'));
            if (parentComponent && parentComponent.form) {
                parentComponent.form.subSector = option;
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
            } else if (this.highlightedIndex === -1 && this.filteredOptions.length > 0) {
                this.highlightedIndex = 0;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    };
};

// Profile data initialization - stored in window to avoid escaping issues in x-data attribute
window.__profileFormData = <?php echo json_encode([
    'name' => $designer->name ?? '',
    'sector' => $designer->sector ?? '',
    'subSector' => $designer->sub_sector ?? '',
    'bio' => $designer->bio ?? '',
    'email' => $designer->email ?? '',
    'phone' => $designer->phone_number ?? '',
    'phoneCountry' => 'PS',
    'city' => $designer->city ?? array_key_first(\App\Helpers\DropdownHelper::citiesKeyValue()) ?? '',
    'address' => $designer->address ?? '',
    'linkedin' => $designer->linkedin ?? '',
    'instagram' => $designer->instagram ?? '',
    'facebook' => $designer->facebook ?? '',
    'behance' => $designer->behance ?? '',
    'avatarPreview' => null,
    'avatarUploading' => false,
    'coverPreview' => null,
    'coverUploading' => false
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileSkills = <?php echo json_encode(($designer->skills ?? collect())->pluck('name')->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileSkillOptions = <?php echo json_encode(App\View\Components\Profile\SkillsSection::getSkillOptions(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileProjects = <?php echo json_encode($projectsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileProducts = <?php echo json_encode($productsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileServices = <?php echo json_encode($servicesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileMarketplacePosts = <?php echo json_encode($marketplaceData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileCertifications = <?php echo json_encode($certificationsData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
window.__profileUploadSession = '{{ App\View\Components\Profile\Layout::generateUUID() }}';
window.__profileCsrfToken = '{{ csrf_token() }}';
window.__profileUpdateUrl = '{{ route("profile.update", ["locale" => app()->getLocale()]) }}';
window.__profileBaseUrl = '{{ url(app()->getLocale()) }}';
window.__profileStorageUrl = '{{ str_replace("'", "\\'", url("media")) }}';
window.__profileUploadUrl = '{{ route("upload.registration.image", ["locale" => app()->getLocale()]) }}';

// Main Alpine.js component for profile editing
window.profileEditData = function() {
    return {
        activeTab: 'profile',
        saving: false,
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        form: window.__profileFormData,
        socialLinkErrors: {
            linkedin: '',
            instagram: '',
            facebook: '',
            behance: ''
        },
        uploadedPaths: {
            avatar: null,
            cover: null
        },
        skills: window.__profileSkills,
        selectedSkill: '',
        customSkill: '',
        skillOptions: window.__profileSkillOptions,
        projects: window.__profileProjects,
        products: window.__profileProducts,
        services: window.__profileServices,
        marketplacePosts: window.__profileMarketplacePosts || [],
        certifications: window.__profileCertifications || [],
        certUploading: false,
        certSaving: false,
        projectModal: false,
        projectForm: {id: null, title: '', description: '', category: '', customCategory: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false},
        projectSubmitting: false,
        productModal: false,
        productForm: {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false},
        productSubmitting: false,
        serviceModal: false,
        serviceForm: {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false},
        serviceSubmitting: false,
        marketplaceModal: false,
        marketplaceForm: {id: null, title: '', description: '', category: '', type: '', tags: '', selectedTags: [], imagePath: null, imagePreview: null, image: null, uploading: false, sourceType: '', sourceId: ''},
        marketplaceSubmitting: false,
        uploadSession: window.__profileUploadSession,

        init() {
            const hash = window.location.hash.substring(1);
            if (hash && ['profile', 'projects', 'products', 'services', 'marketplace'].includes(hash)) {
                this.activeTab = hash;
            }
            this.$watch('activeTab', (value) => {
                window.location.hash = value;
            });
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        availableSkills() {
            return this.skillOptions.filter(skill => !this.skills.includes(skill));
        },

        addSkill() {
            let skillToAdd = '';
            if (this.selectedSkill) {
                skillToAdd = this.selectedSkill;
            } else if (this.customSkill && this.customSkill.trim().length > 0) {
                const trimmed = this.customSkill.trim();
                if (trimmed.length > 50) {
                    this.showToast('{{ __("Skill name is too long. Maximum 50 characters allowed.") }}', 'error');
                    return;
                }
                skillToAdd = trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
            }
            const skillLowerCase = skillToAdd.toLowerCase();
            const isDuplicate = this.skills.some(s => s.toLowerCase() === skillLowerCase);
            if (skillToAdd && !isDuplicate) {
                if (this.skills.length >= 20) {
                    this.showToast('{{ __("Maximum 20 skills allowed.") }}', 'warning');
                    return;
                }
                this.skills.push(skillToAdd);
                this.selectedSkill = '';
                this.customSkill = '';
            } else if (skillToAdd && isDuplicate) {
                this.showToast('{{ __("This skill has already been added!") }}', 'warning');
            }
        },

        removeSkill(skill) {
            this.skills = this.skills.filter(s => s !== skill);
        },

        getPhonePlaceholder() {
            const placeholders = {
                'PS': '0599123456', 'JO': '791234567', 'LB': '71123456', 'SY': '911234567', 'IL': '501234567',
                'SA': '501234567', 'AE': '501234567', 'KW': '50012345', 'QA': '33123456', 'BH': '36123456', 'OM': '92123456',
                'EG': '1001234567', 'MA': '612345678', 'DZ': '551234567', 'TN': '20123456', 'LY': '912345678',
                'IQ': '7901234567', 'YE': '712345678', 'SD': '912345678', 'SO': '612345678', 'DJ': '77123456', 'MR': '22123456', 'KM': '3212345',
                'US': '2025551234', 'GB': '7700900123', 'DE': '15112345678', 'FR': '612345678', 'TR': '5321234567'
            };
            return placeholders[this.form.phoneCountry] || '0599123456';
        },

        getPhoneHint() {
            const hints = {
                'PS': '{{ __("Enter 10 digits starting with 05 (e.g., 0599123456)") }}',
                'JO': '{{ __("Enter 9-10 digits (without country code)") }}', 'LB': '{{ __("Enter 7-8 digits (without country code)") }}',
                'SY': '{{ __("Enter 9-10 digits (without country code)") }}', 'IL': '{{ __("Enter 9-10 digits (without country code)") }}',
                'SA': '{{ __("Enter 9 digits starting with 5 (e.g., 501234567)") }}', 'AE': '{{ __("Enter 9 digits starting with 5 (e.g., 501234567)") }}',
                'KW': '{{ __("Enter 8 digits (without country code)") }}', 'QA': '{{ __("Enter 8 digits (without country code)") }}',
                'BH': '{{ __("Enter 8 digits (without country code)") }}', 'OM': '{{ __("Enter 8 digits (without country code)") }}',
                'EG': '{{ __("Enter 10 digits starting with 1 (e.g., 1001234567)") }}', 'MA': '{{ __("Enter 9 digits (without country code)") }}',
                'DZ': '{{ __("Enter 9 digits (without country code)") }}', 'TN': '{{ __("Enter 8 digits (without country code)") }}',
                'LY': '{{ __("Enter 9-10 digits (without country code)") }}', 'IQ': '{{ __("Enter 10 digits starting with 7 (e.g., 7901234567)") }}',
                'YE': '{{ __("Enter 9 digits starting with 7 (e.g., 712345678)") }}', 'SD': '{{ __("Enter 9 digits starting with 9 (e.g., 912345678)") }}',
                'SO': '{{ __("Enter 9 digits (without country code)") }}', 'DJ': '{{ __("Enter 8 digits (without country code)") }}',
                'MR': '{{ __("Enter 8 digits (without country code)") }}', 'KM': '{{ __("Enter 7 digits (without country code)") }}',
                'US': '{{ __("Enter 10 digits (without country code)") }}', 'GB': '{{ __("Enter 10 digits (without country code)") }}',
                'DE': '{{ __("Enter 10-11 digits (without country code)") }}', 'FR': '{{ __("Enter 9 digits (without country code)") }}',
                'TR': '{{ __("Enter 10 digits starting with 5 (e.g., 5321234567)") }}'
            };
            return hints[this.form.phoneCountry] || '{{ __("Enter phone number without country code") }}';
        },

        validateSocialLink(platform) {
            const url = this.form[platform];
            this.socialLinkErrors[platform] = '';
            if (!url || url.trim() === '') return true;
            try { new URL(url); } catch (e) {
                this.socialLinkErrors[platform] = '{{ __("Please enter a valid URL starting with https://") }}';
                return false;
            }
            const validations = {
                linkedin: { pattern: /^https?:\/\/(www\.)?linkedin\.com\/(in|company)\/[\w-]+\/?/i, message: '{{ __("Please enter a valid LinkedIn profile URL") }}' },
                instagram: { pattern: /^https?:\/\/(www\.)?instagram\.com\/[\w.]+\/?/i, message: '{{ __("Please enter a valid Instagram profile URL") }}' },
                facebook: { pattern: /^https?:\/\/(www\.)?facebook\.com\/[\w.-]+\/?/i, message: '{{ __("Please enter a valid Facebook profile URL") }}' },
                behance: { pattern: /^https?:\/\/(www\.)?behance\.net\/[\w-]+\/?/i, message: '{{ __("Please enter a valid Behance profile URL") }}' }
            };
            const validation = validations[platform];
            if (!validation.pattern.test(url)) {
                this.socialLinkErrors[platform] = validation.message;
                return false;
            }
            return true;
        },

        async saveProfile() {
            if (!this.form.name || this.form.name.trim() === '') { this.showToast('{{ __("Name is required") }}', 'error'); return; }
            if (!this.form.email || this.form.email.trim() === '') { this.showToast('{{ __("Email is required") }}', 'error'); return; }
            if (this.form.avatarUploading || this.form.coverUploading) { this.showToast('{{ __("Please wait while your images finish uploading...") }}', 'warning'); return; }
            const platforms = ['linkedin', 'instagram', 'facebook', 'behance'];
            let hasErrors = false;
            for (const platform of platforms) { if (!this.validateSocialLink(platform)) hasErrors = true; }
            if (hasErrors) { this.showToast('{{ __("Please fix the social media link errors") }}', 'error'); return; }
            this.saving = true;
            try {
                const response = await fetch(window.__profileUpdateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        name: this.form.name?.trim() || '', sector: this.form.sector?.trim() || '', sub_sector: this.form.subSector?.trim() || '',
                        bio: this.form.bio?.trim() || '', phone: this.form.phone?.trim() || '', city: this.form.city?.trim() || '',
                        address: this.form.address?.trim() || '', linkedin: this.form.linkedin?.trim() || '', instagram: this.form.instagram?.trim() || '',
                        facebook: this.form.facebook?.trim() || '', behance: this.form.behance?.trim() || '', skills: this.skills.join(','),
                        avatar: this.uploadedPaths.avatar || '', cover_image: this.uploadedPaths.cover || ''
                    })
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.showToast('{{ __("Profile updated successfully!") }}', 'success');
                    setTimeout(() => { window.location.reload(); }, 1500);
                } else {
                    if (data.errors) { this.showToast(Object.values(data.errors).flat().join(' '), 'error'); }
                    else { this.showToast(data.message || '{{ __("Failed to update profile") }}', 'error'); }
                }
            } catch (error) { console.error('Error saving profile:', error); this.showToast('{{ __("An error occurred while saving.") }}', 'error'); }
            finally { this.saving = false; }
        },

        openProjectModal(project = null) {
            if (project) {
                this.projectForm = { id: project.id, title: project.title, description: project.description, category: project.category || '', customCategory: '', role: project.role, customRole: '', images: [], imagePaths: [], uploading: false };
                if (project.image_paths && project.image_paths.length > 0) {
                    this.projectForm.imagePaths = [...project.image_paths];
                    this.projectForm.images = project.image_paths.map(path => ({ preview: path }));
                }
            } else { this.projectForm = {id: null, title: '', description: '', category: '', customCategory: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false}; }
            this.projectModal = true;
        },
        closeProjectModal() { this.projectModal = false; this.projectForm = {id: null, title: '', description: '', category: '', customCategory: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false}; },
        editProject(project) { this.openProjectModal(project); },

        saveProject() {
            if (!this.projectForm.title || !this.projectForm.description || !this.projectForm.category || !this.projectForm.role) { this.showToast('{{ __("Please fill in all required fields") }}', 'error'); return; }
            if (this.projectForm.category === 'Other' && !this.projectForm.customCategory) { this.showToast('{{ __("Please specify your custom category") }}', 'error'); return; }
            if (this.projectForm.role === 'Other' && !this.projectForm.customRole) { this.showToast('{{ __("Please specify your custom role") }}', 'error'); return; }
            this.projectSubmitting = true;
            const url = this.projectForm.id ? window.__profileBaseUrl + '/projects/' + this.projectForm.id : window.__profileBaseUrl + '/projects';
            const formData = new FormData();
            if (this.projectForm.id) formData.append('_method', 'PUT');
            formData.append('title', this.projectForm.title);
            formData.append('description', this.projectForm.description);
            formData.append('category', this.projectForm.category === 'Other' ? this.projectForm.customCategory : this.projectForm.category);
            formData.append('role', this.projectForm.role);
            if (this.projectForm.imagePaths && this.projectForm.imagePaths.length > 0) {
                this.projectForm.imagePaths.forEach((path, index) => { formData.append('image_paths[' + index + ']', path); });
            }
            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.ok ? response.json() : response.text().then(text => { throw new Error('HTTP ' + response.status); }))
            .then(data => {
                if (data.success) {
                    const projectData = { id: data.project.id, title: data.project.title, description: data.project.description, category: data.project.category || '', role: data.project.role, image_paths: (data.project.images || []).map(img => window.__profileStorageUrl + '/' + img.image_path) };
                    if (this.projectForm.id) { const index = this.projects.findIndex(p => p.id === this.projectForm.id); if (index !== -1) this.projects[index] = projectData; }
                    else { this.projects.push(projectData); }
                    this.closeProjectModal(); this.showToast(data.message, 'success'); setTimeout(() => location.reload(), 1000);
                } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); }
            })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); })
            .finally(() => { this.projectSubmitting = false; });
        },

        deleteProject(id, index) {
            if (!confirm('{{ __("Are you sure you want to delete this project?") }}')) return;
            const formData = new FormData(); formData.append('_method', 'DELETE');
            fetch(window.__profileBaseUrl + '/projects/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) { this.projects.splice(index, 1); this.showToast(data.message, 'success'); setTimeout(() => location.reload(), 1000); } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); } })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); });
        },

        openProductModal(product = null) {
            if (product) {
                this.productForm = { id: product.id, name: product.name, description: product.description, category: product.category, customCategory: '', images: [], imagePaths: [], uploading: false };
                if (product.image_paths && product.image_paths.length > 0) {
                    this.productForm.imagePaths = [...product.image_paths];
                    this.productForm.images = product.image_paths.map(path => ({ preview: path }));
                }
            } else { this.productForm = {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false}; }
            this.productModal = true;
        },
        closeProductModal() { this.productModal = false; this.productForm = {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false}; },
        editProduct(product) { this.openProductModal(product); },

        saveProduct() {
            if (!this.productForm.name || !this.productForm.description || !this.productForm.category) { this.showToast('{{ __("Please fill in all required fields") }}', 'error'); return; }
            if (this.productForm.category === 'Other' && !this.productForm.customCategory) { this.showToast('{{ __("Please specify your custom category") }}', 'error'); return; }
            this.productSubmitting = true;
            const url = this.productForm.id ? window.__profileBaseUrl + '/products/' + this.productForm.id : window.__profileBaseUrl + '/products';
            const formData = new FormData();
            if (this.productForm.id) formData.append('_method', 'PUT');
            formData.append('name', this.productForm.name);
            formData.append('description', this.productForm.description);
            formData.append('category', this.productForm.category === 'Other' ? this.productForm.customCategory : this.productForm.category);
            if (this.productForm.imagePaths && this.productForm.imagePaths.length > 0) {
                this.productForm.imagePaths.forEach((path, index) => { formData.append('image_paths[' + index + ']', path); });
            }
            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.ok ? response.json() : response.text().then(text => { throw new Error('HTTP ' + response.status); }))
            .then(data => {
                if (data.success) {
                    const productData = { id: data.product.id, name: data.product.title, description: data.product.description, category: data.product.category, image_paths: (data.product.images || []).map(img => window.__profileStorageUrl + '/' + img.image_path) };
                    if (this.productForm.id) { const index = this.products.findIndex(p => p.id === this.productForm.id); if (index !== -1) this.products[index] = productData; }
                    else { this.products.push(productData); }
                    this.closeProductModal(); this.showToast(data.message, 'success'); setTimeout(() => location.reload(), 1000);
                } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); }
            })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); })
            .finally(() => { this.productSubmitting = false; });
        },

        deleteProduct(id, index) {
            if (!confirm('{{ __("Are you sure you want to delete this product?") }}')) return;
            const formData = new FormData(); formData.append('_method', 'DELETE');
            fetch(window.__profileBaseUrl + '/products/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) { this.products.splice(index, 1); this.showToast(data.message, 'success'); setTimeout(() => location.reload(), 1000); } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); } })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); });
        },

        openServiceModal(service = null) {
            if (service) { this.serviceForm = { id: service.id, name: service.name, description: service.description, category: service.category, customCategory: '', imagePath: service.imagePath || null, imagePreview: service.imagePreview || null, uploading: false }; }
            else { this.serviceForm = {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false}; }
            this.serviceModal = true;
        },
        closeServiceModal() { this.serviceModal = false; this.serviceForm = {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false}; },
        editService(service) { this.openServiceModal(service); },

        saveService() {
            if (!this.serviceForm.name || !this.serviceForm.description || !this.serviceForm.category) { this.showToast('{{ __("Please fill in all required fields") }}', 'error'); return; }
            if (this.serviceForm.category === 'Other' && !this.serviceForm.customCategory) { this.showToast('{{ __("Please specify your custom category") }}', 'error'); return; }
            this.serviceSubmitting = true;
            const url = this.serviceForm.id ? window.__profileBaseUrl + '/services/' + this.serviceForm.id : window.__profileBaseUrl + '/services';
            const formData = new FormData();
            if (this.serviceForm.id) formData.append('_method', 'PUT');
            formData.append('name', this.serviceForm.name);
            formData.append('description', this.serviceForm.description);
            formData.append('category', this.serviceForm.category === 'Other' ? this.serviceForm.customCategory : this.serviceForm.category);
            if (this.serviceForm.imagePath) formData.append('image_path', this.serviceForm.imagePath);
            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.ok ? response.json() : response.text().then(text => { throw new Error('HTTP ' + response.status); }))
            .then(data => {
                if (data.success) {
                    const serviceData = { id: data.service.id, name: data.service.name, description: data.service.description, category: data.service.category };
                    if (this.serviceForm.id) { const index = this.services.findIndex(s => s.id === this.serviceForm.id); if (index !== -1) this.services[index] = serviceData; }
                    else { this.services.push(serviceData); }
                    this.closeServiceModal(); this.showToast(data.message, 'success'); setTimeout(() => location.reload(), 1000);
                } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); }
            })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); })
            .finally(() => { this.serviceSubmitting = false; });
        },

        deleteService(id, index) {
            if (!confirm('{{ __("Are you sure you want to delete this service?") }}')) return;
            const formData = new FormData(); formData.append('_method', 'DELETE');
            fetch(window.__profileBaseUrl + '/services/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) { this.services.splice(index, 1); this.showToast(data.message, 'success'); } else { this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error'); } })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); });
        },

        // Marketplace Methods
        openMarketplaceModal(post = null) {
            if (post) {
                this.marketplaceForm = {
                    id: post.id,
                    title: post.title || '',
                    description: post.description || '',
                    category: post.category || '',
                    type: post.type || '',
                    tags: Array.isArray(post.tags) ? post.tags.join(', ') : (post.tags || ''),
                    selectedTags: Array.isArray(post.tags) ? [...post.tags] : [],
                    imagePath: post.image_path || null,
                    imagePreview: null,
                    image: post.image || null,
                    uploading: false,
                    sourceType: '',
                    sourceId: ''
                };
            } else {
                this.marketplaceForm = {id: null, title: '', description: '', category: '', type: '', tags: '', selectedTags: [], imagePath: null, imagePreview: null, image: null, uploading: false, sourceType: '', sourceId: ''};
            }
            this.marketplaceModal = true;
        },

        closeMarketplaceModal() {
            this.marketplaceModal = false;
            this.marketplaceForm = {id: null, title: '', description: '', category: '', type: '', tags: '', selectedTags: [], imagePath: null, imagePreview: null, image: null, uploading: false, sourceType: '', sourceId: ''};
        },

        editMarketplacePost(post) {
            this.openMarketplaceModal(post);
        },

        async loadSourceData() {
            if (!this.marketplaceForm.sourceType || !this.marketplaceForm.sourceId) return;

            try {
                const response = await fetch(window.__profileBaseUrl + '/marketplace-posts/source-data?source_type=' + this.marketplaceForm.sourceType + '&source_id=' + this.marketplaceForm.sourceId, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__profileCsrfToken }
                });
                const data = await response.json();
                if (data.success && data.data) {
                    this.marketplaceForm.title = data.data.title || '';
                    this.marketplaceForm.description = data.data.description || '';
                    this.marketplaceForm.category = data.data.category || '';
                    if (data.data.image) {
                        this.marketplaceForm.image = data.data.image;
                        this.marketplaceForm.imagePath = data.data.image_path;
                    }
                    this.showToast('{{ __("Data imported successfully!") }}', 'success');
                }
            } catch (error) {
                console.error('Error loading source data:', error);
                this.showToast('{{ __("Failed to load data") }}', 'error');
            }
        },

        saveMarketplacePost() {
            if (!this.marketplaceForm.title || !this.marketplaceForm.description || !this.marketplaceForm.category || !this.marketplaceForm.type) {
                this.showToast('{{ __("Please fill in all required fields") }}', 'error');
                return;
            }
            this.marketplaceSubmitting = true;
            const url = this.marketplaceForm.id ? window.__profileBaseUrl + '/marketplace-posts/' + this.marketplaceForm.id : window.__profileBaseUrl + '/marketplace-posts';
            const formData = new FormData();
            if (this.marketplaceForm.id) formData.append('_method', 'PUT');
            formData.append('title', this.marketplaceForm.title);
            formData.append('description', this.marketplaceForm.description);
            formData.append('category', this.marketplaceForm.category);
            formData.append('type', this.marketplaceForm.type);
            // Send selected tags as individual array entries
            (this.marketplaceForm.selectedTags || []).forEach(tag => {
                formData.append('tags[]', tag);
            });
            if (this.marketplaceForm.imagePath) formData.append('image_path', this.marketplaceForm.imagePath);

            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData })
            .then(response => response.ok ? response.json() : response.text().then(text => { throw new Error('HTTP ' + response.status); }))
            .then(data => {
                if (data.success) {
                    const postData = {
                        id: data.post.id,
                        title: data.post.title,
                        description: data.post.description,
                        category: data.post.category,
                        type: data.post.type,
                        tags: data.post.tags || [],
                        image: data.post.image,
                        approval_status: data.post.approval_status
                    };
                    const isCreate = !this.marketplaceForm.id;
                    if (!isCreate) {
                        const index = this.marketplacePosts.findIndex(p => p.id === this.marketplaceForm.id);
                        if (index !== -1) this.marketplacePosts[index] = postData;
                    } else {
                        this.marketplacePosts.push(postData);
                    }
                    this.closeMarketplaceModal();
                    if (isCreate) {
                        // Show share popup for new posts
                        this.$dispatch('share-marketplace-post', { postId: data.post.id, postTitle: data.post.title });
                    } else {
                        this.showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error');
                }
            })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); })
            .finally(() => { this.marketplaceSubmitting = false; });
        },

        deleteMarketplacePost(id, index) {
            if (!confirm('{{ __("Are you sure you want to delete this marketplace post?") }}')) return;
            fetch(window.__profileBaseUrl + '/marketplace-posts/' + id + '/delete', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.marketplacePosts.splice(index, 1);
                    this.showToast(data.message, 'success');
                } else {
                    this.showToast('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'), 'error');
                }
            })
            .catch(error => { console.error('Error:', error); this.showToast('{{ __("An error occurred") }}', 'error'); });
        },

        async handleMarketplaceImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                this.showToast(validation.error, 'error');
                event.target.value = '';
                return;
            }
            this.marketplaceForm.uploading = true;
            const uploadedPath = await this.uploadImage(file, 'marketplace', 'temp_' + Date.now());
            if (uploadedPath) {
                const reader = new FileReader();
                reader.onload = (e) => { this.marketplaceForm.imagePreview = e.target.result; };
                reader.readAsDataURL(file);
                this.marketplaceForm.imagePath = uploadedPath;
            }
            this.marketplaceForm.uploading = false;
            event.target.value = '';
        },

        removeMarketplaceImage() {
            this.marketplaceForm.imagePreview = null;
            this.marketplaceForm.imagePath = null;
            this.marketplaceForm.image = null;
        },

        async handleCertUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.type !== 'application/pdf') { this.showToast('{{ __("Only PDF files are allowed.") }}', 'error'); event.target.value = ''; return; }
            if (file.size > 10 * 1024 * 1024) { this.showToast('{{ __("File size exceeds 10MB limit.") }}', 'error'); event.target.value = ''; return; }
            if (this.certifications.length >= 3) { this.showToast('{{ __("Maximum 3 certifications allowed.") }}', 'error'); event.target.value = ''; return; }
            this.certUploading = true;
            try {
                const formData = new FormData();
                formData.append('new_certification', file);
                const response = await fetch(window.__profileBaseUrl + '/profile/update-certifications', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.certifications.push(data.new_cert);
                    this.showToast('{{ __("Certification uploaded successfully!") }}', 'success');
                } else {
                    this.showToast(data.message || '{{ __("Upload failed") }}', 'error');
                }
            } catch (error) { console.error('Cert upload error:', error); this.showToast('{{ __("An error occurred while uploading.") }}', 'error'); }
            finally { this.certUploading = false; event.target.value = ''; }
        },

        async removeCert(index) {
            if (!confirm('{{ __("Are you sure you want to remove this certification?") }}')) return;
            this.certSaving = true;
            try {
                const formData = new FormData();
                formData.append('remove_index', index);
                const response = await fetch(window.__profileBaseUrl + '/profile/update-certifications', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.certifications.splice(index, 1);
                    this.showToast('{{ __("Certification removed successfully!") }}', 'success');
                } else {
                    this.showToast(data.message || '{{ __("Failed to remove") }}', 'error');
                }
            } catch (error) { console.error('Cert remove error:', error); this.showToast('{{ __("An error occurred.") }}', 'error'); }
            finally { this.certSaving = false; }
        },

        closeAllModals() { this.projectModal = false; this.productModal = false; this.serviceModal = false; this.marketplaceModal = false; },

        validateImageFile(file) {
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) return { valid: false, error: '{{ __("File size exceeds 5MB limit.") }}' };
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) return { valid: false, error: '{{ __("Invalid file type. Only JPG, PNG, and GIF allowed.") }}' };
            const extension = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'gif'].includes(extension)) return { valid: false, error: '{{ __("Invalid file extension.") }}' };
            return { valid: true };
        },

        async calculateFileHash(file) {
            const str = file.name + file.size + file.lastModified;
            let hash = 0;
            for (let i = 0; i < str.length; i++) { hash = ((hash << 5) - hash) + str.charCodeAt(i); hash = hash & hash; }
            return hash.toString(36);
        },

        async uploadImage(file, type, itemId = null) {
            const abortController = new AbortController();
            const timeoutId = setTimeout(() => abortController.abort(), 30000);
            try {
                if (!file || file.size === 0) throw new Error('File is empty');
                if (file.size > 5 * 1024 * 1024) throw new Error('File size exceeds 5MB');
                let fileHash = await this.calculateFileHash(file);
                if (type === 'product' || type === 'project') fileHash = fileHash + '_' + (itemId || Date.now());
                const formData = new FormData();
                formData.append('image', file); formData.append('type', type); formData.append('session_id', this.uploadSession); formData.append('file_hash', fileHash);
                const response = await fetch(window.__profileUploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__profileCsrfToken, 'Accept': 'application/json' }, body: formData, signal: abortController.signal });
                clearTimeout(timeoutId);
                if (!response.ok) throw new Error('Server error');
                const data = await response.json();
                if (data.success && data.path) return data.path;
                throw new Error(data.message || 'Upload failed');
            } catch (error) {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') this.showToast('{{ __("Upload timed out.") }}', 'error');
                else this.showToast('{{ __("Upload failed:") }} ' + error.message, 'error');
                return null;
            }
        },

        async handleProjectImageUpload(event) {
            const files = event.target.files; if (!files || files.length === 0) return;
            const currentCount = this.projectForm.images ? this.projectForm.images.length : 0;
            if (files.length > 6 - currentCount) { this.showToast('{{ __("Maximum 6 images per project.") }}', 'error'); event.target.value = ''; return; }
            this.projectForm.uploading = true;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const validation = this.validateImageFile(file); if (!validation.valid) { this.showToast(validation.error, 'error'); continue; }
                const uploadedPath = await this.uploadImage(file, 'project', 'temp_' + Date.now());
                if (uploadedPath) {
                    const reader = new FileReader();
                    reader.onload = (e) => { if (!this.projectForm.images) this.projectForm.images = []; this.projectForm.images.push({ preview: e.target.result }); };
                    reader.readAsDataURL(file);
                    if (!this.projectForm.imagePaths) this.projectForm.imagePaths = [];
                    this.projectForm.imagePaths.push(uploadedPath);
                }
            }
            this.projectForm.uploading = false; event.target.value = '';
        },

        removeProjectImage(index) {
            if (this.projectForm.images.length <= 1) { this.showToast('{{ __("Keep at least 1 image.") }}', 'warning'); return; }
            this.projectForm.images.splice(index, 1); this.projectForm.imagePaths.splice(index, 1);
        },

        async handleProductImageUpload(event) {
            const files = event.target.files; if (!files || files.length === 0) return;
            const currentCount = this.productForm.images ? this.productForm.images.length : 0;
            if (files.length > 6 - currentCount) { this.showToast('{{ __("Maximum 6 images per product.") }}', 'error'); event.target.value = ''; return; }
            this.productForm.uploading = true;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const validation = this.validateImageFile(file); if (!validation.valid) { this.showToast(validation.error, 'error'); continue; }
                const uploadedPath = await this.uploadImage(file, 'product', 'temp_' + Date.now());
                if (uploadedPath) {
                    const reader = new FileReader();
                    reader.onload = (e) => { if (!this.productForm.images) this.productForm.images = []; this.productForm.images.push({ preview: e.target.result }); };
                    reader.readAsDataURL(file);
                    if (!this.productForm.imagePaths) this.productForm.imagePaths = [];
                    this.productForm.imagePaths.push(uploadedPath);
                }
            }
            this.productForm.uploading = false; event.target.value = '';
        },

        removeProductImage(index) {
            if (this.productForm.images.length <= 1) { this.showToast('{{ __("Keep at least 1 image.") }}', 'warning'); return; }
            this.productForm.images.splice(index, 1); this.productForm.imagePaths.splice(index, 1);
        },

        async handleServiceImageUpload(event) {
            const file = event.target.files[0]; if (!file) return;
            const validation = this.validateImageFile(file); if (!validation.valid) { this.showToast(validation.error, 'error'); event.target.value = ''; return; }
            this.serviceForm.uploading = true;
            const uploadedPath = await this.uploadImage(file, 'service', 'temp_' + Date.now());
            if (uploadedPath) {
                const reader = new FileReader();
                reader.onload = (e) => { this.serviceForm.imagePreview = e.target.result; };
                reader.readAsDataURL(file);
                this.serviceForm.imagePath = uploadedPath;
            }
            this.serviceForm.uploading = false; event.target.value = '';
        },

        removeServiceImage() { this.serviceForm.imagePreview = null; this.serviceForm.imagePath = null; },

        async handleAvatarUpload(event) {
            const file = event.target.files[0]; if (!file) return;
            const validation = this.validateImageFile(file); if (!validation.valid) { this.showToast(validation.error, 'error'); event.target.value = ''; return; }
            this.form.avatarUploading = true;
            const uploadedPath = await this.uploadImage(file, 'avatar');
            if (uploadedPath) {
                this.uploadedPaths.avatar = uploadedPath;
                const reader = new FileReader();
                reader.onload = (e) => { this.form.avatarPreview = e.target.result; };
                reader.readAsDataURL(file);
            }
            this.form.avatarUploading = false; event.target.value = '';
        },

        async handleCoverUpload(event) {
            const file = event.target.files[0]; if (!file) return;
            const validation = this.validateImageFile(file); if (!validation.valid) { this.showToast(validation.error, 'error'); event.target.value = ''; return; }
            this.form.coverUploading = true;
            const uploadedPath = await this.uploadImage(file, 'cover');
            if (uploadedPath) {
                this.uploadedPaths.cover = uploadedPath;
                const reader = new FileReader();
                reader.onload = (e) => { this.form.coverPreview = e.target.result; };
                reader.readAsDataURL(file);
            }
            this.form.coverUploading = false; event.target.value = '';
        }
    };
};
</script>

<div class="min-h-screen bg-gray-50" x-data="profileEditData()" x-init="init()" @keydown.escape="closeAllModals()" x-cloak>

    {{-- Toast Notification --}}
    <div x-show="toast.show"
         x-cloak
         @click="toast.show = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[9999] max-w-md w-full mx-4 cursor-pointer">
        <div :class="{
                'bg-gradient-to-r from-blue-600 to-green-500': toast.type === 'success',
                'bg-gradient-to-r from-red-500 to-red-600': toast.type === 'error',
                'bg-gradient-to-r from-yellow-500 to-orange-500': toast.type === 'warning'
             }"
             class="rounded-2xl shadow-2xl p-4 flex items-center gap-4 text-white backdrop-blur-sm">

            {{-- Icon --}}
            <div class="flex-shrink-0">
                <template x-if="toast.type === 'success'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </template>
            </div>

            {{-- Message --}}
            <div class="flex-1">
                <p class="text-white font-semibold text-base leading-tight" x-text="toast.message"></p>
            </div>

            {{-- Close Button --}}
            <button @click.stop="toast.show = false"
                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 hover:bg-white/25 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110 hover:rotate-90">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Share Post Modal --}}
    <x-modal.share-post />

    {{ $slot }}
</div>
