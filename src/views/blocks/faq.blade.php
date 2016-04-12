<div class="portlet" id="faq_{{ $block_guid }}" dx_block_id="{{ $id }}">
    <div class="portlet-title">
        <div class="caption font-grey-cascade uppercase">{{ $block_title }}</div>
        <div class="tools">
            <a class="collapse" href="javascript:;"> </a>                                       
        </div>
    </div>
    <div class="portlet-body">
        <div class="faq-page faq-content-1">
            <div class="faq-content-container">
                <div class="row">
                    <?php $section_no = 0; ?>
                    @foreach($question_list as $section_key => $section) 
                    <?php $section_no++; ?>
                    @if ($is_compact == 0)
                        <div class="col-md-6"> 
                    @else
                        <div class="col-md-12"> 
                    @endif                       
                        <div class="faq-section ">
                            <h2 class="faq-title uppercase font-blue">{{$section_key}}</h2>
                            <div class="panel-group accordion faq-content" id="accordion3">
                                @for ($i = 0; $i < count($section); $i++)
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <i class="fa fa-circle"></i>
                                            <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_{{$section_no}}_{{$i}}" aria-expanded="false"> {{$section[$i][0]}}</a>
                                        </h4>
                                    </div>
                                    <div id="collapse_{{$section_no}}_{{$i}}" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                                        <div class="panel-body">
                                            <p>{{$section[$i][1]}}</p>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>                         
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>