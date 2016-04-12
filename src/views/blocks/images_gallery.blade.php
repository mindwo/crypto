<div class="portlet">
    <div class="portlet-body">
        <div class="row">
            <div class="col-lg-12" style='margin-bottom: 20px;'>
                <h3 style="margin-bottom: 10px;">{{ $item->title }}</h3>
                <span class="font-yellow-gold publish-time">{!! format_event_time($item->publish_time) !!}</span>
            </div>
            
            <div class='col-lg-12'>
                <p style='margin-left: 0px;'>{{ $item->intro_text }}</p>
            </div>
            
            
            <div class='col-lg-12 article_item_row'>
                @include('elements.article_tags', ['article' => $item, 'tags' => $tags]) 
                <span class="pull-right" style="color: gray;"><i class="fa fa-picture-o"> </i> {{ count($images_rows) }} attēl{{ count($images_rows)==1 ? 's':'i' }}</span>
            </div>
                  
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-body">

        <div class="lightBoxGallery">
            @foreach($images_rows as $img)
                <a class="dx-no-link-pic" href="{{Request::root()}}/img/{{ $img->file_guid }}" title="{{ $img->file_name }}" data-gallery=""><img src="{{Request::root()}}/formated_img/medium/{{ $img->file_guid }}"></a>
            @endforeach
        </div>
    </div>
</div>