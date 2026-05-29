@php
    $handledTags = $handled_tags ?? collect();
    $handledTagOptions = $handled_tag_options ?? [];
@endphp

<style {!! \Helper::cspNonceAttr() !!}>
    .handled-tag-swatch {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 8px;
        border: 1px solid rgba(17, 24, 39, 0.18);
        border-radius: 999px;
        vertical-align: middle;
    }

    .handled-tags-settings-table td,
    .handled-tags-settings-table th {
        vertical-align: middle !important;
    }

    .handled-tags-settings-actions {
        min-width: 280px;
    }
</style>

<div class="margin-top margin-bottom">
    <form class="form-inline margin-bottom" method="POST" action="{{ route('handled.tags.manage') }}">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="create" />
        <div class="form-group margin-right">
            <label for="handled_tag_name" class="sr-only">{{ __('Tag name') }}</label>
            <input id="handled_tag_name" type="text" name="name" class="form-control" maxlength="60" placeholder="{{ __('New tag name') }}" required />
        </div>
        <div class="form-group margin-right">
            <label for="handled_tag_color" class="sr-only">{{ __('Colour') }}</label>
            <input id="handled_tag_color" type="text" name="color" class="form-control" maxlength="7" value="#5B6B7A" placeholder="#5B6B7A" />
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Create tag') }}</button>
    </form>

    <p class="form-help margin-bottom">
        {{ __('Tags are global across the helpdesk. Use replace to swap one tag for another on every attached conversation without touching the conversation content.') }}
    </p>

    <div class="table-responsive">
        <table class="table table-bordered handled-tags-settings-table">
            <thead>
                <tr>
                    <th>{{ __('Tag') }}</th>
                    <th width="110">{{ __('Usage') }}</th>
                    <th width="180">{{ __('Conversations') }}</th>
                    <th>{{ __('Manage') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($handledTags as $handledTag)
                    <tr>
                        <td>
                            <span class="handled-tag-swatch" style="background: {{ $handledTag->color ?: '#5B6B7A' }};"></span>
                            <strong>{{ $handledTag->name }}</strong>
                            <div class="text-help">{{ $handledTag->color }}</div>
                        </td>
                        <td>{{ $handledTag->conversations_count }}</td>
                        <td>
                            <a href="{{ route('conversations.search', ['f' => ['tag' => [$handledTag->id]]]) }}" class="btn btn-default btn-sm">
                                {{ __('Open search') }}
                            </a>
                        </td>
                        <td class="handled-tags-settings-actions">
                            <form class="form-inline margin-bottom-10" method="POST" action="{{ route('handled.tags.manage') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="tag_id" value="{{ $handledTag->id }}" />
                                <div class="form-group margin-right">
                                    <input type="text" name="name" class="form-control input-sm" maxlength="60" value="{{ $handledTag->name }}" required />
                                </div>
                                <div class="form-group margin-right">
                                    <input type="text" name="color" class="form-control input-sm" maxlength="7" value="{{ $handledTag->color }}" />
                                </div>
                                <button type="submit" class="btn btn-default btn-sm">{{ __('Save') }}</button>
                            </form>

                            <form class="form-inline pull-left margin-right" method="POST" action="{{ route('handled.tags.manage') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="action" value="replace" />
                                <input type="hidden" name="tag_id" value="{{ $handledTag->id }}" />
                                <div class="form-group margin-right">
                                    <select name="replacement_tag_id" class="form-control input-sm" @if (count($handledTagOptions) <= 1) disabled="disabled" @endif>
                                        @foreach ($handledTagOptions as $handledTagOption)
                                            @if ($handledTagOption['id'] !== (int) $handledTag->id)
                                                <option value="{{ $handledTagOption['id'] }}">{{ $handledTagOption['name'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-default btn-sm" @if (count($handledTagOptions) <= 1) disabled="disabled" @endif>{{ __('Replace') }}</button>
                            </form>

                            <form method="POST" action="{{ route('handled.tags.manage') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="tag_id" value="{{ $handledTag->id }}" />
                                <button type="submit" class="btn btn-link btn-sm text-danger">{{ __('Detach and delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-help">{{ __('No tags created yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
