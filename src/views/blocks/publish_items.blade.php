@foreach($publish as $item)
<div class="cbp-item dx-publish-type-{{ $item->publish_type_id }}">
    <div class="cbp-caption">
        <div class="cbp-caption-defaultWrap">
            <img src="{{Request::root()}}/formated_img/gallery_thumbn/{{ ($item->prev_file_guid) ? $item->prev_file_guid : $avatar }}" alt="" class="dx-gallery-thumbnail">
        </div>
        <div class="cbp-caption-activeWrap">
            <div class="cbp-l-caption-alignCenter">
                <div class="cbp-l-caption-body">
                    <a target="_blank" href="{{Request::root()}}/img/{{ $item->file_guid }}" class="cbp-l-caption-buttonLeft btn blue-soft uppercase" rel="nofollow">Apskatīt</a>
                </div>
            </div>
        </div>
    </div>
    <div class="cbp-l-grid-projects-title uppercase text-center">{{ $item->publish_type_title }}</div>
    <div class="cbp-l-grid-projects-desc uppercase text-center">
        <strong class="font-yellow-gold">{{ $item->nr }} / {!! short_date($item->pub_date) !!}</strong>
    </div>
</div>
@endforeach