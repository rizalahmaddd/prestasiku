@props(['label'])

<div class="pt-4 border-t border-slate-800/80 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5 [&>*]:w-full sm:[&>*]:w-auto">
    <x-secondary-button type="button" wire:click="closeModal">Batal</x-secondary-button>
    <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save,mediaUploads">
        <x-loading-label target="save" loading="Menyimpan...">{{ $label }}</x-loading-label>
    </x-primary-button>
</div>
