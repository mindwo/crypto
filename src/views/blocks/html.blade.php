@if ($is_border_0)
    <div id="html_block_{{ $block_guid }}">
            {!! $html !!}
    </div>
@else
<div class="portlet" dx_block_id="{{ $id }}">
    @if ($block_title)
        <div class="portlet-title">
            <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
            <div class="tools">
                <a class="collapse" href="javascript:;"> </a>                                       
            </div>
        </div>
    @endif
    <div class="portlet-body">
        <div id="html_block_{{ $block_guid }}" style="overflow: hidden !important; text-overflow: ellipsis;">
            {!! $html !!}
        </div>
    </div>
</div>
@endif