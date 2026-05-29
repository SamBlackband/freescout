@php
    $handledTagOptions = $handled_tag_options ?? [];
@endphp

@if (count($handledTagOptions))
    <div class="btn-group">
        <button type="button" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('Add tag') }}">
            <span class="glyphicon glyphicon-tag"></span><span class="caret"></span>
        </button>
        <ul class="dropdown-menu conv-tag-add dm-scrollable">
            @foreach ($handledTagOptions as $handledTagOption)
                <li><a href="#" data-tag_id="{{ $handledTagOption['id'] }}">{{ $handledTagOption['name'] }}</a></li>
            @endforeach
        </ul>
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('Remove tag') }}">
            <span class="glyphicon glyphicon-remove-circle"></span><span class="caret"></span>
        </button>
        <ul class="dropdown-menu conv-tag-remove dm-scrollable">
            @foreach ($handledTagOptions as $handledTagOption)
                <li><a href="#" data-tag_id="{{ $handledTagOption['id'] }}">{{ $handledTagOption['name'] }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
