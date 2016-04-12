@foreach($galeries as $item)
<div class="cbp-item {{ $item->type_code }}">
    <div class="cbp-caption">
        <div class="cbp-caption-defaultWrap">
            <img src="{{Request::root()}}/formated_img/gallery_thumbn/{{ ($item->picture_guid) ? $item->picture_guid : $item->placeholder_pic }}" alt="" class="dx-gallery-thumbnail">
        </div>
        <div class="cbp-caption-activeWrap">
            <div class="cbp-l-caption-alignCenter">
                <div class="cbp-l-caption-body">
                    <a href="{{Request::root()}}/{{ $article_url }}/{{ $item->id }}" class="cbp-l-caption-buttonLeft btn blue-soft uppercase" rel="nofollow">Apskatīt</a>
                </div>
            </div>
        </div>
    </div>
    <div class="cbp-l-grid-projects-title uppercase text-center" title="{{ $item->title }}">{{ $item->title }}</div>
    <div class="cbp-l-grid-projects-desc text-center">
        <strong class="font-yellow-gold">{!! short_date($item->publish_time) !!}</strong>
    </div>
</div>
@endforeach