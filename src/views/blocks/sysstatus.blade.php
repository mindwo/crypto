<div class="portlet dx-block-container-sysstatus" id="dailyquest_{{ $block_guid }}"
     dx_block_init="0"
     dx_block_id="{{ $id }}"
     dx_block_guid="{{ $block_guid }}"
     dx_sys_statuses = '{{ json_encode(array_values($sys_statuses)) }}'>
    <div class="portlet-title">
        <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
        <div class="tools">
            <a class="collapse" href="javascript:;"> </a>                                       
        </div>
    </div>
    <div class="portlet-body">
        <div id="sysstatus-{{ $block_guid }}-answer-panel">
            <div class="row">
                @foreach($sys_statuses as $sys)
                <div class="col-md-12" style="margin-bottom: 10px;">
                    <div id="sysstatus-{{ $block_guid }}-{{ $sys->id }}" class="badge {{ $sys->solved_time ? 'badge-success' : 'badge-danger' }}" style="cursor: pointer;">{{ $sys->name }}</div>             
                </div>
                @endforeach
            </div>
            @if ($last_modified)
            <p style="font-size: xx-small;">Informācija aktualizēta: {{ (new DateTime($last_modified))->format('Y-m-d H:i:s') }}</p>
            @endif
        </div>
    </div>
</div>