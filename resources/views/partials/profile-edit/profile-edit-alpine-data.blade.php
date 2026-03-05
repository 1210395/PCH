<div class="min-h-screen bg-gray-50" x-data="{
    activeTab: 'profile',
    saving: false,
    form: @js([
        'name' => $designer->name,
        'title' => $designer->title ?? '',
        'bio' => $designer->bio ?? '',
        'email' => $designer->email,
        'phone' => $designer->phone_number ?? '',
        'city' => $designer->city ?? '',
        'address' => $designer->address ?? '',
        'website' => $designer->website ?? '',
        'avatarPath' => null,
        'avatarPreview' => null,
        'avatarUploading' => false,
        'coverPath' => null,
        'coverPreview' => null,
        'coverUploading' => false
    ]),
    skills: @js(($designer->skills ?? collect())->pluck('name')->toArray()),
    selectedSkill: '',
    customSkill: '',
    skillOptions: [
        'Graphic Design', 'UX/UI Design', 'Web Development', 'Mobile Development',
        'Photography', 'Videography', '3D Modeling', 'Animation',
        'Branding', 'Marketing', 'Content Writing', 'Social Media',
        'Project Management', 'Business Development', 'Woodworking', 'Metalworking',
        'AutoCAD', 'Revit', 'SketchUp', 'Rhino', 'ArchiCAD', 'V-Ray', '3ds Max',
        'Architectural Drawing', 'Building Design', 'Site Planning', 'Construction Documentation',
        'Sustainable Design', 'LEED Certification', 'Building Codes', 'Structural Design',
        'Oil Painting', 'Acrylic Painting', 'Watercolor', 'Drawing', 'Sketching', 'Charcoal',
        'Sculpture', 'Clay Modeling', 'Ceramics', 'Pottery', 'Glassblowing', 'Glass Art',
        'Printmaking', 'Screen Printing', 'Etching', 'Lithography', 'Relief Printing',
        'Mixed Media', 'Collage', 'Assemblage', 'Installation Art', 'Public Art',
        'Digital Art', 'Digital Painting', 'Photoshop', 'Illustrator', 'Procreate', 'Corel Painter',
        'Fine Art Photography', 'Darkroom', 'Photo Editing', 'Textile Art', 'Weaving', 'Fiber Art',
        'Mural Painting', 'Street Art', 'Graffiti', 'Spray Paint', 'Concept Art', 'Art Theory',
        'Color Theory', 'Composition', 'Figure Drawing', 'Portrait Art', 'Abstract Art', 'Realism'
    ],
    projects: @js($projectsData),
    products: @js($productsData),
    services: @js($servicesData),
    projectModal: false,
    projectForm: {id: null, title: '', description: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false},
    projectSubmitting: false,
    productModal: false,
    productForm: {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false},
    productSubmitting: false,
    serviceModal: false,
    serviceForm: {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false},
    serviceSubmitting: false,
    uploadSession: '',
    init() {
        // Generate upload session ID
        this.uploadSession = this.generateUUID();
    },
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
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
                alert('{{ __("Skill name is too long. Maximum 50 characters allowed.") }}');
                return;
            }
            skillToAdd = trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
        }
        const skillLowerCase = skillToAdd.toLowerCase();
        const isDuplicate = this.skills.some(s => s.toLowerCase() === skillLowerCase);
        if (skillToAdd && !isDuplicate) {
            this.skills.push(skillToAdd);
            this.selectedSkill = '';
            this.customSkill = '';
        } else if (skillToAdd && isDuplicate) {
            alert('{{ __("This skill has already been added!") }}');
        }
    },
    removeSkill(skill) {
        this.skills = this.skills.filter(s => s !== skill);
    },
    saveProfile() {
        this.saving = true;
        fetch("{{ url(app()->getLocale()) }}/profile/update", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: this.form.name,
                title: this.form.title,
                bio: this.form.bio,
                phone: this.form.phone,
                city: this.form.city,
                address: this.form.address,
                website: this.form.website,
                skills: this.skills.join(','),
                avatar: this.form.avatarPath,
                cover_image: this.form.coverPath
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __("Profile updated successfully!") }}');
                window.location.href = "{{ url(app()->getLocale()) }}/profile";
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred while saving") }}');
        })
        .finally(() => {
            this.saving = false;
        });
    },
    openProjectModal(project = null) {
        if (project) {
            this.projectForm = {
                id: project.id,
                title: project.title,
                description: project.description,
                role: project.role,
                customRole: '',
                images: [],
                imagePaths: [],
                uploading: false
            };

            // Load existing images
            if (project.image_paths && project.image_paths.length > 0) {
                this.projectForm.imagePaths = [...project.image_paths];
                this.projectForm.images = project.image_paths.map(path => ({
                    preview: path
                }));
            }
        } else {
            this.projectForm = {id: null, title: '', description: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false};
        }
        this.projectModal = true;
    },
    closeProjectModal() {
        this.projectModal = false;
        this.projectForm = {id: null, title: '', description: '', role: '', customRole: '', images: [], imagePaths: [], uploading: false};
    },
    editProject(project) {
        this.openProjectModal(project);
    },
    saveProject() {
        if (!this.projectForm.title || !this.projectForm.description || !this.projectForm.role) {
            alert('{{ __("Please fill in all required fields") }}');
            return;
        }
        this.projectSubmitting = true;
        const url = this.projectForm.id
            ? "{{ url(app()->getLocale()) }}/projects/" + this.projectForm.id
            : "{{ url(app()->getLocale()) }}/projects";
        const formData = new FormData();
        if (this.projectForm.id) formData.append('_method', 'PUT');
        formData.append('title', this.projectForm.title);
        formData.append('description', this.projectForm.description);
        formData.append('role', this.projectForm.role);

        // Add image paths
        if (this.projectForm.imagePaths && this.projectForm.imagePaths.length > 0) {
            this.projectForm.imagePaths.forEach((path, index) => {
                formData.append(`image_paths[${index}]`, path);
            });
        }
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
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('HTTP ' + response.status + ': ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const projectData = {
                    id: data.project.id,
                    title: data.project.title,
                    description: data.project.description,
                    role: data.project.role,
                    image_paths: (data.project.images || []).map(img => '{{ str_replace("'", "\\'", asset("storage")) }}/' + img.image_path)
                };
                if (this.projectForm.id) {
                    const index = this.projects.findIndex(p => p.id === this.projectForm.id);
                    if (index !== -1) {
                        this.projects[index] = projectData;
                    }
                } else {
                    this.projects.push(projectData);
                }
                this.closeProjectModal();
                alert(data.message);
                // Reload page to get fresh data
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred:") }} ' + error.message);
        })
        .finally(() => {
            this.projectSubmitting = false;
        });
    },
    deleteProject(id, index) {
        if (!confirm('{{ __("Are you sure you want to delete this project?") }}')) return;
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch("{{ url(app()->getLocale()) }}/projects/" + id, {
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
                this.projects.splice(index, 1);
                alert(data.message);
                // Reload page to get fresh data
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    },
    openProductModal(product = null) {
        if (product) {
            this.productForm = {
                id: product.id,
                name: product.name,
                description: product.description,
                category: product.category,
                customCategory: '',
                images: [],
                imagePaths: [],
                uploading: false
            };

            // Load existing images
            if (product.image_paths && product.image_paths.length > 0) {
                this.productForm.imagePaths = [...product.image_paths];
                this.productForm.images = product.image_paths.map(path => ({
                    preview: path
                }));
            }
        } else {
            this.productForm = {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false};
        }
        this.productModal = true;
    },
    closeProductModal() {
        this.productModal = false;
        this.productForm = {id: null, name: '', description: '', category: '', customCategory: '', images: [], imagePaths: [], uploading: false};
    },
    editProduct(product) {
        this.openProductModal(product);
    },
    saveProduct() {
        if (!this.productForm.name || !this.productForm.description || !this.productForm.category) {
            alert('{{ __("Please fill in all required fields") }}');
            return;
        }
        this.productSubmitting = true;
        const url = this.productForm.id
            ? "{{ url(app()->getLocale()) }}/products/" + this.productForm.id
            : "{{ url(app()->getLocale()) }}/products";
        const formData = new FormData();
        if (this.productForm.id) formData.append('_method', 'PUT');
        formData.append('name', this.productForm.name);
        formData.append('description', this.productForm.description);
        formData.append('category', this.productForm.category);

        // Add image paths
        if (this.productForm.imagePaths && this.productForm.imagePaths.length > 0) {
            this.productForm.imagePaths.forEach((path, index) => {
                formData.append(`image_paths[${index}]`, path);
            });
        }
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
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error('HTTP ' + response.status + ': ' + text);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const productData = {
                    id: data.product.id,
                    name: data.product.title,
                    description: data.product.description,
                    category: data.product.category,
                    image_paths: (data.product.images || []).map(img => '{{ str_replace("'", "\\'", asset("storage")) }}/' + img.image_path)
                };
                if (this.productForm.id) {
                    const index = this.products.findIndex(p => p.id === this.productForm.id);
                    if (index !== -1) {
                        this.products[index] = productData;
                    }
                } else {
                    this.products.push(productData);
                }
                this.closeProductModal();
                alert(data.message);
                // Reload page to get fresh data
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred:") }} ' + error.message);
        })
        .finally(() => {
            this.productSubmitting = false;
        });
    },
    deleteProduct(id, index) {
        if (!confirm('{{ __("Are you sure you want to delete this product?") }}')) return;
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch("{{ url(app()->getLocale()) }}/products/" + id, {
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
                this.products.splice(index, 1);
                alert(data.message);
                // Reload page to get fresh data
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    },
    openServiceModal(service = null) {
        if (service) {
            this.serviceForm = { ...service };
        } else {
            this.serviceForm = {id: null, name: '', description: '', category: '', customCategory: ''};
        }
        this.serviceModal = true;
    },
    closeServiceModal() {
        this.serviceModal = false;
        this.serviceForm = {id: null, name: '', description: '', category: '', customCategory: '', imagePath: null, imagePreview: null, uploading: false};
    },
    editService(service) {
        this.openServiceModal(service);
    },
    saveService() {
        if (!this.serviceForm.name || !this.serviceForm.description || !this.serviceForm.category) {
            alert('{{ __("Please fill in all required fields") }}');
            return;
        }
        this.serviceSubmitting = true;
        const url = this.serviceForm.id
            ? "{{ url(app()->getLocale()) }}/services/" + this.serviceForm.id
            : "{{ url(app()->getLocale()) }}/services";
        const formData = new FormData();
        if (this.serviceForm.id) formData.append('_method', 'PUT');
        formData.append('name', this.serviceForm.name);
        formData.append('description', this.serviceForm.description);
        formData.append('category', this.serviceForm.category);

        // Add image path
        if (this.serviceForm.imagePath) {
            formData.append('image_path', this.serviceForm.imagePath);
        }
        fetch(url, {
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
                if (this.serviceForm.id) {
                    const index = this.services.findIndex(s => s.id === this.serviceForm.id);
                    if (index !== -1) {
                        this.services[index] = {...this.serviceForm};
                    }
                } else {
                    this.services.push({...data.service});
                }
                this.closeServiceModal();
                alert(data.message);
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        })
        .finally(() => {
            this.serviceSubmitting = false;
        });
    },
    deleteService(id, index) {
        if (!confirm('{{ __("Are you sure you want to delete this service?") }}')) return;
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        fetch("{{ url(app()->getLocale()) }}/services/" + id, {
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
                this.services.splice(index, 1);
                alert(data.message);
            } else {
                alert('{{ __("Error:") }} ' + (data.message || '{{ __("Unknown error") }}'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred") }}');
        });
    },
    closeAllModals() {
        this.projectModal = false;
        this.productModal = false;
        this.serviceModal = false;
    },

    // ========================================
    // IMAGE UPLOAD FUNCTIONS
    // ========================================

    validateImageFile(file) {
        // Check file size - 5MB max
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            return {
                valid: false,
                error: '{{ __("File size is") }} ' + fileSizeMB + '{{ __("MB, which exceeds the 5MB limit.") }}' + '\n\n' + '{{ __("Please choose a smaller image or compress it before uploading.") }}'
            };
        }

        // Check file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            return {
                valid: false,
                error: '{{ __("Invalid file type. Only JPG, PNG, and GIF images are allowed.") }}'
            };
        }

        // Check file extension matches MIME type (prevent MIME spoofing)
        const extension = file.name.split('.').pop().toLowerCase();
        const validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!validExtensions.includes(extension)) {
            return {
                valid: false,
                error: '{{ __("Invalid file extension. Only .jpg, .jpeg, .png, and .gif files are allowed.") }}'
            };
        }

        // Verify MIME type matches extension
        const mimeExtensionMap = {
            'image/jpeg': ['jpg', 'jpeg'],
            'image/png': ['png'],
            'image/gif': ['gif']
        };

        const allowedExtensions = mimeExtensionMap[file.type] || [];
        if (!allowedExtensions.includes(extension)) {
            return {
                valid: false,
                error: '{{ __("File type mismatch. The file extension does not match the file type.") }}'
            };
        }

        return { valid: true };
    },

    async calculateFileHash(file) {
        // Simple hash using file name, size, and last modified
        const str = file.name + file.size + file.lastModified;
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return Math.abs(hash).toString(36);
    },

    async uploadImage(file, type, itemId = null) {
        // Calculate file hash for duplicate detection
        let fileHash = await this.calculateFileHash(file);
        if (type === 'product' || type === 'project') {
            // Add itemId to hash to make each product/project image unique
            fileHash = fileHash + '_' + (itemId || Date.now());
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', type);
        formData.append('session_id', this.uploadSession);
        formData.append('file_hash', fileHash);
        if (itemId) formData.append('item_id', itemId);

        try {
            const response = await fetch('{{ route("upload.registration.image", ["locale" => app()->getLocale()]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error('{{ __("Upload failed") }}');
            }

            const data = await response.json();
            if (data.success && data.path) {
                return data.path;
            } else {
                throw new Error(data.message || '{{ __("Upload failed") }}');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('{{ __("Failed to upload image:") }} ' + error.message);
            return null;
        }
    },

    async handleProjectImageUpload(event) {
        try {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            // Check limit
            const currentCount = this.projectForm.images ? this.projectForm.images.length : 0;
            const remainingSlots = 6 - currentCount;
            if (files.length > remainingSlots) {
                alert('{{ __("You can only upload") }} ' + remainingSlots + ' {{ __("more image(s). Maximum is 6 images per project.") }}');
                event.target.value = '';
                return;
            }

            this.projectForm.uploading = true;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Validate file
                const validation = this.validateImageFile(file);
                if (!validation.valid) {
                    alert(validation.error);
                    continue;
                }

                // Upload to server
                const uploadedPath = await this.uploadImage(file, 'project', 'temp_' + Date.now());

                if (uploadedPath) {
                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (!this.projectForm.images) this.projectForm.images = [];
                        this.projectForm.images.push({
                            preview: e.target.result
                        });
                    };
                    reader.onerror = () => {
                        console.error('Failed to read file');
                    };
                    reader.readAsDataURL(file);
                    if (!this.projectForm.imagePaths) this.projectForm.imagePaths = [];
                    this.projectForm.imagePaths.push(uploadedPath);
                }
            }

            this.projectForm.uploading = false;
            event.target.value = '';
        } catch (error) {
            console.error('Error in handleProjectImageUpload:', error);
            this.projectForm.uploading = false;
            alert('{{ __("An error occurred while uploading images. Please try again.") }}');
        }
    },

    removeProjectImage(index) {
        this.projectForm.images.splice(index, 1);
        this.projectForm.imagePaths.splice(index, 1);
    },

    async handleProductImageUpload(event) {
        try {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            // Check limit
            const currentCount = this.productForm.images ? this.productForm.images.length : 0;
            const remainingSlots = 6 - currentCount;
            if (files.length > remainingSlots) {
                alert('{{ __("You can only upload") }} ' + remainingSlots + ' {{ __("more image(s). Maximum is 6 images per product.") }}');
                event.target.value = '';
                return;
            }

            this.productForm.uploading = true;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Validate file
                const validation = this.validateImageFile(file);
                if (!validation.valid) {
                    alert(validation.error);
                    continue;
                }

                // Upload to server
                const uploadedPath = await this.uploadImage(file, 'product', 'temp_' + Date.now());

                if (uploadedPath) {
                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (!this.productForm.images) this.productForm.images = [];
                        this.productForm.images.push({
                            preview: e.target.result
                        });
                    };
                    reader.onerror = () => {
                        console.error('Failed to read file');
                    };
                    reader.readAsDataURL(file);
                    if (!this.productForm.imagePaths) this.productForm.imagePaths = [];
                    this.productForm.imagePaths.push(uploadedPath);
                }
            }

            this.productForm.uploading = false;
            event.target.value = '';
        } catch (error) {
            console.error('Error in handleProductImageUpload:', error);
            this.productForm.uploading = false;
            alert('{{ __("An error occurred while uploading images. Please try again.") }}');
        }
    },

    removeProductImage(index) {
        this.productForm.images.splice(index, 1);
        this.productForm.imagePaths.splice(index, 1);
    },

    async handleServiceImageUpload(event) {
        try {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                alert(validation.error);
                event.target.value = '';
                return;
            }

            this.serviceForm.uploading = true;

            // Upload to server
            const uploadedPath = await this.uploadImage(file, 'service', 'temp_' + Date.now());

            if (uploadedPath) {
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.serviceForm.imagePreview = e.target.result;
                };
                reader.onerror = () => {
                    console.error('Failed to read file');
                };
                reader.readAsDataURL(file);
                this.serviceForm.imagePath = uploadedPath;
            }

            this.serviceForm.uploading = false;
            event.target.value = '';
        } catch (error) {
            console.error('Error in handleServiceImageUpload:', error);
            this.serviceForm.uploading = false;
            alert('{{ __("An error occurred while uploading image. Please try again.") }}');
        }
    },

    removeServiceImage() {
        this.serviceForm.imagePreview = null;
        this.serviceForm.imagePath = null;
    },

    async handleAvatarUpload(event) {
        try {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                alert(validation.error);
                event.target.value = '';
                return;
            }

            this.form.avatarUploading = true;

            // Upload to server
            const uploadedPath = await this.uploadImage(file, 'avatar', 'designer_{{ $designer->id }}');

            if (uploadedPath) {
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.avatarPreview = e.target.result;
                };
                reader.onerror = () => {
                    console.error('Failed to read file');
                };
                reader.readAsDataURL(file);
                this.form.avatarPath = uploadedPath;
            }

            this.form.avatarUploading = false;
            event.target.value = '';
        } catch (error) {
            console.error('Error in handleAvatarUpload:', error);
            this.form.avatarUploading = false;
            alert('{{ __("An error occurred while uploading avatar. Please try again.") }}');
        }
    },

    async handleCoverUpload(event) {
        try {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file
            const validation = this.validateImageFile(file);
            if (!validation.valid) {
                alert(validation.error);
                event.target.value = '';
                return;
            }

            this.form.coverUploading = true;

            // Upload to server
            const uploadedPath = await this.uploadImage(file, 'cover', 'designer_{{ $designer->id }}');

            if (uploadedPath) {
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.coverPreview = e.target.result;
                };
                reader.onerror = () => {
                    console.error('Failed to read file');
                };
                reader.readAsDataURL(file);
                this.form.coverPath = uploadedPath;
            }

            this.form.coverUploading = false;
            event.target.value = '';
        } catch (error) {
            console.error('Error in handleCoverUpload:', error);
            this.form.coverUploading = false;
            alert('{{ __("An error occurred while uploading cover image. Please try again.") }}');
        }
    }
}" x-init="init()" @keydown.escape="closeAllModals()" x-cloak>
