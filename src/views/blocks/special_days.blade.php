<div 
    @if ($spec_day)
        title="{{ $spec_day }}" style="color: #E87E04; border-bottom: 1px dashed #E87E04; cursor: help;" class="dx_tooltip"
    @endif
    >
    <b>{{ $dat_day_name }}</b>, {{ $dat_day_nr }}. {{ $dat_month_name }}</div>
<div style="font-size: 11px;">{{ $names }}</div>