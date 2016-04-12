@if ($is_tags)
    <div class="portlet" dx_block_id="{{ $id }}">
        <div class="portlet-title">
            <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
            <div class="tools">
                <a class="collapse dx-cloud-collapse" href="javascript:;"> </a>                                       
            </div>
        </div>
        <div class="portlet-body" style="padding:5px;">
            <div id="tags_cloud_{{ $block_guid }}" style="height: 200px; width: 100%;" dx_attr="cloud" dx_json="{{ $tags_json }}">
            </div>
        </div>
    </div>
@endif