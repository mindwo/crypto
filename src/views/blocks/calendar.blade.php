<div class="portlet dx-block-container-calendar" id="dailyquest_{{ $block_guid }}" style="background-color: white; padding: 20px;"
     dx_block_init="0"
     dx_block_id="{{ $id }}"
     dx_block_guid = "{{ $block_guid }}"
     dx_events_items = "{{ $events_items }}">
    <div class="portlet-title">
        <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
        <div class="tools">
            <a class="collapse" href="javascript:;"> </a>                                       
        </div>
    </div>
    <div class="portlet-body">
        <div id="dailyquest-{{ $block_guid }}-calendar"></div>
    </div>
</div>