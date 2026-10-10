@props(['id', 'label', 'first' => false, 'last' => false])

<div class="flex items-center gap-0.5">
    <x-icon-button icon="arrow-up" :label="'Naikkan '.$label" wire:click="moveUp({{ $id }})" :disabled="$first" />
    <x-icon-button icon="arrow-down" :label="'Turunkan '.$label" wire:click="moveDown({{ $id }})" :disabled="$last" />
</div>
