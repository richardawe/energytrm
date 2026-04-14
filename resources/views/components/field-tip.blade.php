@php
    $tip = \App\Models\FieldDescription::tip($field, $tab ?? '');
@endphp
@if($tip)
<span
    data-bs-toggle="tooltip"
    data-bs-placement="{{ $placement ?? 'top' }}"
    title="{{ $tip }}"
    style="cursor:help; color:var(--etrm-secondary); font-size:.75rem; vertical-align:middle; margin-left:3px;"
    tabindex="0"
>&#9432;</span>
@endif
