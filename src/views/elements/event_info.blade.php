<div style="padding-left: 20px;">
<div class='col-lg-12'>
    <div class="row">
        <div class="col-lg-3">
            Nosaukums:
        </div>
        <div class="col-lg-9">
            <b>{{ $title }}</b>
        </div>
    </div>

    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            @if ($time_from != $time_to)
                Sākuma laiks:
            @else
                Datums/laiks:
            @endif
        </div>
        <div class="col-lg-9">
            {!! $time_from !!}
        </div>
    </div>

    @if ($time_to)
        @if ($time_from != $time_to)
            <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
                <div class="col-lg-3">
                    Beigu laiks:
                </div>
                <div class="col-lg-9">
                    {!! $time_to !!}
                </div>
            </div>
        @endif
    @endif
    
    @if ($address)
        
        <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
            <div class="col-lg-3">
                Vieta:
            </div>
            <div class="col-lg-9">
                {{ $address }}
            </div>
        </div>
        
    @endif
</div>
@if ($picture)
    <div class="col-lg-12">
        <img class="img-responsive img-thumbnail" src='{{Request::root()}}/img/{{ $picture }}' alt='Kalendāra notikums' title ='{{ $title }}'/>
    </div>
@endif

@if ($description)
    <div class="col-lg-12" style='margin-top:20px;'>
        {!! $description !!}
    </div>
@endif
</div>