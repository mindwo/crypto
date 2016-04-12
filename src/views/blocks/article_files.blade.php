<div class="mt-element-list">
    <div class="mt-list-container list-simple ext-1">
        <ul>
            @foreach ($files_rows as $key => $file)
            @if ($key == 0)
            <li class="mt-list-item" style="border-top: 1px solid #e7ecf1;">
            @else
            <li class="mt-list-item">
            @endif
                <div class="list-icon-container">
                    <i class="fa fa-lg fa-paperclip"></i>
                </div>

                <div class="list-item-content">
                    <h3 class="uppercase">
                        <a href='{{Request::root()}}/img/{{ $file->file_guid}}' target='_blank'>{{ ($file->title) ? $file->title : $file->file_name }}</a>    
                    </h3>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>