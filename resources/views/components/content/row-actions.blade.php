@props(['id', 'label'])

<div class="flex items-center justify-end gap-1">
    <x-icon-button icon="pencil" :label="'Edit '.$label" tone="edit" @click="$dispatch('open-modal', 'record-form')" wire:click="openEditModal({{ $id }})" />
    <x-icon-button icon="trash" :label="'Hapus '.$label" tone="danger" @click="$dispatch('open-modal', 'confirm-delete')" wire:click="confirmDelete({{ $id }})" />
</div>
