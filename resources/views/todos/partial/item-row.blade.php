{{-- One editable checklist row --}}
<li class="flex items-center gap-2">
    <input type="text" name="items[]" value="{{ $text }}" autocomplete="off"
           class="field-input !py-2.5 !text-sm"
           placeholder="Write a checklist item..."/>

    <button type="button" data-remove-item
            class="icon-btn icon-btn-danger size-9 shrink-0"
            title="Remove row">
        <i class="bi bi-dash-lg"></i>
    </button>
</li>
