@if ($is_setting_rights)
    <div class='btn-group {{ $pull_class }} dx-register-tools'>
        <button  type='button' class='btn btn-white dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
            <i class='fa fa-cog'></i> <i class='fa fa-caret-down'></i>
        </button>
        <ul class='dropdown-menu pull-right'>
            <li><a href='#' class="dx-register-settings">Iestatījumi</a></li>
        </ul>
    </div>
@endif