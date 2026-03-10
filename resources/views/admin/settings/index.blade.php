@extends('admin.layouts.app')

@section('title', __('Site Settings'))

@section('content')
<div class="p-6 space-y-8">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Site Settings') }}</h1>
        <p class="text-gray-600">{{ __('Manage site-wide settings including header, footer, and hero images. Use the order field to sort items.') }}</p>
    </div>

    {{-- Header Navigation Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="headerSettings()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-pink-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Header Navigation') }}</h2>
                    <p class="text-white/80 text-sm">{{ __('Manage navigation links in the site header. Use order numbers to sort.') }}</p>
                </div>
                <button @click="resetToDefaults()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                    {{ __('Reset to Defaults') }}
                </button>
            </div>
        </div>

        <div class="p-6">
            {{-- Navigation Links --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Navigation Links') }}</h3>
                    <button @click="addNavLink()" type="button" class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Link') }}
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(link, index) in navLinks" :key="'nav-'+index">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                {{-- Order Input --}}
                                <div class="w-16">
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Order') }}</label>
                                    <input type="number" x-model.number="link.order" min="1" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="1">
                                </div>
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Type') }}</label>
                                        <select x-model="link.type" @change="onLinkTypeChange(link)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                            <option value="link">{{ __('Link') }}</option>
                                            <option value="dropdown">{{ __('Dropdown') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Title (EN)') }}</label>
                                        <input type="text" x-model="link.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="{{ __('Link Title') }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Title (AR)') }}</label>
                                        <input type="text" x-model="link.title_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-arabic" placeholder="عنوان الرابط">
                                    </div>
                                    <div x-show="link.type !== 'dropdown'">
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('URL Path') }}</label>
                                        <input type="text" x-model="link.url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="/path">
                                    </div>
                                    <div x-show="link.type !== 'dropdown'">
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Route Name (optional)') }}</label>
                                        <input type="text" x-model="link.route" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="route.name">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label x-show="link.type !== 'dropdown'" class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="link.highlight" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                        <span class="text-xs text-gray-600">{{ __('Highlight') }}</span>
                                    </label>
                                    <button @click="removeNavLink(index)" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Dropdown Children --}}
                            <div x-show="link.type === 'dropdown'" class="mt-4 ml-8 pl-4 border-l-2 border-purple-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-medium text-purple-600">{{ __('Dropdown Items') }}</span>
                                    <button @click="addDropdownChild(link)" type="button" class="px-2 py-1 bg-purple-100 text-purple-600 text-xs rounded hover:bg-purple-200 transition-colors">
                                        <i class="fas fa-plus mr-1"></i> {{ __('Add Item') }}
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(child, childIndex) in (link.children || [])" :key="'child-'+index+'-'+childIndex">
                                        <div class="flex items-center gap-2 p-2 bg-white rounded border border-gray-200">
                                            <div class="w-12">
                                                <input type="number" x-model.number="child.order" min="1" class="w-full px-1 py-1.5 border border-gray-300 rounded text-xs text-center" placeholder="#">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" x-model="child.title" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="{{ __('Item Title (EN)') }}">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" x-model="child.title_ar" dir="rtl" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm font-arabic" placeholder="عنوان العنصر (AR)">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" x-model="child.url" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="/url-path">
                                            </div>
                                            <button @click="removeDropdownChild(link, childIndex)" type="button" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <div x-show="!link.children || link.children.length === 0" class="text-xs text-gray-400 text-center py-2">
                                        {{ __('No dropdown items. Click "Add Item" to add one.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="navLinks.length === 0" class="text-center py-8 text-gray-500">
                    {{ __('No navigation links. Click "Add Link" to add one.') }}
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex justify-end">
                <button @click="saveHeader()" type="button" :disabled="saving" class="px-6 py-2.5 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save Header Settings') }}</span>
                    <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Subheader Navigation Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="subheaderSettings()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-600 to-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Subheader Navigation') }}</h2>
                    <p class="text-white/80 text-sm">{{ __('Secondary navigation bar for logged-in users. Hides on scroll down, shows on scroll up.') }}</p>
                </div>
                <button @click="resetToDefaults()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                    {{ __('Reset to Defaults') }}
                </button>
            </div>
        </div>

        <div class="p-6">
            {{-- Enable/Disable Toggle --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" x-model="enabled" class="w-5 h-5 text-teal-600 rounded focus:ring-teal-500">
                    <div>
                        <span class="font-medium text-gray-900">{{ __('Enable Subheader') }}</span>
                        <p class="text-sm text-gray-500">{{ __('Show subheader navigation bar for logged-in users (excludes guests)') }}</p>
                    </div>
                </label>
            </div>

            {{-- Navigation Links --}}
            <div class="space-y-4" x-show="enabled">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Navigation Links') }}</h3>
                    <button @click="addNavLink()" type="button" class="px-3 py-1.5 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Link') }}
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(link, index) in navLinks" :key="'subnav-'+index">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            {{-- Order Input --}}
                            <div class="w-16">
                                <label class="block text-xs text-gray-500 mb-1">{{ __('Order') }}</label>
                                <input type="number" x-model.number="link.order" min="1" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="1">
                            </div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Title (EN)') }}</label>
                                    <input type="text" x-model="link.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="{{ __('Link Title') }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Title (AR)') }}</label>
                                    <input type="text" x-model="link.title_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-arabic" placeholder="عنوان الرابط">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('URL Path') }}</label>
                                    <input type="text" x-model="link.url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="/path">
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="link.highlight" class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500">
                                    <span class="text-xs text-gray-600">{{ __('Highlight') }}</span>
                                </label>
                                <button @click="removeNavLink(index)" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="navLinks.length === 0" class="text-center py-8 text-gray-500">
                    {{ __('No navigation links. Click "Add Link" to add one.') }}
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex justify-end">
                <button @click="saveSubheader()" type="button" :disabled="saving" class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save Subheader Settings') }}</span>
                    <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Footer Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="footerSettings()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Footer Settings') }}</h2>
                    <p class="text-white/80 text-sm">{{ __('Manage footer content, links, and contact information. Use order numbers to sort.') }}</p>
                </div>
                <button @click="resetToDefaults()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                    {{ __('Reset to Defaults') }}
                </button>
            </div>
        </div>

        <div class="p-6 space-y-8">
            {{-- Description --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Site Description (EN)') }}</label>
                    <textarea x-model="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Enter site description for footer') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Site Description (AR)') }}</label>
                    <textarea x-model="description_ar" dir="rtl" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic" placeholder="أدخل وصف الموقع للتذييل"></textarea>
                </div>
            </div>

            {{-- Supporter Text --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Supporter/Sponsor Text (EN)') }}</label>
                    <textarea x-model="supporterText" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g. Platform supported by Global Communities...') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Supporter/Sponsor Text (AR)') }}</label>
                    <textarea x-model="supporterText_ar" dir="rtl" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-arabic" placeholder="مثال: المنصة مدعومة من..."></textarea>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Quick Links') }}</h3>
                    <button @click="addQuickLink()" type="button" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Link') }}
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(link, index) in quickLinks" :key="'quick-'+index">
                        <div class="flex items-center gap-3">
                            {{-- Order Input --}}
                            <input type="number" x-model.number="link.order" min="1" class="w-14 px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500" placeholder="1">
                            <input type="text" x-model="link.title" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ __('Title (EN)') }}">
                            <input type="text" x-model="link.title_ar" dir="rtl" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic" placeholder="العنوان (AR)">
                            <input type="text" x-model="link.url" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="/url-path">
                            <button @click="removeQuickLink(index)" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Resource Links --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Resource Links') }}</h3>
                    <button @click="addResourceLink()" type="button" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Link') }}
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(link, index) in resourceLinks" :key="'resource-'+index">
                        <div class="flex items-center gap-3">
                            {{-- Order Input --}}
                            <input type="number" x-model.number="link.order" min="1" class="w-14 px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500" placeholder="1">
                            <input type="text" x-model="link.title" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ __('Title (EN)') }}">
                            <input type="text" x-model="link.title_ar" dir="rtl" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic" placeholder="العنوان (AR)">
                            <input type="text" x-model="link.url" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="/url-path">
                            <button @click="removeResourceLink(index)" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Contact Information --}}
            <div>
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Contact Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ __('Address') }}</label>
                        <textarea x-model="contact.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ __('Street Address') }}"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ __('Email') }}</label>
                        <input type="email" x-model="contact.email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ __('Phone') }}</label>
                        <input type="text" x-model="contact.phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="+970 123 456 789">
                    </div>
                </div>
            </div>

            {{-- Social Links --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">{{ __('Social Media Links') }}</h3>
                    <button @click="addSocialLink()" type="button" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Social') }}
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(link, index) in socialLinks" :key="'social-'+index">
                        <div class="flex items-center gap-3">
                            {{-- Order Input --}}
                            <input type="number" x-model.number="link.order" min="1" class="w-14 px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500" placeholder="1">
                            <select x-model="link.platform" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="facebook">{{ __('Facebook') }}</option>
                                <option value="twitter">{{ __('Twitter/X') }}</option>
                                <option value="instagram">{{ __('Instagram') }}</option>
                                <option value="linkedin">{{ __('LinkedIn') }}</option>
                                <option value="youtube">{{ __('YouTube') }}</option>
                                <option value="tiktok">{{ __('TikTok') }}</option>
                            </select>
                            <input type="text" x-model="link.url" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="https://...">
                            <button @click="removeSocialLink(index)" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Copyright & Bottom Links --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Copyright Text (EN)') }}</label>
                        <input type="text" x-model="copyright" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="{{ __('© 2025 Your Company. All rights reserved.') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Copyright Text (AR)') }}</label>
                        <input type="text" x-model="copyright_ar" dir="rtl" class="w-full px-4 py-3 border border-gray-300 rounded-lg font-arabic" placeholder="© 2025 شركتك. جميع الحقوق محفوظة.">
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">{{ __('Bottom Links') }}</label>
                        <button @click="addBottomLink()" type="button" class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">
                            <i class="fas fa-plus"></i> {{ __('Add') }}
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(link, index) in bottomLinks" :key="'bottom-'+index">
                            <div class="flex items-center gap-2">
                                {{-- Order Input --}}
                                <input type="number" x-model.number="link.order" min="1" class="w-12 px-1 py-1.5 border border-gray-300 rounded text-sm text-center focus:ring-2 focus:ring-gray-400" placeholder="1">
                                <input type="text" x-model="link.title" class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="{{ __('Title (EN)') }}">
                                <input type="text" x-model="link.title_ar" dir="rtl" class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-arabic" placeholder="العنوان (AR)">
                                <input type="text" x-model="link.url" class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm" placeholder="/url">
                                <button @click="removeBottomLink(index)" type="button" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex justify-end">
                <button @click="saveFooter()" type="button" :disabled="saving" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-medium rounded-lg hover:from-blue-700 hover:to-green-600 transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save Footer Settings') }}</span>
                    <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Home Page Counters Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="counterSettings()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Home Page Counters') }}</h2>
                    <p class="text-white/80 text-sm">{{ __('Configure the statistics displayed on the discover/home page. Use order numbers to sort.') }}</p>
                </div>
                <button @click="resetToDefaults()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                    {{ __('Reset to Defaults') }}
                </button>
            </div>
        </div>

        <div class="p-6 space-y-8">
            {{-- Badge Counter (the one in the badge above headline) --}}
            <div>
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Badge Counter') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('This counter appears in the badge above the main headline: "Join X+ [label]"') }}</p>
                <div class="p-4 bg-gray-50 rounded-lg space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Counter Type') }}</label>
                            <select x-model="badgeCounter.type" @change="onBadgeTypeChange()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($availableCounterTypes as $key => $info)
                                    <option value="{{ $key }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1" x-text="availableTypes[badgeCounter.type]?.description || ''"></p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Display Label (EN)') }}</label>
                            <input type="text" x-model="badgeCounter.label" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ __('e.g. creative professionals') }}">
                            <p class="text-xs text-gray-400 mt-1">{{ __('Text shown after the number (e.g. "Join 94+ creative professionals")') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Display Label (AR)') }}</label>
                            <input type="text" x-model="badgeCounter.label_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic" placeholder="مثال: مبدعين محترفين">
                            <p class="text-xs text-gray-400 mt-1">{{ __('Arabic text shown after the number in Arabic mode') }}</p>
                        </div>
                        <div></div>
                    </div>
                    {{-- Sector filter for badge counter --}}
                    <div x-show="badgeCounter.type === 'designers_by_sector'" x-transition>
                        <label class="block text-xs text-gray-500 mb-2">{{ __('Select Sectors to Count') }}</label>
                        <select multiple x-model="badgeCounter.sectors" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" size="5">
                            @foreach($availableSectors as $sector)
                                <option value="{{ $sector['value'] }}">{{ $sector['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Hold Ctrl (Cmd on Mac) to select multiple sectors') }}</p>
                        <p x-show="badgeCounter.type === 'designers_by_sector' && (!badgeCounter.sectors || badgeCounter.sectors.length === 0)" class="text-xs text-amber-600 mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i> {{ __('Please select at least one sector') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Stats Counters (the 3 stats below CTAs) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ __('Stats Counters') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('These counters appear below the CTA buttons. You can add up to 6.') }}</p>
                    </div>
                    <button @click="addStatsCounter()" type="button" :disabled="statsCounters.length >= 6" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Counter') }}
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(counter, index) in statsCounters" :key="'stat-'+index">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                {{-- Order Input --}}
                                <div class="w-16">
                                    <label class="block text-xs text-gray-500 mb-1">{{ __('Order') }}</label>
                                    <input type="number" x-model.number="counter.order" min="1" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-indigo-500" placeholder="1">
                                </div>

                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Counter Type') }}</label>
                                        <select x-model="counter.type" @change="onCounterTypeChange(counter)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            @foreach($availableCounterTypes as $key => $info)
                                                <option value="{{ $key }}">{{ $info['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Label (EN)') }}</label>
                                        <input type="text" x-model="counter.label" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ __('e.g. Products') }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">{{ __('Label (AR)') }}</label>
                                        <input type="text" x-model="counter.label_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-arabic" placeholder="مثال: المنتجات">
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="removeStatsCounter(index)" type="button" :disabled="statsCounters.length <= 1" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Sector Multi-Select (shown when type is designers_by_sector) --}}
                            <div x-show="counter.type === 'designers_by_sector'" x-transition class="mt-3 ml-20">
                                <label class="block text-xs text-gray-500 mb-2">{{ __('Select Sectors to Count') }}</label>
                                <select multiple x-model="counter.sectors" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" size="5">
                                    @foreach($availableSectors as $sector)
                                        <option value="{{ $sector['value'] }}">{{ $sector['label'] }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Hold Ctrl (Cmd on Mac) to select multiple sectors') }}</p>
                                <p x-show="counter.type === 'designers_by_sector' && (!counter.sectors || counter.sectors.length === 0)" class="text-xs text-amber-600 mt-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ __('Please select at least one sector') }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="statsCounters.length === 0" class="text-center py-8 text-gray-500">
                    {{ __('No counters configured. Click "Add Counter" to add one.') }}
                </div>
            </div>

            {{-- Preview --}}
            <div class="border-t pt-6">
                <h3 class="font-medium text-gray-900 mb-4">{{ __('Preview') }}</h3>
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6">
                    {{-- Badge Preview --}}
                    <div class="flex justify-center mb-4">
                        <div class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="text-sm text-gray-700 font-medium">
                                {{ __('Join') }} <span class="text-blue-600 font-bold">XX+</span> <span x-text="badgeCounter.label"></span>
                            </span>
                        </div>
                    </div>
                    {{-- Stats Preview --}}
                    <div class="flex justify-center gap-8" :class="{'flex-wrap': statsCounters.length > 4}">
                        <template x-for="(counter, index) in statsCounters" :key="'preview-'+index">
                            <div class="text-center">
                                <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">XX+</div>
                                <div class="text-xs text-gray-600" x-text="counter.label"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <button @click="saveCounters()" type="button" :disabled="saving" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-500 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-purple-600 transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save Counter Settings') }}</span>
                    <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Registration Policies Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="registrationPoliciesSettings()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-600 to-teal-500">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Registration Policies') }}</h2>
                    <p class="text-white/80 text-sm">{{ __('Terms of Service and Policies shown during user registration') }}</p>
                </div>
                <button @click="resetToDefaults()" type="button" class="px-3 py-1.5 bg-white/20 text-white text-sm rounded-lg hover:bg-white/30 transition-colors">
                    {{ __('Reset to Defaults') }}
                </button>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Terms & Policies Content') }}</label>
                <p class="text-xs text-gray-500 mb-3">{{ __('This content is displayed to users when they click "View Policies" during registration. Use plain text with line breaks for formatting.') }}</p>
                <textarea x-model="content" rows="15" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm" placeholder="{{ __('Enter your terms and policies here...') }}"></textarea>
            </div>

            {{-- Preview --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('Preview') }}</h3>
                <div class="max-h-60 overflow-y-auto p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap" x-text="content"></div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <button @click="savePolicies()" type="button" :disabled="saving" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-500 text-white font-medium rounded-lg hover:from-emerald-700 hover:to-teal-600 transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('Save Policies') }}</span>
                    <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Hero Images Carousel Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-500 to-red-500">
            <h2 class="text-lg font-semibold text-white">{{ __('Hero Image Carousels') }}</h2>
            <p class="text-white/80 text-sm">{{ __('Upload up to 5 hero images per page. Images auto-rotate every 5 seconds. (Recommended: 1920x600px)') }}</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($heroImages as $page => $data)
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200" x-data="{
                    uploading: false,
                    removing: false,
                    images: @json($data['images'] ?? []),
                    currentIndex: 0,
                    maxImages: 5,

                    get canAddMore() {
                        return this.images.length < this.maxImages;
                    },

                    async uploadImage(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        if (!this.canAddMore) {
                            showToast('{{ __('Maximum 5 images allowed. Please remove an image first.') }}', 'error');
                            event.target.value = '';
                            return;
                        }

                        // Client-side validation
                        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
                        if (!validTypes.includes(file.type)) {
                            showToast('{{ __('Invalid file type. Please upload a JPG, PNG, GIF, or WebP image.') }}', 'error');
                            event.target.value = '';
                            return;
                        }
                        if (file.size > 10 * 1024 * 1024) {
                            showToast('{{ __('File too large. Maximum size is 10MB.') }}', 'error');
                            event.target.value = '';
                            return;
                        }

                        this.uploading = true;
                        const formData = new FormData();
                        formData.append('page', '{{ $page }}');
                        formData.append('image', file);

                        try {
                            const response = await fetch('{{ route('admin.settings.hero.update', ['locale' => app()->getLocale()]) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const result = await response.json();
                            if (result.success) {
                                this.images = result.data.images;
                                this.currentIndex = this.images.length - 1;
                                showToast(result.message, 'success');
                            } else {
                                // Show validation errors if present
                                if (result.errors) {
                                    const firstError = Object.values(result.errors).flat()[0];
                                    showToast(firstError || result.message || '{{ __('Failed to upload image') }}', 'error');
                                } else {
                                    showToast(result.message || '{{ __('Failed to upload image') }}', 'error');
                                }
                            }
                        } catch (error) {
                            showToast('{{ __('Failed to upload image. Please try again.') }}', 'error');
                        }
                        this.uploading = false;
                        event.target.value = '';
                    },

                    async removeImage(index) {
                        if (!confirm('{{ __('Are you sure you want to remove this image from the carousel?') }}')) return;

                        this.removing = true;
                        try {
                            const response = await fetch('{{ route('admin.settings.hero.remove', ['locale' => app()->getLocale()]) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ page: '{{ $page }}', index: index })
                            });

                            const result = await response.json();
                            if (result.success) {
                                this.images = result.data.images;
                                if (this.currentIndex >= this.images.length) {
                                    this.currentIndex = Math.max(0, this.images.length - 1);
                                }
                                showToast(result.message, 'success');
                            } else {
                                showToast(result.message || '{{ __('Failed to remove image') }}', 'error');
                            }
                        } catch (error) {
                            showToast('{{ __('Failed to remove image. Please try again.') }}', 'error');
                        }
                        this.removing = false;
                    },

                    selectImage(index) {
                        this.currentIndex = index;
                    }
                }">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $data['label'] }}</h3>
                            <p class="text-xs text-gray-500" x-text="images.length + '/5 {{ __('images') }}'"></p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-gray-200 text-gray-600">{{ $page }}</span>
                    </div>

                    {{-- Main Image Preview --}}
                    <div class="relative aspect-[16/6] bg-gray-200 rounded-lg overflow-hidden mb-3">
                        <template x-if="images.length > 0">
                            <img :src="images[currentIndex]?.url" class="w-full h-full object-cover" alt="{{ $data['label'] }} {{ __('hero') }}">
                        </template>
                        <template x-if="images.length === 0">
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">{{ __('No images uploaded') }}</p>
                                </div>
                            </div>
                        </template>

                        {{-- Remove Current Image Button --}}
                        <button x-show="images.length > 0 && !removing" @click="removeImage(currentIndex)" type="button" class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>

                        {{-- Navigation Arrows --}}
                        <button x-show="images.length > 1" @click="currentIndex = (currentIndex - 1 + images.length) % images.length" type="button" class="absolute left-2 top-1/2 -translate-y-1/2 p-1 bg-black/40 text-white rounded-full hover:bg-black/60 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button x-show="images.length > 1" @click="currentIndex = (currentIndex + 1) % images.length" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 bg-black/40 text-white rounded-full hover:bg-black/60 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Image Counter --}}
                        <div x-show="images.length > 0" class="absolute bottom-2 left-2 px-2 py-1 bg-black/50 text-white text-xs rounded">
                            <span x-text="(currentIndex + 1) + ' / ' + images.length"></span>
                        </div>

                        {{-- Upload/Remove Overlay --}}
                        <div x-show="uploading || removing" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 text-white animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p x-show="uploading" class="text-white text-xs mt-1">{{ __('Uploading...') }}</p>
                                <p x-show="removing" class="text-white text-xs mt-1">{{ __('Removing...') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Thumbnail Strip --}}
                    <div class="flex gap-2 mb-3 overflow-x-auto pb-1" x-show="images.length > 0">
                        <template x-for="(img, index) in images" :key="'thumb-{{ $page }}-'+index">
                            <button @click="selectImage(index)" type="button" class="flex-shrink-0 w-16 h-10 rounded overflow-hidden border-2 transition-colors" :class="currentIndex === index ? 'border-orange-500' : 'border-transparent hover:border-gray-300'">
                                <img :src="img.url" class="w-full h-full object-cover" alt="{{ __('Thumbnail') }}">
                            </button>
                        </template>

                        {{-- Add More Placeholder --}}
                        <template x-if="canAddMore">
                            <label class="flex-shrink-0 w-16 h-10 rounded border-2 border-dashed border-gray-300 hover:border-orange-400 flex items-center justify-center cursor-pointer transition-colors">
                                <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" @change="uploadImage($event)" class="hidden" :disabled="uploading || removing">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </label>
                        </template>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <label class="flex-1 cursor-pointer" :class="{'opacity-50 cursor-not-allowed': !canAddMore || uploading || removing}">
                            <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" @change="uploadImage($event)" class="hidden" :disabled="uploading || removing || !canAddMore">
                            <span class="block w-full text-center px-3 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors" :class="{'opacity-50 cursor-not-allowed': uploading || removing || !canAddMore}">
                                <span x-show="!uploading && !removing && canAddMore">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Add Image') }}
                                </span>
                                <span x-show="uploading">{{ __('Uploading...') }}</span>
                                <span x-show="removing">{{ __('Removing...') }}</span>
                                <span x-show="!uploading && !removing && !canAddMore">{{ __('Max 5 Images') }}</span>
                            </span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Hero Texts Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="heroTextsManager()">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-500 to-cyan-500">
            <h2 class="text-lg font-semibold text-white">{{ __('Page Hero Texts') }}</h2>
            <p class="text-white/80 text-sm">{{ __('Manage the title and subtitle text displayed on each page hero section (English & Arabic).') }}</p>
        </div>

        <div class="p-6">
            <div class="space-y-4">
                <template x-for="(page, key) in pages" :key="key">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900" x-text="page.label"></h3>
                            <div class="flex gap-2">
                                <button @click="resetPage(key)" type="button" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors">
                                    <i class="fas fa-undo mr-1"></i> {{ __('Reset') }}
                                </button>
                                <button @click="savePage(key)" type="button" class="px-3 py-1.5 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700 transition-colors" :disabled="page.saving">
                                    <span x-show="!page.saving"><i class="fas fa-save mr-1"></i> {{ __('Save') }}</span>
                                    <span x-show="page.saving">{{ __('Saving...') }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Title (English)') }}</label>
                                <input type="text" x-model="page.title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Title (Arabic)') }}</label>
                                <input type="text" x-model="page.title_ar" dir="rtl" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Subtitle (English)') }}</label>
                                <textarea x-model="page.subtitle" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Subtitle (Arabic)') }}</label>
                                <textarea x-model="page.subtitle_ar" dir="rtl" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>


<script>
function heroTextsManager() {
    const heroTexts = @json($heroTexts ?? []);
    const heroPageLabels = @json(collect($heroImages)->mapWithKeys(fn($data, $key) => [$key => $data['label']]));

    let pages = {};
    for (const [key, label] of Object.entries(heroPageLabels)) {
        pages[key] = {
            label: label,
            title: heroTexts[key]?.title || '',
            title_ar: heroTexts[key]?.title_ar || '',
            subtitle: heroTexts[key]?.subtitle || '',
            subtitle_ar: heroTexts[key]?.subtitle_ar || '',
            saving: false,
        };
    }

    return {
        pages: pages,

        async savePage(key) {
            this.pages[key].saving = true;
            try {
                const response = await fetch('{{ route("admin.settings.hero-texts.update", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        page: key,
                        title: this.pages[key].title,
                        title_ar: this.pages[key].title_ar,
                        subtitle: this.pages[key].subtitle,
                        subtitle_ar: this.pages[key].subtitle_ar,
                    })
                });
                const result = await response.json();
                showToast(result.message || 'Saved', result.success ? 'success' : 'error');
            } catch (e) {
                showToast('Failed to save', 'error');
            }
            this.pages[key].saving = false;
        },

        async resetPage(key) {
            if (!confirm('Reset hero texts for this page to defaults?')) return;
            try {
                const response = await fetch('{{ route("admin.settings.hero-texts.reset", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ page: key })
                });
                const result = await response.json();
                if (result.success) {
                    // Reload to get fresh defaults
                    location.reload();
                }
                showToast(result.message || 'Reset', result.success ? 'success' : 'error');
            } catch (e) {
                showToast('Failed to reset', 'error');
            }
        }
    };
}

