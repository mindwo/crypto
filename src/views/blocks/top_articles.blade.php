@if (count($articles_items))
    
            <div id="top-article-slider-container" style="position: relative; top: 0px; left: 0px; width: 540px; height: 320px; margin-bottom: 20px;"
                 dx_transition_time = "{{ get_portal_config('TOP_SLIDE_TRANSITION_TIME') }}"
                 >
                <!-- Loading Screen -->
                <div data-u="loading" style="position: absolute; top: 0px; left: 0px;">
                    <div style="filter: alpha(opacity=70); opacity: 0.7; position: absolute; display: block; top: 0px; left: 0px; width: 100%; height: 100%;"></div>
                    <div style="position:absolute;display:block;background:url('{{Request::root()}}/img/loading.gif') no-repeat center center;top:0px;left:0px;width:100%;height:100%;"></div>
                </div>
                <div data-u="slides" style="cursor: default; position: relative; top: 0px; left: 0px; width: 540px; height: 320px; overflow: hidden;">
                    @foreach($articles_items as $key => $item)
                        @if (@$item->picture_guid)
                        <div data-p="112.50" style="display: none;">
                            <a href="{{Request::root()}}/{{$article_url}}/{{ $item->id }}">
                            <img data-u="image" src="{{Request::root()}}/img/{{ $item->picture_guid }}" class="img-responsive img-thumbnail"/>
                            <div data-u="caption" data-t="3" style="position: absolute; top: 30px; left: 6px; width: 350px; height: 60px; background-color: rgba(242,120,75,0.5); font-size: 20px; color: #ffffff; line-height: 30px; text-align: center;">{{ $item->title }}</div>
                            </a>
                        </div>                 
                        @endif
                    @endforeach
                </div>
                <!-- Bullet Navigator -->
                <div data-u="navigator" class="jssorb01" style="bottom:16px;right:16px;">
                    <div data-u="prototype" style="width:12px;height:12px;"></div>
                </div>
                <!-- Arrow Navigator -->
                <span data-u="arrowleft" class="jssora02l" style="top:0px;left:8px;width:55px;height:55px;" data-autocenter="2"></span>
                <span data-u="arrowright" class="jssora02r" style="top:0px;right:8px;width:55px;height:55px;" data-autocenter="2"></span>
            </div>
      
@endif