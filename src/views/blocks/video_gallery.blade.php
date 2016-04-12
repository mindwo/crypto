<div class="portlet">
    <div class="portlet-body" style='padding-right: 30px; padding-left: 30px;'>
        <div class="row">
            <div class="col-lg-12" style='margin-bottom: 10px;'>
                <h2>{{ $item->title }}</h2>
                <span>{!! format_event_time($item->publish_time) !!}</span>
            </div>
            
            <div class='col-lg-12'>
                <p style='margin-left: 0px;'>{{ $item->intro_text }}</p>
            </div>
            
            
            <div class='col-lg-12 article_item_row'>
                @include('elements.article_tags', ['article' => $item, 'tags' => $tags]) 
                <span class="pull-right" style="color: gray;"><i class="fa fa-video-camera"> </i> {{ count($video_rows) }} video</span>
            </div>
                  
        </div>
    </div>
</div>

@include('blocks.video_gallery_items')