function headerSettings() {
    return {
        navLinks: @json($headerSettings['nav_links'] ?? []),
        saving: false,

        init() {
            // Ensure each link has an order property and type
            this.navLinks.forEach((link, index) => {
                if (!link.order) link.order = index + 1;
                if (!link.type) link.type = 'link';
                if (link.type === 'dropdown' && !link.children) link.children = [];
            });
        },

        addNavLink() {
            const maxOrder = this.navLinks.reduce((max, link) => Math.max(max, link.order || 0), 0);
            this.navLinks.push({ title: '', title_ar: '', url: '/', route: '', highlight: false, order: maxOrder + 1, type: 'link', children: [] });
        },

        removeNavLink(index) {
            this.navLinks.splice(index, 1);
        },

        onLinkTypeChange(link) {
            if (link.type === 'dropdown') {
                if (!link.children) link.children = [];
                link.url = '';
                link.route = '';
                link.highlight = false;
            }
        },

        addDropdownChild(link) {
            if (!link.children) link.children = [];
            const maxOrder = link.children.reduce((max, child) => Math.max(max, child.order || 0), 0);
            link.children.push({ title: '', title_ar: '', url: '/', order: maxOrder + 1 });
        },

        removeDropdownChild(link, childIndex) {
            if (link.children) {
                link.children.splice(childIndex, 1);
            }
        },

        async saveHeader() {
            this.saving = true;
            try {
                // Sort by order before saving
                const sortedLinks = [...this.navLinks].sort((a, b) => (a.order || 0) - (b.order || 0));

                // Also sort children for dropdown items
                sortedLinks.forEach(link => {
                    if (link.type === 'dropdown' && link.children && link.children.length > 0) {
                        link.children = [...link.children].sort((a, b) => (a.order || 0) - (b.order || 0));
                    }
                });

                const response = await fetch('{{ route('admin.settings.header.update', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ nav_links: sortedLinks })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || '{{ __('Failed to save header settings') }}', 'error');
                }
            } catch (error) {
                console.error('Save header error:', error);
                showToast('{{ __('Failed to save header settings') }}', 'error');
            }
            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('{{ __('Are you sure you want to reset header settings to defaults?') }}')) return;

            try {
                const response = await fetch('{{ route('admin.settings.header.reset', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showToast(result.message || '{{ __('Failed to reset') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to reset') }}', 'error');
            }
        }
    };
}

function subheaderSettings() {
    const settings = @json($subheaderSettings);
    return {
        enabled: settings.enabled ?? true,
        navLinks: settings.nav_links || [],
        saving: false,

        init() {
            // Ensure each link has order and highlight properties
            this.navLinks.forEach((link, index) => {
                if (!link.order) link.order = index + 1;
                if (link.highlight === undefined) link.highlight = false;
            });
        },

        addNavLink() {
            const maxOrder = this.navLinks.reduce((max, link) => Math.max(max, link.order || 0), 0);
            this.navLinks.push({ title: '', title_ar: '', url: '/', highlight: false, order: maxOrder + 1 });
        },

        removeNavLink(index) {
            this.navLinks.splice(index, 1);
        },

        async saveSubheader() {
            this.saving = true;
            try {
                // Sort by order before saving
                const sortedLinks = [...this.navLinks].sort((a, b) => (a.order || 0) - (b.order || 0));

                const response = await fetch('{{ route('admin.settings.subheader.update', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        enabled: this.enabled,
                        nav_links: sortedLinks
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || '{{ __('Failed to save subheader settings') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to save subheader settings') }}', 'error');
            }
            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('{{ __('Are you sure you want to reset subheader settings to defaults?') }}')) return;

            try {
                const response = await fetch('{{ route('admin.settings.subheader.reset', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showToast(result.message || '{{ __('Failed to reset') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to reset') }}', 'error');
            }
        }
    };
}

