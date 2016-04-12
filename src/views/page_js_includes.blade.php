@foreach($inc_arr as $inc)
    <script src="{{Request::root()}}/{{ $inc }}" type="text/javascript"></script>
@endforeach
