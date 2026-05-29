@php
    $handledFilterTags = $handled_tags ?? [];
    $handledSelectedTagIds = isset($filters['tag']) && is_array($filters['tag']) ? array_map('intval', $filters['tag']) : [];
@endphp

<div class="col-sm-6 form-group @if (count($handledSelectedTagIds)) active @endif" data-filter="tag">
    <label>{{ __('Tags') }} <b class="remove" data-toggle="tooltip" title="{{ __('Remove filter') }}">×</b></label>
    <select name="f[tag][]" class="form-control filter-multiple" multiple @if (!count($handledSelectedTagIds)) disabled @endif>
        @foreach ($handledFilterTags as $handledTag)
            <option value="{{ $handledTag['id'] }}" @if (in_array((int) $handledTag['id'], $handledSelectedTagIds)) selected="selected" @endif>{{ $handledTag['name'] }}</option>
        @endforeach
    </select>
</div>
