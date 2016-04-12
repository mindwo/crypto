<div class="dx-employees-birth-page">
    <h3 class="page-title">Dzimšanas dienas
        <small>
            <a href="{{ Request::root() }}/dzimsanas_dienas_sodien">ŠODIEN
            <span class="badge badge-success" style='margin-top: -15px;'> {{ $empl_cnt_day }} </span>
            </a>
        </small>
    </h3>

    <div class="dx-employees-birth-block">
        @include('search_tools.search_form', [
                    'criteria_title' => 'Vārds/uzvārds',
                    'fields_view' => 'search_tools.birthday_fields',
                    'form_url' => 'dzimsanas_dienas_sodien'
                ])
    </div>

    @if (count($employees))
        <div class="employee-list">
            @foreach($employees as $item)
                @include('elements.employee', ['item' => $item, 'show_date' => 1])
            @endforeach
        </div>
    @else
        <div class="alert alert-danger" role="alert">Nav atrasts neviens atbilstošs ieraksts.</div>
    @endif
</div>