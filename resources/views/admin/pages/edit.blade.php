@extends('admin.layouts.app')

@section('title', $config['label'] . ' - ' . __('Edit Page'))

@section('breadcrumb')
    <a href="{{ route('admin.pages.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Pages') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ $config['label'] }}</span>
@endsection

@section('content')
<div class="p-6" x-data="pageEditor()">
    <div class="max-w-5xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="{{ $config['icon'] }} text-{{ $config['color'] }}-600 mr-2"></i>
                    {{ $config['label'] }}
                </h1>
                <p class="text-gray-500 mt-1">{{ __('Edit page content in English and Arabic') }}</p>
            </div>
            <div class="flex gap-2">
                <button @click="resetToDefaults()" type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                    <i class="fas fa-undo mr-1"></i> {{ __('Reset to Defaults') }}
                </button>
                <a href="{{ url(app()->getLocale() . '/' . str_replace('_', '-', $slug)) }}" target="_blank"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i> {{ __('Preview') }}
                </a>
                <button @click="savePage()" :disabled="saving" type="button"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm disabled:opacity-50">
                    <i class="fas fa-save mr-1"></i>
                    <span x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Changes') }}'"></span>
                </button>
            </div>
        </div>

        <!-- Page Title & Subtitle Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-{{ $config['color'] }}-600 to-{{ $config['color'] }}-500">
                <h2 class="text-lg font-semibold text-white">{{ __('Page Header') }}</h2>
                <p class="text-white/80 text-sm">{{ __('Title and subtitle shown at the top of the page') }}</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title (English)') }}</label>
                        <input type="text" x-model="pageData.title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Page title') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title (Arabic)') }}</label>
                        <input type="text" x-model="pageData.title_ar" dir="rtl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic" placeholder="عنوان الصفحة">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Subtitle (English)') }}</label>
                        <input type="text" x-model="pageData.subtitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Page subtitle') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Subtitle (Arabic)') }}</label>
                        <input type="text" x-model="pageData.subtitle_ar" dir="rtl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic" placeholder="النص الفرعي">
                    </div>
                </div>

                @if($slug !== 'sitemap')
                <!-- Hero Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hero Image') }}</label>
                    <div class="flex items-center gap-4">
                        <template x-if="pageData.hero_image">
                            <div class="relative">
                                <img :src="getImageUrl(pageData.hero_image)" class="h-24 w-40 object-cover rounded-lg border">
                                <button @click="removeHeroImage()" type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <label class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm border border-gray-300">
                            <i class="fas fa-upload mr-1"></i> {{ __('Upload Image') }}
                            <input type="file" accept="image/*" @change="uploadHeroImage($event)" class="hidden">
                        </label>
                    </div>
                </div>
                @endif

                @if(in_array($slug, ['terms', 'privacy', 'community_guidelines']))
                <!-- Last Updated -->
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Last Updated Date') }}</label>
                    <input type="date" x-model="pageData.last_updated" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                @endif
            </div>
        </div>

        @if($slug === 'support')
        <!-- Support-specific: Contact Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-600 to-green-500">
                <h2 class="text-lg font-semibold text-white">{{ __('Contact Information') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Support Email') }}</label>
                        <input type="email" x-model="pageData.contact_email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Support Phone') }}</label>
                        <input type="text" x-model="pageData.contact_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Intro Content (English)') }}</label>
                        <textarea x-model="pageData.content" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Intro Content (Arabic)') }}</label>
                        <textarea x-model="pageData.content_ar" rows="4" dir="rtl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support-specific: FAQ Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-600 to-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('FAQ Items') }}</h2>
                        <p class="text-white/80 text-sm">{{ __('Frequently asked questions') }}</p>
                    </div>
                    <button @click="addFaqItem()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add FAQ') }}
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(faq, index) in pageData.faq_items" :key="'faq-'+index">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-600">{{ __('FAQ') }} #<span x-text="index + 1"></span></span>
                            <button @click="removeFaqItem(index)" type="button" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Question (EN)') }}</label>
                                <input type="text" x-model="faq.question" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Question (AR)') }}</label>
                                <input type="text" x-model="faq.question_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Answer (EN)') }}</label>
                                <textarea x-model="faq.answer" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Answer (AR)') }}</label>
                                <textarea x-model="faq.answer_ar" rows="3" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="!pageData.faq_items || pageData.faq_items.length === 0">
                    <p class="text-gray-400 text-sm text-center py-4">{{ __('No FAQ items yet. Click "Add FAQ" to add one.') }}</p>
                </template>
            </div>
        </div>
        @endif

        @if($slug === 'about')
        <!-- About-specific: Content Sections -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Content Sections') }}</h2>
                        <p class="text-white/80 text-sm">{{ __('Mission, Vision, What We Do, etc.') }}</p>
                    </div>
                    <button @click="addSection()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Section') }}
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(section, index) in pageData.sections" :key="'section-'+index">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-600">{{ __('Section') }} #<span x-text="index + 1"></span></span>
                            <div class="flex gap-2">
                                <button @click="moveSection(index, -1)" x-show="index > 0" type="button" class="p-1.5 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button @click="moveSection(index, 1)" x-show="index < pageData.sections.length - 1" type="button" class="p-1.5 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button @click="removeSection(index)" type="button" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Section Title (EN)') }}</label>
                                <input type="text" x-model="section.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Section Title (AR)') }}</label>
                                <input type="text" x-model="section.title_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Content (EN)') }}</label>
                                <textarea x-model="section.content" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Content (AR)') }}</label>
                                <textarea x-model="section.content_ar" rows="4" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic"></textarea>
                            </div>
                        </div>
                        <!-- Section Image -->
                        <div class="mt-3">
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Section Image (optional)') }}</label>
                            <div class="flex items-center gap-3">
                                <template x-if="section.image">
                                    <div class="relative">
                                        <img :src="getImageUrl(section.image)" class="h-16 w-24 object-cover rounded border">
                                        <button @click="section.image = null" type="button" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    </div>
                                </template>
                                <label class="cursor-pointer px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-xs border border-gray-300">
                                    <i class="fas fa-image mr-1"></i> {{ __('Upload') }}
                                    <input type="file" accept="image/*" @change="uploadSectionImage($event, index)" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- About-specific: Team Members -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Team Members') }}</h2>
                        <p class="text-white/80 text-sm">{{ __('Optional team section') }}</p>
                    </div>
                    <button @click="addTeamMember()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Member') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Team Section Title (EN)') }}</label>
                        <input type="text" x-model="pageData.team_title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Team Section Title (AR)') }}</label>
                        <input type="text" x-model="pageData.team_title_ar" dir="rtl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic">
                    </div>
                </div>
                <div class="space-y-3">
                    <template x-for="(member, index) in pageData.team_members" :key="'team-'+index">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <template x-if="member.image">
                                    <div class="relative">
                                        <img :src="getImageUrl(member.image)" class="w-16 h-16 rounded-full object-cover border">
                                        <button @click="member.image = null" type="button" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="!member.image">
                                    <label class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center cursor-pointer hover:bg-gray-300 transition-colors">
                                        <i class="fas fa-camera text-gray-400"></i>
                                        <input type="file" accept="image/*" @change="uploadTeamImage($event, index)" class="hidden">
                                    </label>
                                </template>
                            </div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Name (EN)') }}</label>
                                    <input type="text" x-model="member.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Name (AR)') }}</label>
                                    <input type="text" x-model="member.name_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Role (EN)') }}</label>
                                    <input type="text" x-model="member.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Role (AR)') }}</label>
                                    <input type="text" x-model="member.role_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic">
                                </div>
                            </div>
                            <button @click="removeTeamMember(index)" type="button" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </template>
                    <template x-if="!pageData.team_members || pageData.team_members.length === 0">
                        <p class="text-gray-400 text-sm text-center py-4">{{ __('No team members yet. Click "Add Member" to add one.') }}</p>
                    </template>
                </div>
            </div>
        </div>
        @endif

        @if(in_array($slug, ['community_guidelines', 'terms', 'privacy', 'accessibility', 'sitemap']))
        <!-- Generic Content Sections -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-{{ $config['color'] }}-600 to-{{ $config['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Content Sections') }}</h2>
                        <p class="text-white/80 text-sm">{{ __('Add and manage content sections') }}</p>
                    </div>
                    <button @click="addSection()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Section') }}
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(section, index) in pageData.sections" :key="'section-'+index">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-600">{{ __('Section') }} #<span x-text="index + 1"></span></span>
                            <div class="flex gap-2">
                                <button @click="moveSection(index, -1)" x-show="index > 0" type="button" class="p-1.5 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button @click="moveSection(index, 1)" x-show="index < pageData.sections.length - 1" type="button" class="p-1.5 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button @click="removeSection(index)" type="button" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Section Title (EN)') }}</label>
                                <input type="text" x-model="section.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Section Title (AR)') }}</label>
                                <input type="text" x-model="section.title_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Content (EN)') }}</label>
                                <textarea x-model="section.content" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Content (AR)') }}</label>
                                <textarea x-model="section.content_ar" rows="5" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="!pageData.sections || pageData.sections.length === 0">
                    <p class="text-gray-400 text-sm text-center py-4">{{ __('No sections yet. Click "Add Section" to add one.') }}</p>
                </template>
            </div>
        </div>
        @endif

        <!-- Bottom Save Button -->
        <div class="flex justify-end gap-2">
            <button @click="resetToDefaults()" type="button"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-undo mr-1"></i> {{ __('Reset to Defaults') }}
            </button>
            <button @click="savePage()" :disabled="saving" type="button"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                <i class="fas fa-save mr-1"></i>
                <span x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Changes') }}'"></span>
            </button>
        </div>
    </div>
</div>

<script>
function pageEditor() {
    return {
        pageData: @json($content),
        saving: false,
        slug: '{{ $slug }}',

        getImageUrl(path) {
            if (!path) return '';
            if (path.startsWith('http')) return path;
            return '{{ url("storage") }}/' + path;
        },

        async savePage() {
            this.saving = true;
            try {
                const response = await adminFetch('{{ route("admin.pages.update", ["locale" => app()->getLocale(), "slug" => $slug]) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ content: this.pageData })
                });
                const data = await response.json();
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || '{{ __("Error saving page") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("Error saving page") }}', 'error');
                console.error(error);
            } finally {
                this.saving = false;
            }
        },

        async resetToDefaults() {
            if (!confirm('{{ __("Are you sure you want to reset this page to default content? All custom content will be lost.") }}')) return;
            try {
                const response = await adminFetch('{{ route("admin.pages.reset", ["locale" => app()->getLocale(), "slug" => $slug]) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await response.json();
                if (data.success) {
                    this.pageData = data.data;
                    showToast(data.message, 'success');
                }
            } catch (error) {
                showToast('{{ __("Error resetting page") }}', 'error');
            }
        },

        async uploadHeroImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('image', file);
            formData.append('section', 'hero');
            try {
                const response = await fetch('{{ route("admin.pages.upload-image", ["locale" => app()->getLocale(), "slug" => $slug]) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.pageData.hero_image = data.data.path;
                    showToast('{{ __("Image uploaded") }}', 'success');
                }
            } catch (error) {
                showToast('{{ __("Error uploading image") }}', 'error');
            }
            event.target.value = '';
        },

        removeHeroImage() {
            this.pageData.hero_image = null;
        },

        async uploadSectionImage(event, index) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('image', file);
            formData.append('section', 'section_' + index);
            try {
                const response = await fetch('{{ route("admin.pages.upload-image", ["locale" => app()->getLocale(), "slug" => $slug]) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.pageData.sections[index].image = data.data.path;
                    showToast('{{ __("Image uploaded") }}', 'success');
                }
            } catch (error) {
                showToast('{{ __("Error uploading image") }}', 'error');
            }
            event.target.value = '';
        },

        async uploadTeamImage(event, index) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('image', file);
            formData.append('section', 'team_' + index);
            try {
                const response = await fetch('{{ route("admin.pages.upload-image", ["locale" => app()->getLocale(), "slug" => $slug]) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.pageData.team_members[index].image = data.data.path;
                    showToast('{{ __("Image uploaded") }}', 'success');
                }
            } catch (error) {
                showToast('{{ __("Error uploading image") }}', 'error');
            }
            event.target.value = '';
        },

        addSection() {
            if (!this.pageData.sections) this.pageData.sections = [];
            this.pageData.sections.push({
                title: '', title_ar: '',
                content: '', content_ar: '',
                image: null
            });
        },

        removeSection(index) {
            if (confirm('{{ __("Remove this section?") }}')) {
                this.pageData.sections.splice(index, 1);
            }
        },

        moveSection(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.pageData.sections.length) return;
            const temp = this.pageData.sections[index];
            this.pageData.sections[index] = this.pageData.sections[newIndex];
            this.pageData.sections[newIndex] = temp;
            // Force reactivity
            this.pageData.sections = [...this.pageData.sections];
        },

        addFaqItem() {
            if (!this.pageData.faq_items) this.pageData.faq_items = [];
            this.pageData.faq_items.push({
                question: '', question_ar: '',
                answer: '', answer_ar: ''
            });
        },

        removeFaqItem(index) {
            if (confirm('{{ __("Remove this FAQ item?") }}')) {
                this.pageData.faq_items.splice(index, 1);
            }
        },

        addTeamMember() {
            if (!this.pageData.team_members) this.pageData.team_members = [];
            this.pageData.team_members.push({
                name: '', name_ar: '',
                role: '', role_ar: '',
                image: null
            });
        },

        removeTeamMember(index) {
            if (confirm('{{ __("Remove this team member?") }}')) {
                this.pageData.team_members.splice(index, 1);
            }
        }
    };
}
</script>
@endsection
