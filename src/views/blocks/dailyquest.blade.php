<div class="portlet dx-block-container-dailyquest" id="dailyquest_{{ $block_guid }}" 
     dx_block_init="0"
     dx_block_id="{{ $id }}"
     dx_block_guid = "{{ $block_guid }}"
     dx_source_id = "{{ $source_id }}"
     dx_option_list = '{{ json_encode(array_values($option_list)) }}'
     dx_is_multi_answer = "{{ $is_multi_answer }}"
     dx_is_answered = "{{ $is_answered }}"
     dx_has_legend = "{{ $has_legend }}"
     dx_chart_colors = "{{ $chart_colors }}"
     >
    <div class="portlet-title">
        <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
        <div class="tools">
            <a class="collapse" href="javascript:;"> </a>                                       
        </div>
    </div>
    <div class="portlet-body">
        <p><b>{{ $question_text }}</b></p>
        @if ($question_img != '')
        <p><img class="img-responsive img-thumbnail" src="{{ Request::root() }}/img/{{ $question_img }}" alt="{{ $question_text }} - Attēls" style="width: 100%;"></p>
        @endif
        @if ($question_img)
        <p id="dailyquest-{{ $block_guid }}-answer-label" style="display: {{ ($is_answered) ? 'block' : 'none'}};"><b>Atbilžu sadalījums</b></p>
        @endif
        <div id="dailyquest-{{ $block_guid }}-chart"></div>
        @if (!$is_answered)
        <div id="dailyquest-{{ $block_guid }}-answer-panel">
            <div class="form-group" style="margin-top: 20px;">
                @foreach($option_list as $option)
                <div id="dailyquest-{{ $block_guid }}-options" class="input-group" style="margin-bottom: 10px;">
                    <input type="{{ $is_multi_answer ? 'checkbox' : 'radio' }}" class="icheck" name="rad_apt" value="{{ $option->id }}" data-radio="iradio_square-grey"  data-cursor="true" id="opt-{{ $option->id }}">
                    <label for="opt-{{ $option->id }}" style="cursor: pointer;">{{ $option->option_text }}</label>
                </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <button class="btn btn-primary pull-right" type="button" id="dailyquest-{{ $block_guid }}-btnSave">Atbildēt</button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>