<div class='col-lg-12'>
    <div class="row">
        <div class="col-lg-3">
            Nosaukums:
        </div>
        <div class="col-lg-9">
            <b>{{ $system->name }}</b>
        </div>
    </div>

    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            Interneta adrese:
        </div>
        <div class="col-lg-9">
            <a title="{{ $system->name }} - {!! $system->url !!}" href="{!! $system->url !!}">{!! $system->url !!}</a>
        </div>
    </div>

    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            Atbildīgā persona:
        </div>
        <div class="col-lg-9">
            {{ $system->employee }}, <a href="mailto:{{ $system->employee_email }}">{{ $system->employee_email }}</a>
        </div>
    </div>

    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            Incidenta laiks:
        </div>
        <div class="col-lg-9">
            {{$system->created_time}}
        </div>
    </div>

    @if ($system->solved_time == "-")
    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            Papildus informācija:
        </div>
        <div class="col-lg-9">
            {{$system->details}}
        </div>
    </div>

    @else
    <div class="row" style='margin-bottom: 10px; margin-top: 10px;'>
        <div class="col-lg-3">
            Atrisināšanas laiks:
        </div>
        <div class="col-lg-9">
            {{$system->solved_time}}
        </div>
    </div>
    @endif
</div>