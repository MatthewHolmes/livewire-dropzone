# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **livewire-dropzone**, a Laravel package that provides a Livewire component for drag-and-drop file uploads. It's built as a reusable package using Orchestra Testbench workbench for development and testing.

**Key modification:** This fork has been edited to allow for custom filename and description on uploading (see `resources/views/livewire/dropzone.blade.php:106-109`).

## Development Commands

### Testing
```bash
composer test              # Run Pest test suite
```

### Code Quality
```bash
composer lint              # Run Laravel Pint (--dirty flag)
composer analyse           # Run PHPStan static analysis
```

### Workbench (Development Server)
```bash
composer serve             # Build and start testbench development server
composer build             # Build workbench assets
```

### Styles (Tailwind CSS)
The package includes Tailwind-compiled styles in `resources/js/livewire-dropzone-styles/`:
```bash
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

**`resources/views/livewire/dropzone.blade.php`** - Component view
- Contains inline Alpine.js logic in `@script` section
- Uses global `window.totalFileSize` to track uploads across multiple dropzone instances
- Handles drag-and-drop, file validation, and server upload size checking (10MB limit)
- Prefixed Tailwind classes (e.g., `dz-*`) to avoid conflicts

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

## Important Implementation Details

### File Size Tracking
- Global `window.totalFileSize` tracks cumulative upload size across ALL dropzone instances on the page
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
- Custom error messages in `$messages` property
- Computed properties (`mimes()`, `accept()`, `maxFileSize()`) extract metadata from rules

## Testing Pull Requests

When adding features, ensure:
1. Add Pest tests to `tests/Feature/DropzoneTest.php`
2. Run `composer test` and `composer analyse` before committing
3. Update documentation if behavior changes (per CONTRIBUTING.md requirements)
