# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **livewire-dropzone**, a Laravel package that provides a Livewire component for drag-and-drop file uploads. It's built as a reusable package using Orchestra Testbench workbench for development and testing.

**Key modification:** This fork has been edited to allow for custom filename and description on uploading (see `resources/views/livewire/dropzone.blade.php:105-108`).

## Quick Reference

| File | Purpose |
|------|---------|
| `src/Http/Livewire/Dropzone.php` | Main Livewire component (182 lines) |
| `resources/views/livewire/dropzone.blade.php` | View with Alpine.js logic (223 lines) |
| `src/LivewireDropzoneServiceProvider.php` | Package registration |
| `tests/Feature/DropzoneTest.php` | Pest test suite |

## Development Commands

### Testing
```bash
composer test              # Run Pest test suite
```

### Code Quality
```bash
composer lint              # Run Laravel Pint (--dirty flag)
composer analyse           # Run PHPStan static analysis (level 5)
```

### Workbench (Development Server)
```bash
composer serve             # Build and start testbench development server
composer build             # Build workbench assets
```

### Styles (Tailwind CSS)
The package includes Tailwind-compiled styles in `resources/js/livewire-dropzone-styles/`:
```bash
cd resources/js/livewire-dropzone-styles
npm run dev                # Watch and compile styles
npm run build              # Build minified styles
```

## Architecture

### Core Component Structure

The package centers around a single Livewire component with tight Alpine.js integration:

**`src/Http/Livewire/Dropzone.php`** - Main Livewire component
- Uses `WithFileUploads` trait for temporary file handling
- Implements `#[Modelable]` for two-way binding of `$files` array
- Event-driven architecture using Livewire's `#[On]` attributes with UUID-scoped events
- Key events: `{uuid}:fileAdded`, `{uuid}:fileRemoved`, `{uuid}:uploadError`
- Files are stored as arrays with metadata: `tmpFilename`, `name`, `extension`, `path`, `temporaryUrl`, `size`, `description`

**Key methods:**
- `mount()` (line 52) - Initializes UUID, rules, multiple mode
- `updatedUpload()` (line 60) - Validates and processes uploads
- `handleUpload()` (line 87) - Dispatches fileAdded event with metadata
- `onFileAdded()` (line 103) - Event listener, merges file into `$files`
- `onFileRemoved()` (line 112) - Event listener, filters file from `$files`
- `mimes()` (line 136) - Computed property extracting MIME types from rules
- `maxFileSize()` (line 159) - Computed property extracting max size from rules

**`resources/views/livewire/dropzone.blade.php`** - Component view
- Contains inline Alpine.js logic in `@script` section (lines 126-219)
- Uses global `window.totalFileSize` to track uploads across multiple dropzone instances
- Handles drag-and-drop, file validation, and server upload size checking (10MB limit)
- Prefixed Tailwind classes (e.g., `dz-*`) to avoid conflicts
- **Custom description field** at lines 105-108

**Alpine.js functions** (in `@script` block):
- `validateFiles()` (line 135) - Entry point for file input change
- `checkFileSizes()` (line 159) - Validates individual and total file sizes
- `uploadFiles()` (line 148) - Calls Livewire upload methods
- `removeUpload()` (line 197) - Handles file removal with size tracking

### Event Flow

1. **File Upload**: User drops/selects file → Alpine validates → Livewire uploads → `updatedUpload()` → dispatches `{uuid}:fileAdded` event
2. **File Added**: Event received by Alpine → updates `window.totalFileSize` → Livewire listener `onFileAdded()` → adds to `$files` array
3. **File Removal**: Alpine `removeUpload()` → decrements `window.totalFileSize` → dispatches `{uuid}:fileRemoved` → Livewire `onFileRemoved()` filters array
4. **Validation Error**: Validation fails → dispatches `{uuid}:uploadError` → Alpine shows error message

### Package Registration

