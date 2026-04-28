@props(['designer', 'projectsData', 'productsData', 'servicesData'])

<script>
    // Base URL for AJAX calls (includes /PalestineCreativeHub/{locale} prefix)
    window.__portfolioBaseUrl = '{{ url(app()->getLocale()) }}';
    window.__portfolioCsrfToken = '{{ csrf_token() }}';

    // Global Alpine functions for project category and role comboboxes
    window.searchableProjectCategory = function() {
        const categories = [
            'Branding', 'UI/UX', 'Photography', 'Illustration', 'Architecture',
            'Fashion', 'Digital Art', 'Graphic Design', 'Interior Design',
            'General', 'Other'
        ].sort();

        return {
            selectedValue: '',
            searchQuery: '',
            isOpen: false,
            highlightedIndex: -1,

            init() {
                // Get the parent portfolio component
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem && parentComponent.currentItem.category) {
                    this.selectedValue = parentComponent.currentItem.category;
                    this.searchQuery = parentComponent.currentItem.category;
                }
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
                this.updateCategory();
            },

            updateCategory() {
                // Update parent component's currentItem.category
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem) {
                    parentComponent.currentItem.category = this.searchQuery;
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

    window.searchableProjectRole = function() {
        const roles = [
            'Lead Designer', 'Designer', 'Architect', 'Interior Designer',
            'Interior Architect', 'Lead Interior & Furniture Designer',
            'Interior Architect & Fit-Out Designer', 'Interior Designer & Revit Modeler',
            'Key Urban Planner', 'Lead Graphic Designer', 'Lead UI/UX Designer',
            'Lead Social Media Designer', '3D Rendering Specialist', 'Project Manager',
            'Planning & Supervision', 'Developer', 'Services Provider', 'Other'
        ].sort();

        return {
            selectedValue: '',
            searchQuery: '',
            isOpen: false,
            highlightedIndex: -1,

            init() {
                // Get the parent portfolio component
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem && parentComponent.currentItem.role) {
                    this.selectedValue = parentComponent.currentItem.role;
                    this.searchQuery = parentComponent.currentItem.role;
                }
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
                this.updateRole();
            },

            updateRole() {
                // Update parent component's currentItem.role
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem) {
                    parentComponent.currentItem.role = this.searchQuery;
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

    window.serviceCategorySelect = function() {
        const categories = @json(\App\Helpers\DropdownHelper::serviceCategories());

        return {
            selectedValue: '',
            searchQuery: '',
            isOpen: false,
            highlightedIndex: -1,

            initFromParent() {
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem && parentComponent.currentItem.category) {
                    this.selectedValue = parentComponent.currentItem.category;
                    this.searchQuery = parentComponent.currentItem.category;
                }
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
                // Update parent component's currentItem.category
                const parentComponent = Alpine.$data(this.$el.closest('[x-data*="portfolioData"]'));
                if (parentComponent && parentComponent.currentItem) {
                    parentComponent.currentItem.category = option;
                }
            },

            validateAndUpdate() {
                const matchedCategory = categories.find(cat => cat.toLowerCase() === this.searchQuery.trim().toLowerCase());
                if (matchedCategory) {
                    this.selectOption(matchedCategory);
                } else if (this.searchQuery.trim() && !this.selectedValue) {
                    this.searchQuery = '';
                } else {
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
    };

    window.portfolioData = function() {
        return {
        deleteModal: false,
        deleteType: '',
        deleteId: null,
        deleteName: '',

        editProjectModal: false,
        editProductModal: false,
        editServiceModal: false,
        editBioModal: false,
        editSkillsModal: false,

        addProjectModal: false,
        addProductModal: false,
        addServiceModal: false,

        currentItem: {},
        uploadedImages: [],
        isSubmitting: false,
        bioText: '',
        skillsText: '',

        selectedSkill: '',
        customSkill: '',
        skillOptions: <?php echo json_encode(App\View\Components\Profile\SkillsSection::getSkillOptions()); ?>,
        skillsArray: [],

        init() {

            // Add global event listeners to track dispatch events
            window.addEventListener('open-edit-project', (e) => {
            });
            window.addEventListener('open-edit-product', (e) => {
            });
            window.addEventListener('open-edit-service', (e) => {
            });
            window.addEventListener('open-delete-project', (e) => {
            });
            window.addEventListener('open-delete-product', (e) => {
            });
            window.addEventListener('open-delete-service', (e) => {
            });
            window.addEventListener('open-add-project', (e) => {
            });
            window.addEventListener('open-add-product', (e) => {
            });
            window.addEventListener('open-add-service', (e) => {
            });
        },

        openDeleteModal(type, id, name) {
            this.deleteType = type;
            this.deleteId = id;
            this.deleteName = name;
            this.deleteModal = true;
        },

        confirmDelete() {

            const locale = '{{ app()->getLocale() }}';

            const url = `/${locale}/${this.deleteType}s/${this.deleteId}`;

            // Use FormData with _method for Laravel method spoofing
            const formData = new FormData();
            formData.append('_method', 'DELETE');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {

                if (!response.ok) {
                    console.error('❌ Response not OK! Status:', response.status);
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse response as JSON:', e);
                            throw new Error('Server returned status ' + response.status + ': ' + text.substring(0, 200));
                        }
                    });
                }

                return response.json();
            })
            .then(data => {

                if (data.success) {
                    this.deleteModal = false;
                    location.reload();
                } else {
                    console.error('❌ Delete failed! Message:', data.message);
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ ERROR CAUGHT');
                console.error('Error object:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                alert('{{ __("An error occurred while deleting") }}: ' + error.message);
            });
        },

        openEditProjectModal(id) {
            const url = `/{{ app()->getLocale() }}/projects/${id}`;

            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.currentItem = data.project || data;
                    this.uploadedImages = [];
                    this.editProjectModal = true;
                })
                .catch(error => {
                    console.error('❌ Error fetching project:', error);
                });
        },

        openEditProductModal(id) {
            const url = `/{{ app()->getLocale() }}/products/${id}`;

            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.currentItem = data.product || data;
                    this.uploadedImages = [];
                    this.editProductModal = true;
                })
                .catch(error => {
                    console.error('❌ Error fetching product:', error);
                });
        },

        openEditServiceModal(id) {
            const url = `/{{ app()->getLocale() }}/services/${id}`;

            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.currentItem = data.service || data;
                    this.uploadedImages = [];
                    this.editServiceModal = true;
                })
                .catch(error => {
                    console.error('❌ Error fetching service:', error);
                });
        },

        openEditBioModal() {
            this.bioText = <?php echo json_encode($designer->bio ?? ''); ?>;
            this.editBioModal = true;
        },

        openEditSkillsModal() {
            // Parse the skills from comma-separated string to array
            const skillsString = <?php echo json_encode($designer->skills->pluck('name')->implode(', ')); ?>;
            this.skillsArray = skillsString.split(',').map(s => s.trim()).filter(s => s.length > 0);
            this.selectedSkill = '';
            this.customSkill = '';
            this.editSkillsModal = true;
        },

        openAddProjectModal() {
            this.currentItem = { title: '', description: '', role: '', category: '' };
            this.uploadedImages = [];
            this.addProjectModal = true;
        },

        openAddProductModal() {
            this.currentItem = { name: '', description: '', category: '' };
            this.uploadedImages = [];
            this.addProductModal = true;
        },

        openAddServiceModal() {
            this.currentItem = { title: '', description: '', category: '' };
            this.uploadedImages = [];
            this.addServiceModal = true;
        },

        submitEditProject() {
            this.isSubmitting = true;
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('title', this.currentItem.title);
            formData.append('description', this.currentItem.description);
            formData.append('role', this.currentItem.role);
            formData.append('category', this.currentItem.category || '');

            this.uploadedImages.forEach((image, index) => {
                formData.append(`image_paths[${index}]`, image);
            });

            fetch(`${window.__portfolioBaseUrl}/projects/${this.currentItem.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.editProjectModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditProduct() {
            this.isSubmitting = true;
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('name', this.currentItem.name);
            formData.append('description', this.currentItem.description);
            formData.append('category', this.currentItem.category);

            this.uploadedImages.forEach((image, index) => {
                formData.append(`image_paths[${index}]`, image);
            });

            fetch(`${window.__portfolioBaseUrl}/products/${this.currentItem.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.editProductModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditService() {
            this.isSubmitting = true;

            fetch(`${window.__portfolioBaseUrl}/services/${this.currentItem.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PUT',
                    name: this.currentItem.name,
                    description: this.currentItem.description,
                    category: this.currentItem.category
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.editServiceModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditBio() {
            this.isSubmitting = true;
            fetch(`{{ route('designer.update-bio', ['locale' => app()->getLocale()]) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ bio: this.bioText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.editBioModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditSkills() {
            this.isSubmitting = true;
            fetch(`${window.__portfolioBaseUrl}/designer/update-skills`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ skills: this.skillsArray.join(', ') })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.editSkillsModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitAddProject() {
            this.isSubmitting = true;
            const formData = new FormData();
            formData.append('title', this.currentItem.title);
            formData.append('description', this.currentItem.description);
            formData.append('role', this.currentItem.role);
            formData.append('category', this.currentItem.category || '');

            this.uploadedImages.forEach((image, index) => {
                formData.append(`image_paths[${index}]`, image);
            });

            fetch(`${window.__portfolioBaseUrl}/projects`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.addProjectModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitAddProduct() {
            this.isSubmitting = true;
            const formData = new FormData();
            formData.append('name', this.currentItem.name);
            formData.append('description', this.currentItem.description);
            formData.append('category', this.currentItem.category);

            this.uploadedImages.forEach((image, index) => {
                formData.append(`image_paths[${index}]`, image);
            });

            fetch(`${window.__portfolioBaseUrl}/products`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.addProductModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitAddService() {
            this.isSubmitting = true;

            fetch(`${window.__portfolioBaseUrl}/services`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: this.currentItem.name,
                    description: this.currentItem.description,
                    category: this.currentItem.category
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.addServiceModal = false;
                    location.reload();
                } else {
                    alert('{{ __("Error") }}: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred") }}');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        handleImageUpload(event) {
            // Client-side guards so users get feedback BEFORE uploading 50 MB
            // of bandwidth that the server will reject. Mirrors the server
            // rules: jpg/jpeg/png/webp, max 5 MB, max 6 images per item.
            // (bugs.md H-14)
            const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const MAX_BYTES = 5 * 1024 * 1024;
            const MAX_IMAGES = 6;
            const files = Array.from(event.target.files);
            const rejected = [];

            files.forEach(file => {
                if (!ALLOWED_MIMES.includes(file.type)) {
                    rejected.push(`${file.name}: {{ __('only JPG, PNG, or WebP images are allowed') }}`);
                    return;
                }
                if (file.size > MAX_BYTES) {
                    rejected.push(`${file.name}: {{ __('larger than 5 MB') }}`);
                    return;
                }
                if (this.uploadedImages.length >= MAX_IMAGES) {
                    rejected.push(`${file.name}: {{ __('maximum 6 images per item') }}`);
                    return;
                }
                this.uploadedImages.push(file);
            });

            if (rejected.length > 0) {
                if (typeof showToast === 'function') {
                    showToast(rejected.join('\n'), 'warning');
                } else {
                    alert(rejected.join('\n'));
                }
            }

            // Reset the input so re-selecting the same rejected file fires change again.
            event.target.value = '';
        },

        removeImage(index) {
            this.uploadedImages.splice(index, 1);
        },

        availableSkills() {
            return this.skillOptions.filter(skill => !this.skillsArray.includes(skill));
        },

        addSkill() {
            let skillToAdd = '';

            if (this.selectedSkill) {
                skillToAdd = this.selectedSkill;
            } else if (this.customSkill && this.customSkill.trim().length > 0) {
                const trimmed = this.customSkill.trim();
                if (trimmed.length > 50) {
                    alert('{{ __("Skill name is too long. Maximum 50 characters allowed.") }}');
                    return;
                }
                skillToAdd = trimmed;
                skillToAdd = skillToAdd.charAt(0).toUpperCase() + skillToAdd.slice(1);
            }

            const skillLowerCase = skillToAdd.toLowerCase();
            const isDuplicate = this.skillsArray.some(s => s.toLowerCase() === skillLowerCase);

            if (skillToAdd && !isDuplicate) {
                this.skillsArray.push(skillToAdd);
                this.selectedSkill = '';
                this.customSkill = '';
            } else if (skillToAdd && isDuplicate) {
                alert('{{ __("This skill has already been added!") }}');
            }
        },

        removeSkill(skill) {
            this.skillsArray = this.skillsArray.filter(s => s !== skill);
        }
        };
    }
</script>

<div
    id="portfolioModals"
    class="min-h-screen bg-gray-50"
    x-data="portfolioData()"

    {{-- Event Listeners for $dispatch --}}
    @open-edit-project.window="openEditProjectModal($event.detail.id)"
    @open-edit-product.window="openEditProductModal($event.detail.id)"
    @open-edit-service.window="openEditServiceModal($event.detail.id)"
    @open-edit-bio.window="openEditBioModal()"
    @open-edit-skills.window="openEditSkillsModal()"

    @open-delete-project.window="openDeleteModal('project', $event.detail.id, $event.detail.name)"
    @open-delete-product.window="openDeleteModal('product', $event.detail.id, $event.detail.name)"
    @open-delete-service.window="openDeleteModal('service', $event.detail.id, $event.detail.name)"

    @open-add-project.window="openAddProjectModal()"
    @open-add-product.window="openAddProductModal()"
    @open-add-service.window="openAddServiceModal()"

    x-cloak
>
    {{ $slot }}
</div>

<style>
[x-cloak] {
    display: none !important;
}
</style>
