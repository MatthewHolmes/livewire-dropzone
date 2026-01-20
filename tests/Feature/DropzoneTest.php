<?php

use Dasundev\LivewireDropzone\Http\Livewire\Dropzone;
use Illuminate\Http\UploadedFile;

it('renders successfully', function () {
    Livewire\Livewire::test(Dropzone::class)
        ->assertOk();
});

it('accepts and sets rules parameter correctly', function () {
    Livewire\Livewire::test(Dropzone::class, ['rules' => ['image,mimes:png,jpeg']])
        ->assertSet('rules', ['image,mimes:png,jpeg']);
});

it('accepts and sets multiple parameter correctly', function () {
    Livewire\Livewire::test(Dropzone::class, ['multiple' => true])
        ->assertSet('multiple', true);
});

it('can upload file', function () {
    $dropzone = Livewire\Livewire::test(Dropzone::class);

    $uuid = $dropzone->get('uuid');

    $dropzone
        ->set('upload', UploadedFile::fake()->image('foo.png'))
        ->assertDispatched("$uuid:fileAdded");
});

it('accepts and sets files parameter correctly', function () {
    $file1 = UploadedFile::fake()->image('file1.png');
    $file2 = UploadedFile::fake()->image('file2.jpg');

    $files = [
        [
            'name' => $file1->getClientOriginalName(),
            'path' => $file1->path(),
            'extension' => $file1->extension(),
            'temporaryUrl' => $file1->path(),
            'size' => $file1->getSize(),
            'tmpFilename' => $file1->getFilename(),
        ],
        [
            'name' => $file2->getClientOriginalName(),
            'path' => $file2->path(),
            'extension' => $file2->extension(),
            'temporaryUrl' => $file2->path(),
            'size' => $file2->getSize(),
            'tmpFilename' => $file2->getFilename(),
        ],
    ];

    Livewire\Livewire::test(Dropzone::class, ['files' => $files])
        ->assertSet('files', $files);
});

it('handles files with missing extension key via normalizedFiles', function () {
    $files = [
        [
            'name' => 'test-file.pdf',
            'tmpFilename' => 'abc123',
            // 'extension' is intentionally missing
        ],
    ];

    $component = Livewire\Livewire::test(Dropzone::class)
        ->set('files', $files);

    $normalizedFiles = $component->invade()->normalizedFiles();

    expect($normalizedFiles)->toHaveCount(1);
    expect($normalizedFiles[0]['extension'])->toBe('');
    expect($normalizedFiles[0]['name'])->toBe('test-file.pdf');
    expect($normalizedFiles[0]['size'])->toBe(0);
});

it('handles files with missing size key via normalizedFiles', function () {
    $files = [
        [
            'name' => 'document.pdf',
            'tmpFilename' => 'xyz789',
            'extension' => 'pdf',
            // 'size' is intentionally missing
        ],
    ];

    $component = Livewire\Livewire::test(Dropzone::class)
        ->set('files', $files);

    $normalizedFiles = $component->invade()->normalizedFiles();

    expect($normalizedFiles)->toHaveCount(1);
    expect($normalizedFiles[0]['size'])->toBe(0);
    expect($normalizedFiles[0]['extension'])->toBe('pdf');
});

it('handles minimal file arrays via normalizedFiles', function () {
    $files = [
        [
            'name' => 'minimal.txt',
        ],
    ];

    $component = Livewire\Livewire::test(Dropzone::class)
        ->set('files', $files);

    $normalizedFiles = $component->invade()->normalizedFiles();

    expect($normalizedFiles)->toHaveCount(1);
    expect($normalizedFiles[0])->toMatchArray([
        'tmpFilename' => '',
        'name' => 'minimal.txt',
        'extension' => '',
        'path' => '',
        'temporaryUrl' => null,
        'size' => 0,
        'description' => '',
    ]);
});

it('filters out invalid entries via normalizedFiles', function () {
    $files = [
        ['name' => 'valid.txt', 'tmpFilename' => 'valid123'],
        'not-an-array',
        null,
        ['name' => 'another-valid.txt', 'tmpFilename' => 'valid456'],
    ];

    $component = Livewire\Livewire::test(Dropzone::class)
        ->set('files', $files);

    $normalizedFiles = $component->invade()->normalizedFiles();

    expect($normalizedFiles)->toHaveCount(2);
    expect($normalizedFiles[0]['name'])->toBe('valid.txt');
    expect($normalizedFiles[1]['name'])->toBe('another-valid.txt');
});

it('filters invalid entries when removing files', function () {
    $files = [
        ['name' => 'file1.txt', 'tmpFilename' => 'tmp1'],
        'invalid-entry',
        ['name' => 'file2.txt', 'tmpFilename' => 'tmp2'],
    ];

    $component = Livewire\Livewire::test(Dropzone::class)
        ->set('files', $files);

    $uuid = $component->get('uuid');

    $component->dispatch("$uuid:fileRemoved", tmpFilename: 'tmp1');

    $remainingFiles = $component->get('files');

    // Should only have file2, invalid entries are filtered out
    expect($remainingFiles)->toHaveCount(1);
    expect($remainingFiles[0]['tmpFilename'])->toBe('tmp2');
});
