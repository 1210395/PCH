@props(['designer', 'projectsData', 'productsData', 'servicesData'])

<script>
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ ERROR CAUGHT');
                console.error('Error object:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                alert('An error occurred while deleting: ' + error.message);
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

            fetch(`/{{ app()->getLocale() }}/projects/${this.currentItem.id}`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
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

            fetch(`/{{ app()->getLocale() }}/products/${this.currentItem.id}`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditService() {
            this.isSubmitting = true;

            fetch(`/{{ app()->getLocale() }}/services/${this.currentItem.id}`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditBio() {
            this.isSubmitting = true;
            fetch(`/{{ app()->getLocale() }}/designer/update-bio`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitEditSkills() {
            this.isSubmitting = true;
            fetch(`/{{ app()->getLocale() }}/designer/update-skills`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
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

            fetch(`/{{ app()->getLocale() }}/projects`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
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

            fetch(`/{{ app()->getLocale() }}/products`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        submitAddService() {
            this.isSubmitting = true;

            fetch(`/{{ app()->getLocale() }}/services`, {
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
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        handleImageUpload(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    this.uploadedImages.push(file);
                }
            });
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
                    alert('Skill name is too long. Maximum 50 characters allowed.');
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
                alert('This skill has already been added!');
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
