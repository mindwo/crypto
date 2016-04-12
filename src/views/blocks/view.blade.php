<div id="{{ $block_id }}" class="dx-block-container-view" 
     dx_block_init = '0' 
     dx_tab_id="{{ $tab_id }}" 
     dx_menu_id="{{ $menu_id }}" 
     dx_grid_id="{{ $grid_id }}" 
     dx_list_id="{{ $list_id }}"
     dx_rel_field_id = "{{ $rel_field_id }}"
     dx_rel_field_value = "{{ $rel_field_value }}"
     dx_form_htm_id = "{{ $form_htm_id }}" 
     dx_view_id = "{{ $view_id }}" 
     dx_grid_form = "{{ $grid_form }}"
    >
    @if (!$tab_id)
        <div class='portlet' style='background-color: white; padding: 10px;'>
            <div class='portlet-title'>
                <div class="caption font-grey-cascade uppercase">{{ $grid_title }}
                @if ($hint)
                    &nbsp;<i class='fa fa-question-circle dx-form-help-popup' title='{{ $hint }}' style='cursor: help; float: none; font-size: 18px;'></i>
                @endif
                </div>
                
                @include('blocks.view_settings_btn', ['pull_class' => 'pull-right'])
            </div>
            <div class='portlet-body'>
    @endif

                <form class='form-horizontal'>
                    <input type=hidden name=filter_data id="filter_data_{{ $grid_id }}" value="{{ $filter_data }}">
                    <div class='row'>
                        <div class='col-lg-6'>
                            <button class='btn btn-white' type='button' id="{{ $menu_id }}_refresh" dx_attr="refresh"><i class='fa fa-refresh'></i>&nbsp;{{ trans('grid.reload') }}</button>
                            <button class='btn btn-white' type='button' id="{{ $menu_id }}_excel" title='{{ trans('grid.excel_hint') }}'><i class='fa fa-file-excel-o'></i>&nbsp;{{ trans('grid.excel') }}</button>

                            @if ($show_new_button)
                                <button class='btn btn-primary' type='button' id="{{ $menu_id }}_new">{{ trans('grid.new') }}</button>
                            @endif
                            
                            @if ($tab_id)
                                @include('blocks.view_settings_btn', ['pull_class' => ''])
                            @endif
                        </div>

                        @if (count($combo_items))
                            <div class='col-lg-6'>
                                <div class='form-group'>
                                    <label class='col-sm-2 control-label'>{{ trans('grid.view') }}:</label>
                                    <div class='col-sm-10'>
                                        <select class='form-control' id="{{ $menu_id }}_viewcbo">                
                                            @foreach($combo_items as $item)
                                                <option 

                                                    @if ($item->id == $view_id)
                                                        selected
                                                    @endif                                                

                                                    value="{{ ($item->url && !$tab_id) ? $item->url : $item->id }}">{{ $item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </form>

                @if ($tab_id)
                    <div style='height: 15px'></div>
                @endif

                {!! $grid_htm !!}
                {!! $paginator_htm !!}
    @if (!$tab_id)
            </div>        
        </div>
    @endif
</div>