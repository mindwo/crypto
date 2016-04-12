<div class="portfolio-content portfolio-1 dx-block-container-publish"
    dx_block_init="0"
     dx_skip = "{{ $skip }}"
     dx_filt_nr = "{{ $filt_nr }}"
     dx_filt_year = "{{ $filt_year }}"   
    >
    
    <div>
        <button class="btn blue-soft pull-right search-article-tools-btn" id="dx_search_tools_btn" type='button'>Meklēšanas rīki <i class="fa fa-caret-down"></i></button>
    </div>
    
    <div id="js-filters-juicy-projects" class="cbp-l-filters-button">
        <div data-filter="*" class="cbp-filter-item-active cbp-filter-item btn dark btn-outline uppercase"> Visi
            <div class="cbp-filter-counter"></div>
        </div>
        @foreach($types as $type)
        <div data-filter=".dx-publish-type-{{ $type->id }}" class="cbp-filter-item btn dark btn-outline uppercase"> {{ $type->title }}
            <div class="cbp-filter-counter"></div>
        </div>
        @endforeach
    </div>
    
        <div class="search-tools-block">
        <form action='{{ Request::root() }}/{{ Request::path() }}' method='POST' id="search_form" class="search-tools-form search-tools-hiden">

            <div class="search-tools-container">

                <div class="row">
                    <div class="col-lg-4">
                        <div class="input-group" style="width: 100%; margin-bottom: 10px;">
                            <select class="form-control" name="year">
                                <option value="0" {{ ($filt_year == 0) ? 'selected' : '' }}>Visi gadi</option>
                                <option disabled></option>
                                @foreach($years as $item)
                                    <option value='{{ $item->y }}' {{ ($filt_year == $item->y) ? 'selected' : '' }}>{{ $item->y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group" style='width: 100%;'>
                            <input class='form-control' name='nr' value='{{ $filt_nr }}' placeholder="Numurs"/>                                    
                        </div>
                    </div>
                </div>

                <div style='margin-top: 10px; margin-bottom: 20px;'>
                    <button class="btn blue-soft search-article-btn search-article-bottom pull-right" type="submit"><i class="fa fa-search"></i> Meklēt</button>
                    <a class="pull-left" id="clear_link" style="margin-right: 10px;" title='Notīrīt meklēšanas kritērijus'><i class="fa fa-eraser"></i> Notīrīt kritērijus</a>
                </div>
            </div>
            {!! csrf_field() !!}
        </form>
    </div>
        
    @if (count($publish) > 0)
        <div id="js-grid-juicy-projects" class="cbp">
            @include('blocks.publish_items')            
        </div>
        <div id="js-loadMore-juicy-projects" class="cbp-l-loadMore-button">
            <a href="#" class="cbp-l-loadMore-link btn blue-soft" rel="nofollow" id='load_more_link'>VAIRĀK</a>
        </div>
    @else
        <div class="alert alert-info" role="alert" style='margin-top: 60px;'>Nav atrasts neviens atbilstošs ieraksts.</div>
    @endif 
</div>