function footerSettings() {
    const settings = @json($footerSettings);
    return {
        description: settings.description || '',
        description_ar: settings.description_ar || '',
        supporterText: settings.supporter_text || '',
        supporterText_ar: settings.supporter_text_ar || '',
        quickLinks: settings.quick_links || [],
        resourceLinks: settings.resource_links || [],
        contact: settings.contact || { address: '', email: '', phone: '' },
        socialLinks: settings.social_links || [],
        copyright: settings.copyright || '',
        copyright_ar: settings.copyright_ar || '',
        bottomLinks: settings.bottom_links || [],
        saving: false,

        init() {
            // Ensure each link has an order property
            const ensureOrder = (list) => {
                list.forEach((item, index) => {
                    if (!item.order) item.order = index + 1;
                });
            };
            ensureOrder(this.quickLinks);
            ensureOrder(this.resourceLinks);
            ensureOrder(this.socialLinks);
            ensureOrder(this.bottomLinks);
        },

        getNextOrder(list) {
            return list.reduce((max, item) => Math.max(max, item.order || 0), 0) + 1;
        },

        addQuickLink() {
            this.quickLinks.push({ title: '', title_ar: '', url: '#', order: this.getNextOrder(this.quickLinks) });
        },
        removeQuickLink(index) {
            this.quickLinks.splice(index, 1);
        },

        addResourceLink() {
            this.resourceLinks.push({ title: '', title_ar: '', url: '#', order: this.getNextOrder(this.resourceLinks) });
        },
        removeResourceLink(index) {
            this.resourceLinks.splice(index, 1);
        },

        addSocialLink() {
            this.socialLinks.push({ platform: 'facebook', url: '#', order: this.getNextOrder(this.socialLinks) });
        },
        removeSocialLink(index) {
            this.socialLinks.splice(index, 1);
        },

        addBottomLink() {
            this.bottomLinks.push({ title: '', title_ar: '', url: '#', order: this.getNextOrder(this.bottomLinks) });
        },
        removeBottomLink(index) {
            this.bottomLinks.splice(index, 1);
        },

        async saveFooter() {
            this.saving = true;
            try {
                // Sort all lists by order before saving
                const sortByOrder = (list) => [...list].sort((a, b) => (a.order || 0) - (b.order || 0));

                const response = await fetch('{{ route('admin.settings.footer.update', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        description: this.description,
                        description_ar: this.description_ar,
                        supporter_text: this.supporterText,
                        supporter_text_ar: this.supporterText_ar,
                        quick_links: sortByOrder(this.quickLinks),
                        resource_links: sortByOrder(this.resourceLinks),
                        contact: this.contact,
                        social_links: sortByOrder(this.socialLinks),
                        copyright: this.copyright,
                        copyright_ar: this.copyright_ar,
                        bottom_links: sortByOrder(this.bottomLinks)
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || '{{ __('Failed to save footer settings') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to save footer settings') }}', 'error');
            }
            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('{{ __('Are you sure you want to reset footer settings to defaults?') }}')) return;

            try {
                const response = await fetch('{{ route('admin.settings.footer.reset', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showToast(result.message || '{{ __('Failed to reset') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to reset') }}', 'error');
            }
        }
    };
}

