@if (count($articles_items))
    <div class="dx-article-feed-wrapper">
        <div class="dx-article-feed-content"  id="feed_area_{{ $block_guid }}" >
             <div class="article_row_area">
                @foreach($articles_items as $article)
                    @include('elements.articles_ele')
                @endforeach
                {!! $articles_items->render() !!}
            </div>
        </div>
    </div>
@endif 
        
    
