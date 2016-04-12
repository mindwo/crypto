<div class="portlet dx-article-item-page">
    <div class="portlet-body">
        <div class="row">
            <div class="col-lg-12" style='margin-bottom: 20px;'>
                <h3 style='margin-bottom: 10px;'>{{ $item->title }}</h3>
                <span class="font-yellow-gold publish-time">{!! format_event_time($item->publish_time) !!}</span>
            </div>

            <div class='col-lg-12 dx-article-content'>
                <p style='margin-left: 0px;'>{!! $item->article_text !!}</p>
            </div>  
            
            @if (count($files_rows) > 0)
                <div class='col-lg-12' style='margin-top: 10px;'>
                    @include('blocks.article_files')
                </div>
            @endif
            
            @if ($author_row)
                <div class='col-lg-6' style='margin-top: 20px;'>

                    <div class="employee-details-1">
                        <div class="well">
                            <h4>{{ $author_row->employee_name }}</h4>
                            <a href="#" class='dx_position_link' title="Rādīt visus darbiniekus ar tādu pašu amatu" dx_attr="{{ $author_row->position }}" dx_source_id="{{ $author_row->source_id }}">{{ $author_row->position }}</a><br>
                            <a href="#" class="small dx_department_link" title="Rādīt visus darbiniekus no šīs struktūrvienības" dx_attr="{{ $author_row->department_name }}" dx_source_id="{{ $author_row->source_id }}">{{ $author_row->department_name }}</a><br><br>
                            <div class="text-left">
                                <a href="mailto:{{ $author_row->email }}">{{ $author_row->email }}</a><br>
                                {!! phoneClick2Call($author_row->phone, $click2call_url, $fixed_phone_part) !!}
                                @if ($author_row->source_icon)
                                <i class="{{ $author_row->source_icon }} fa-2x font-grey-salt pull-right" title='{{ $author_row->company_name }}'></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif                       
            
            <div class='col-lg-12 article_item_row'>
                
                @include('elements.article_tags', ['article' => $item, 'tags' => $tags]) 
                
            </div>
        </div>
        
        @include('blocks.video_gallery_items')
        @include('search_tools.employee_links_form')
    </div>
</div>