function counterSettings() {
    const settings = @json($counterSettings);
    const types = @json($availableCounterTypes);

    return {
        badgeCounter: settings.badge_counter || { type: 'designers', label: 'creative professionals', label_ar: 'مبدعين محترفين', sectors: [] },
        statsCounters: settings.stats_counters || [],
        availableTypes: types,
        saving: false,

        init() {
            // Ensure each counter has required properties
            this.statsCounters.forEach((counter, index) => {
                if (!counter.order) counter.order = index + 1;
                if (!counter.sectors) counter.sectors = [];
            });
            // Ensure badge counter has sectors array
            if (!this.badgeCounter.sectors) this.badgeCounter.sectors = [];
        },

        addStatsCounter() {
            if (this.statsCounters.length < 6) {
                const maxOrder = this.statsCounters.reduce((max, c) => Math.max(max, c.order || 0), 0);
                this.statsCounters.push({ type: 'products', label: '{{ __('Products') }}', label_ar: '{{ __('المنتجات') }}', sectors: [], order: maxOrder + 1 });
            }
        },

        removeStatsCounter(index) {
            if (this.statsCounters.length > 1) {
                this.statsCounters.splice(index, 1);
            }
        },

        onBadgeTypeChange() {
            // Initialize sectors array when switching to designers_by_sector
            if (this.badgeCounter.type === 'designers_by_sector') {
                if (!this.badgeCounter.sectors) this.badgeCounter.sectors = [];
            } else {
                this.badgeCounter.sectors = [];
            }
        },

        onCounterTypeChange(counter) {
            // Initialize sectors array when switching to designers_by_sector
            if (counter.type === 'designers_by_sector') {
                if (!counter.sectors) counter.sectors = [];
            } else {
                counter.sectors = [];
            }
            // Auto-suggest label based on type
            const typeInfo = this.availableTypes[counter.type];
            if (typeInfo) {
                counter.label = typeInfo.label;
            }
        },

        async saveCounters() {
            this.saving = true;
            try {
                // Sort counters by order before saving
                const sortedCounters = [...this.statsCounters].sort((a, b) => (a.order || 0) - (b.order || 0));

                // Prepare badge counter data
                const badgeData = {
                    type: this.badgeCounter.type,
                    label: this.badgeCounter.label,
                    label_ar: this.badgeCounter.label_ar || '',
                    sectors: this.badgeCounter.sectors || []
                };

                // Prepare stats counters data
                const statsData = sortedCounters.map(c => ({
                    type: c.type,
                    label: c.label,
                    label_ar: c.label_ar || '',
                    sectors: c.sectors || [],
                    order: c.order || 0
                }));

                const response = await fetch('{{ route('admin.settings.counters.update', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        badge_counter: badgeData,
                        stats_counters: statsData
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    let errorMsg = result.message || '{{ __('Failed to save counter settings') }}';
                    if (result.errors) {
                        errorMsg = Object.values(result.errors).flat().join(', ');
                    }
                    showToast(errorMsg, 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to save counter settings') }}', 'error');
            }
            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('{{ __('Are you sure you want to reset counter settings to defaults?') }}')) return;

            try {
                const response = await fetch('{{ route('admin.settings.counters.reset', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showToast(result.message || '{{ __('Failed to reset') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to reset') }}', 'error');
            }
        }
    };
}

function registrationPoliciesSettings() {
    const settings = @json($registrationPolicies ?? ['content' => '']);
    return {
        content: (settings && settings.content) ? settings.content : '',
        saving: false,

        async savePolicies() {
            this.saving = true;
            try {
                const response = await fetch('{{ route('admin.settings.registration-policies.update', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        content: this.content
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || '{{ __('Failed to save policies') }}', 'error');
                }
            } catch (error) {
                console.error('Save policies error:', error);
                showToast('{{ __('Failed to save policies') }}', 'error');
            }
            this.saving = false;
        },

        async resetToDefaults() {
            if (!confirm('{{ __('Are you sure you want to reset registration policies to defaults?') }}')) return;

            try {
                const response = await fetch('{{ route('admin.settings.registration-policies.reset', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showToast(result.message || '{{ __('Failed to reset') }}', 'error');
                }
            } catch (error) {
                showToast('{{ __('Failed to reset') }}', 'error');
            }
        }
    };
}
</script>
@endsection
