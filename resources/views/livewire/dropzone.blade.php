<div
    x-cloak
    x-data="dropzone({
        _this: @this,
        uuid: @js($uuid),
        multiple: @js($multiple),
        maxSizeMB: 10
    })"
    @dragenter.prevent.document="onDragenter($event)"
    @dragleave.prevent="onDragleave($event)"
    @dragover.prevent="onDragover($event)"
    @drop.prevent="onDrop"
    @window.{{ $uuid }}:fileAdded.window="onFileAdded($event.detail)"
    class="dz-w-full dz-antialiased"
>
    {{-- Outer container --}}
    <div class="dz-flex dz-flex-col dz-items-start dz-h-full dz-w-full dz-max-w-2xl dz-justify-center dz-bg-white dark:dz-bg-gray-800 dark:dz-border-gray-600 dark:hover:dz-border-gray-500">

        {{-- Error section --}}
        @if(! is_null($error))
            <div class="dz-bg-red-50 dz-p-4 dz-w-full dz-mb-4 dz-rounded dark:dz-bg-red-600">
                <div class="dz-flex dz-gap-3 dz-items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dz-w-5 dz-h-5 dz-text-red-400 dark:dz-text-red-200">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="dz-text-sm dz-text-red-800 dz-font-medium dark:dz-text-red-100">{{ $error }}</h3>
                </div>
            </div>
        @endif

        {{-- Dropzone area --}}
        <div @click="$refs.input.click()" class="dz-border dz-border-dashed dz-rounded dz-border-gray-500 dz-w-full dz-cursor-pointer">
            <div>
                {{-- Normal state --}}
                <div x-show="!isDragging" class="dz-flex dz-items-center dz-bg-gray-50 dz-justify-center dz-gap-2 dz-py-8 dz-min-h-[120px] dz-h-full dark:dz-bg-gray-700 dz-rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dz-w-6 dz-h-6 dz-text-gray-500 dark:dz-text-gray-400">
                        <path d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z" />
                        <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                    </svg>
                    <p class="dz-text-md dz-text-gray-600 dark:dz-text-gray-400">
                        Drop here or <span class="dz-font-semibold dz-text-black dark:dz-text-white">Browse files</span>
                    </p>
                </div>

                {{-- Dragging state --}}
                <div x-show="isDragging" class="dz-flex dz-items-center dz-bg-gray-100 dz-justify-center dz-gap-2 dz-py-8 dz-h-full dz-min-h-[120px] dz-rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dz-w-6 dz-h-6 dz-text-gray-500 dark:dz-text-gray-400">
                        <path d="M10 2a.75.75 0 01.75.75v5.59l1.95-2.1a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0L6.2 7.26a.75.75 0 111.1-1.02l1.95 2.1V2.75A.75.75 0 0110 2z" />
                        <path d="M5.273 4.5a1.25 1.25 0 00-1.205.918l-1.523 5.52c-.006.02-.01.041-.015.062H6a1 1 0 01.894.553l.448.894a1 1 0 00.894.553h3.438a1 1 0 00.86-.49l.606-1.02A1 1 0 0114 11h3.47a1.318 1.318 0 00-.015-.062l-1.523-5.52a1.25 1.25 0 00-1.205-.918h-.977a.75.75 0 010-1.5h.977a2.75 2.75 0 012.651 2.019l1.523 5.52c.066.239.099.485.099.732V15a2 2 0 01-2 2H3a2 2 0 01-2-2v-3.73c0-.246.033-.492.099-.73l1.523-5.521A2.75 2.75 0 015.273 3h.977a.75.75 0 010 1.5h-.977z" />
                    </svg>
                    <p class="dz-text-md dz-text-gray-600 dark:dz-text-gray-400">Drop here to upload</p>
                </div>
            </div>

            {{-- Hidden file input --}}
            <input
                x-ref="input"
                wire:model="upload"
                type="file"
                class="dz-hidden"
                x-on:livewire-upload-start="isLoading = true"
                x-on:livewire-upload-finish="isLoading = false"
                x-on:livewire-upload-error="console.error('upload error', $event)"
                @change="validateFiles($event)"
                @if(! is_null($this->accept)) accept="{{ $this->accept }}" @endif
                @if($multiple === true) multiple @endif
            >
        </div>

        {{-- Info bar and loader --}}
        <div class="dz-flex dz-justify-between dz-w-full dz-mt-2">
            <div class="dz-flex dz-items-center dz-gap-2 dz-text-gray-500 dz-text-sm">
                <p>Up to 10&nbsp;MB</p>
                <span class="dz-w-1 dz-h-1 dz-bg-gray-400 dz-rounded-full"></span>
                <p>PNG, JPEG, PDF, MP4</p>
            </div>
            <div x-show="isLoading" role="status">
                <svg aria-hidden="true" class="dz-w-5 dz-h-5 dz-text-gray-200 dz-animate-spin dark:dz-text-gray-700 dz-fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.59C100 78.21 77.61 100.59 50 100.59C22.39 100.59 0 78.21 0 50.59C0 22.98 22.39 0.59 50 0.59C77.61 0.59 100 22.98 100 50.59Z" fill="currentColor"/>
                    <path d="M93.97 39.04C96.39 38.4 97.86 35.91 97.01 33.55C95.29 28.82 92.87 24.37 89.82 20.35C85.85 15.12 80.88 10.72 75.21 7.41C69.54 4.1 63.28 1.94 56.77 1.05C51.77 0.37 46.7 0.45 41.73 1.28C39.26 1.69 37.81 4.2 38.45 6.62C39.09 9.05 41.57 10.47 44.05 10.11C47.85 9.55 51.72 9.53 55.54 10.05C60.86 10.78 65.99 12.55 70.63 15.26C75.27 17.96 79.33 21.56 82.58 25.84Z" fill="currentFill"/>
                </svg>
            </div>
        </div>

        {{-- Uploaded file list --}}
        @if(isset($files) && count($files) > 0)
        <div class="dz-flex dz-flex-wrap dz-gap-x-10 dz-gap-y-2 dz-justify-start dz-w-full dz-mt-5">
            @foreach($files as $key => $file)
                @if(is_array($file))
                <div class="dz-flex dz-items-center dz-justify-between dz-gap-2 dz-border dz-rounded dz-border-gray-200 dz-w-full dark:dz-border-gray-700 dz-overflow-hidden">
                    <div class="dz-flex dz-items-center dz-gap-3">
                        @if($this->isImageMime($file['extension']))
                            <div class="dz-flex-none w-24 h-24">
                                <img src="{{ $file['temporaryUrl'] }}" class="dz-object-cover dz-w-full dz-h-full" alt="{{ $file['name'] }}">
                            </div>
                        @else
                            <div class="dz-flex dz-justify-center dz-items-center dz-w-24 dz-h-24 dz-bg-gray-100 dark:dz-bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="dz-w-8 dz-h-8 dz-text-gray-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625v17.25h12.75V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="dz-flex dz-flex-col dz-items-start dz-gap-1 dz-py-2">
                            <div class="dz-text-slate-900 dz-text-sm dz-font-medium dark:dz-text-slate-100">{{ $file['name'] }}</div>
                            <div class="dz-text-gray-500 dz-text-sm dz-font-medium">{{ \Illuminate\Support\Number::fileSize($file['size']) }}</div>
                            <input class="w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-md border border-slate-200 rounded-sm px-3 py-1 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 " 
                                placeholder="File description"
                                wire:model="files.{{ $key }}.description"
                                >
                        </div>
                    </div>
                    <div class="dz-flex dz-items-center dz-mr-3">
                        <button type="button" @click="removeUpload('{{ $file['tmpFilename'] }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="dz-w-6 dz-h-6 dz-text-black dark:dz-text-white">
                                <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- Alpine logic --}}
    @script
    <script>
        // Global persistent counter (shared across all dropzones)
        window.totalFileSize = window.totalFileSize || 0;

        Alpine.data('dropzone', ({ _this, uuid, multiple, maxSizeMB }) => ({
            isDragging: false,
            isLoading: false,

            validateFiles(e) {
                const files = e.target.files;
                if (!this.checkFileSizes(files)) return;
                this.uploadFiles(files);
            },

            onDrop(e) {
                this.isDragging = false;
                const files = multiple ? e.dataTransfer.files : [e.dataTransfer.files[0]];
                if (!this.checkFileSizes(files)) return;
                this.uploadFiles(files);
            },

            uploadFiles(files) {
                const args = [
                    'upload',
                    files,
                    () => this.isLoading = false,
                    (err) => console.error('upload error', err),
                    () => this.isLoading = true
                ];
                multiple ? _this.uploadMultiple(...args) : _this.upload(...args);
            },

            checkFileSizes(files) {
                let batchSize = 0;

                // Check each file individually
                for (const file of files) {
                    if (file.size > maxSizeMB * 1024 * 1024) {
                        alert(`"${file.name}" exceeds the ${maxSizeMB} MB limit.`);
                        return false;
                    }
                    batchSize += file.size;
                }

                // Check total size across uploads
                const totalLimit = maxSizeMB * 1024 * 1024;
                const newTotal = window.totalFileSize + batchSize;

                if (newTotal > totalLimit) {
                    const totalMB = (newTotal / (1024 * 1024)).toFixed(2);
                    alert(`Your total upload size (${totalMB} MB) exceeds the ${maxSizeMB} MB limit. Try uploading the files separately.`);
                    return false;
                }

                window.totalFileSize = newTotal;

                return true;
            },

            onFileAdded(file) {
                if (file && file.size) {
                    window.totalFileSize += file.size;
                }
                console.log('File added:', file, 'Total so far:', window.totalFileSize);
            },

            removeUpload(tmpFilename) {
                const files = _this.get('files') || [];
                const removed = files.find(f => f.tmpFilename === tmpFilename);

                if (removed?.size) {
                    window.totalFileSize = Math.max(0, window.totalFileSize - removed.size);
                }

                const remaining = files.filter(f => f.tmpFilename !== tmpFilename);
                if (remaining.length === 0) {
                    window.totalFileSize = 0;
                }

                console.log('Removed file:', tmpFilename, 'Total now:', window.totalFileSize);

                _this.dispatch(uuid + ':fileRemoved', { tmpFilename });
            },

            onDragenter() { this.isDragging = true },
            onDragleave() { this.isDragging = false },
            onDragover() { this.isDragging = true },
        }));
    </script>
    @endscript

</div>
