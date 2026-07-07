@extends('layouts.admin')

@section('title', 'Settings - ThemVet Admin')

@section('content')
<div class="page-header">
    <h2>Settings</h2>
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 8px;">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="border-radius: 8px;">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settingsForm">
    @csrf

    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">
                <i class="fas fa-cog me-1"></i> General
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button">
                <i class="fas fa-search me-1"></i> SEO
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contact" type="button">
                <i class="fas fa-envelope me-1"></i> Contact
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-email" type="button">
                <i class="fas fa-server me-1"></i> Email
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exam" type="button">
                <i class="fas fa-clipboard-list me-1"></i> Exam
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-push" type="button">
                <i class="fas fa-bell me-1"></i> Push
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- GENERAL --}}
        <div class="tab-pane fade show active" id="tab-general">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'ThemVet' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Tagline</label>
                            <input type="text" name="site_tagline" class="form-control" value="{{ $settings['site_tagline'] ?? '' }}" placeholder="Your exam prep companion">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Description</label>
                            <input type="text" name="site_description" class="form-control" value="{{ $settings['site_description'] ?? '' }}" placeholder="Short description">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            @if(!empty($settings['logo']))
                                <img src="{{ asset('storage/' . $settings['logo']) }}" class="mt-2" style="height: 32px;">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*">
                            @if(!empty($settings['favicon']))
                                <img src="{{ asset('storage/' . $settings['favicon']) }}" class="mt-2" style="height: 24px;">
                            @endif
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="registration_enabled" value="1" {{ ($settings['registration_enabled'] ?? true) ? 'checked' : '' }} id="regCheck">
                                <label class="form-check-label" for="regCheck">Allow Registration</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} id="maintCheck">
                                <label class="form-check-label" for="maintCheck">Maintenance Mode</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="tab-pane fade" id="tab-seo">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ $settings['meta_title'] ?? '' }}" placeholder="ThemVet - Mock Tests for SSC, Banking, NDA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="mock test, SSC, banking, NDA, exam prep">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2" placeholder="India's #1 community-powered mock test platform">{{ $settings['meta_description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CONTACT --}}
        <div class="tab-pane fade" id="tab-contact">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Address</label>
                            <input type="text" name="contact_address" class="form-control" value="{{ $settings['contact_address'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EMAIL --}}
        <div class="tab-pane fade" id="tab-email">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] ?? '' }}" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] ?? '587' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Username</label>
                            <input type="text" name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Password</label>
                            <input type="password" name="smtp_password" class="form-control" value="{{ $settings['smtp_password'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">From Email</label>
                            <input type="email" name="smtp_from_email" class="form-control" value="{{ $settings['smtp_from_email'] ?? '' }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium">From Name</label>
                            <input type="text" name="smtp_from_name" class="form-control" value="{{ $settings['smtp_from_name'] ?? 'ThemVet' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EXAM --}}
        <div class="tab-pane fade" id="tab-exam">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Default Duration (min)</label>
                            <input type="number" name="default_duration_minutes" class="form-control" value="{{ $settings['default_duration_minutes'] ?? 60 }}" min="5" max="180">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Default Neg. Marking</label>
                            <select name="default_negative_marking" class="form-select">
                                <option value="1" {{ ($settings['default_negative_marking'] ?? true) ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !($settings['default_negative_marking'] ?? true) ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Neg. Marks Value</label>
                            <input type="number" name="default_negative_marking_value" class="form-control" value="{{ $settings['default_negative_marking_value'] ?? 0.25 }}" step="0.01" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Passing %</label>
                            <input type="number" name="passing_percentage" class="form-control" value="{{ $settings['passing_percentage'] ?? 40 }}" min="0" max="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PUSH --}}
        <div class="tab-pane fade" id="tab-push">
            <div class="content-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Firebase Project ID</label>
                            <input type="text" name="firebase_project_id" class="form-control" value="{{ $settings['firebase_project_id'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Firebase Server Key</label>
                            <input type="password" name="firebase_key" class="form-control" value="{{ $settings['firebase_key'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Settings
        </button>
    </div>
</form>
@endsection
