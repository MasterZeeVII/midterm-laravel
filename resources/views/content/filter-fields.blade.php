{{-- Carries the active filters through a write, so saving from a filtered
     view redirects back to that same view instead of the full ledger. --}}
@foreach ($filterQuery as $name => $value)
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
@endforeach
