@if (count($video_rows) > 0)
<div class="hpanel">
    
    <div class="panel-body">
        <div style='max-width: 820px; max-height: 468px; margin: 0 auto; margin-bottom: 100px;'>
            <div class="html5gallery"
                 style="display:none; margin: 0 auto;"
                 data-skin="gallery"
                 data-width="480" 
                 data-height="272"
                 data-responsive="true"
                 data-shownumbering="true" 
                 data-numberingformat="%NUM no %TOTAL - "
                 data-resizemode="fill"
                 data-effect="fadeout"
                 data-autoslide="true"
                 data-thumbshowtitle="false"
                 >

                @foreach($video_rows as $vid)
                    @if ($vid->youtube_url)
                        @if ($vid->youtube_code)
                            <a href="http://www.youtube.com/embed/{{ $vid->youtube_code }}">
                                @if ($vid->prev_file_guid)
                                   <img src="{{Request::root()}}/formated_img_galery/gallery_medium/{{ $vid->prev_file_guid }}" alt="{{ $vid->title }}">
                                @else
                                    <img src="http://img.youtube.com/vi/{{ $vid->youtube_code }}/0.jpg" alt="{{ $vid->title }}">
                                @endif
                            </a>
                        @endif
                    @else
                        @if (strpos($vid->file_guid, '.mp4') !== false)
                            <a href="{{Request::root()}}/img/{{ $vid->file_guid }}">
                                <img src="{{Request::root()}}/formated_img_galery/gallery_medium/{{ ($vid->prev_file_guid) ? $vid->prev_file_guid : $avatar }}" alt="{{ $vid->title }}">
                            </a>
                        @else
                            <a href="{{Request::root()}}/formated_img_galery/gallery_big/{{ $vid->file_guid }}">
                                <img src="{{Request::root()}}/formated_img_galery/gallery_medium/{{ ($vid->prev_file_guid) ? $vid->prev_file_guid : $avatar }}" alt="{{ $vid->title }}">
                            </a>
                        @endif
                    @endif
                @endforeach

            </div>
        </div>
    </div>
</div>
@endif