**`src/LivewireDropzoneServiceProvider.php`** - Service provider
- Registers the component as `<livewire:dropzone />` via `Livewire::component('dropzone', Dropzone::class)`
- Publishes views using Spatie Laravel Package Tools

### Workbench

The `workbench/` directory provides a test Laravel application for development:
- **`workbench/app/Livewire/Welcome.php`** - Example component using dropzone
- **`workbench/routes/web.php`** - Routes for testing
- **`workbench/resources/views/`** - Views including example usage

### Testing

Uses Pest PHP with Orchestra Testbench:
- **`tests/Feature/DropzoneTest.php`** - Component tests using `Livewire::test()`
- Tests rendering, parameter setting, file uploads, and event dispatching
- Uses `UploadedFile::fake()` for file upload simulation

**Current test coverage** (5 tests):
1. Component renders successfully
2. Rules parameter is set correctly
3. Multiple parameter is set correctly
4. File upload works and dispatches event
5. Files parameter initialization works

## Important Implementation Details

### File Size Tracking
- Global `window.totalFileSize` tracks cumulative upload size across ALL dropzone instances on the page
- **Hardcoded limit**: 10MB (set in blade at line 7: `maxSizeMB: 10`)
- Resets to 0 when all files are removed from any dropzone
- Individual file limit AND total upload limit both enforced

### Temporary File Handling
- Livewire automatically cleans up files older than 24 hours
- Component doesn't manually delete temporary files (see `src/Http/Livewire/Dropzone.php:116-119`)
- Parent components must handle permanent storage via the `wire:model="files"` binding

### UUID-Scoped Events
- Each dropzone instance generates a unique UUID on mount
- All Alpine ↔ Livewire events are scoped with this UUID to prevent cross-talk between multiple dropzones on the same page
- Event naming pattern: `{uuid}:eventName`

### Validation Rules
- Passed as `#[Locked]` property to prevent client-side tampering
- Applied via `rules()` method with dynamic field name (`upload` vs `upload.*`)
- Custom error messages in `$messages` property (lines 36-41)
- Computed properties (`mimes()`, `accept()`, `maxFileSize()`) extract metadata from rules

**Example rules:**
```php
:rules="['image', 'mimes:png,jpeg,gif', 'max:10240']"
```

## Known Issues & Gotchas

1. **10MB limit is hardcoded** - Change in `dropzone.blade.php:7` if needed
2. **Info bar text is static** - Lines 72-74 show "Up to 10 MB" and "PNG, JPEG, PDF, MP4" regardless of actual rules
3. **Global window.totalFileSize** - Shared across ALL dropzones on page; can cause issues with SPA navigation
4. **No Laravel 11 in CI** - Tests only run on Laravel 10 (see `.github/workflows/tests.yml`)
5. **Description field styling** - The custom description input (line 105-108) doesn't use `dz-` prefix for all classes

## Namespace Note

The package uses `Dasundev\LivewireDropzone` namespace (original author) despite being forked to `matthewholmes/livewire-dropzone`. This is intentional to maintain compatibility.

## Testing Pull Requests

When adding features, ensure:
1. Add Pest tests to `tests/Feature/DropzoneTest.php`
2. Run `composer test` and `composer analyse` before committing
3. Update documentation if behavior changes (per CONTRIBUTING.md requirements)

## Example Usage

```blade
<livewire:dropzone
    wire:model="files"
    :rules="['image', 'mimes:png,jpeg', 'max:10240']"
    :multiple="true"
/>
```

Accessing files in parent component:
```php
public array $files = [];

public function submit(): void
{
    foreach ($this->files as $file) {
        // $file['tmpFilename'] - Livewire temp filename
        // $file['name'] - Original filename
        // $file['path'] - Temp file path
        // $file['size'] - File size in bytes
        // $file['extension'] - File extension
        // $file['temporaryUrl'] - Preview URL (images only)
        // $file['description'] - User-entered description
    }
}
```
