@if ($is_search)
<div class="portlet" style='background-color: white; padding: 10px;'>
    <div class="portlet-body">

        <h2><i class="fa fa-search"></i> Meklēt darbinieku izmaiņas</h2>
        <form action='{{ Request::root() }}/{{ Request::path() }}' method='POST'>
            <div class="input-group" style="margin-top: 20px; margin-bottom: 10px; width: 100%;position: relative;">
                <input type="text" class="form-control" placeholder="Meklēšanas frāze" name='criteria' value='{{ ($criteria) ? $criteria : "" }}'>                              

                <table>
                    <tr>
                        <td><div class="checkbox"><label> <input type="checkbox" class="i-checks" name='ch_new' value='1' {{ ($is_new) ? 'checked' : '' }}> Jaunie&nbsp;&nbsp;</label></div></td>
                        <td><div class="checkbox"><label> <input type="checkbox" class="i-checks" name='ch_change' value='1' {{ ($is_change) ? 'checked' : '' }}> Izmaiņas&nbsp;&nbsp;</label></div></td>
                        <td><div class="checkbox"><label> <input type="checkbox" class="i-checks" name='ch_leave' value='1' {{ ($is_leave) ? 'checked' : '' }}> Atbrīvotie</label></div></td>
                    </tr>
                </table>    

                <span class="search_sub_title">Izmaiņu datums:</span> <br/>

                <div style="display:inline-block; margin: 0 auto;">
                    <table>
                        <tr>
                            <td style="padding-right: 5px">
                                <span class="table_font">No:</span>
                            </td>
                            <td style="padding: 10px">
                                {!! $picker_from_html !!}
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="display:inline-block; margin: 0 auto;">
                    <table>
                        <tr>
                            <td  style="padding: 10px">
                                <span class="table_font">Līdz:</span>
                            </td>
                            <td  style="padding: 10px">
                                {!! $picker_to_html !!}
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="display:block; float:right; overflow:auto; min-height: 60px;">
                    <button class="btn btn-primary"type="submit" style="bottom:-10px; position: relative;">Meklēt</button>
                </div>

            </div>
            {!! csrf_field() !!}
        </form>

    </div>
</div>
@endif

@if (count($changes_items))

@if ($criteria || $date_from || $date_to)
<div class="alert alert-success" role="alert">Atrasto ierakstu skaits: <b>{{ $rows_count }}</b></div>
@endif

<div class="portlet" style='background-color: white; padding: 10px;'>        
    <div class="portlet-body" id="feed_area_{{ $block_guid }}">            
        <div class="dx_empl_change_row_area">
            <div class="table-responsive">
                <table border="0" width="100%" class="table table-striped">
                    <thead
                        <tr>
                            <th>Darbinieks</th>
                            <th>Struktūrvienība</th>
                            <th>Amats</th>
                            <th style="text-align: center;">Spēkā no</th>
                        </tr>
                    <thead>
                    <tbody>
                        @foreach($changes_items as $item)
                        <tr>
                            <td>
                                <img alt="{{ $item->employee_name }}" class="img-circle m-b dx_empl_change_pic" src="{{Request::root()}}/formated_img/employee_row/{{ ($item->picture_guid) ? $item->picture_guid: $avatar }}">
                                {{ $item->employee_name }}
                            </td>
                            <td>
                                @if ($item->old_department)
                                <span class="dx_empl_change_label">Iepriekšējā:</span><br>
                                {{ $item->old_department }}<br>
                                @endif

                                @if ($item->new_department)
                                <span class="dx_empl_change_label">Jaunā:</span><br>
                                {{ $item->new_department }}
                                @endif
                            </td>
                            <td>
                                @if ($item->old_position)
                                <span class="dx_empl_change_label">Iepriekšējais:</span><br>
                                {{ $item->old_position }}<br>
                                @endif

                                @if ($item->new_position)
                                <span class="dx_empl_change_label">Jaunais:</span><br>
                                {{ $item->new_position }}
                                @endif
                            </td>
                            <td align="center">
                                {!! short_date($item->valid_from) !!}
                                @if (!$item->old_source_id || !$item->new_source_id)
                                <div class="text-center" style="margin-top: 10px;">
                                    @if (!$item->old_source_id)
                                    <span class="badge badge-success">Jauns</span>
                                    @endif

                                    @if (!$item->new_source_id)
                                    <span class="badge badge-default">Atbrīvots</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                        </tr>                                                
                        @endforeach
                    </tbody>
                </table>
            </div>
            {!! $changes_items->appends(['criteria' => utf8_encode($criteria), 'date_from' => $date_from, 'date_to' => $date_to])->render() !!}
        </div>
    </div>
</div>
@else
<div class="alert alert-danger" role="alert">Nav atrasts neviens atbilstošs ieraksts.</div>
@endif