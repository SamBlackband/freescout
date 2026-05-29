@php
    $handledTagsService = app(\App\Services\HandledTagsService::class);
    $handledTagOptions = $handled_tag_options ?? $handledTagsService->getTagOptions();
    $handledSelectedTagIds = $handled_tag_selected_ids ?? [];
    $handledSelectedTagIds = $handledTagsService->getValidTagIds($handledSelectedTagIds);
    $handledCanManageTags = $handled_can_manage_tags ?? false;
    $handledConversationId = $handled_conversation_id ?? null;
@endphp

<style {!! \Helper::cspNonceAttr() !!}>
    .handled-tag-picker-wrap {
        margin-top: 14px;
    }

    .handled-tag-picker-help {
        margin-top: 6px;
    }
</style>

<div class="handled-tag-picker-wrap">
    <label class="text-help">{{ __('Tags') }}</label>
    <select
        class="form-control handled-tag-picker"
        name="handled_tag_ids[]"
        multiple
        data-placeholder="{{ __('Select tags') }}"
        data-can-manage-tags="{{ $handledCanManageTags ? 1 : 0 }}"
        @if (!$handledCanManageTags) disabled="disabled" @endif
    >
        @foreach ($handledTagOptions as $handledTagOption)
            <option
                value="{{ $handledTagOption['id'] }}"
                data-color="{{ $handledTagOption['color'] }}"
                @if (in_array((int) $handledTagOption['id'], $handledSelectedTagIds)) selected="selected" @endif
            >
                {{ $handledTagOption['name'] }}
            </option>
        @endforeach
    </select>
    @if ($handledCanManageTags && $handledConversationId)
        <div class="margin-top-10">
            <button
                type="button"
                class="btn btn-default btn-sm handled-tag-picker-save"
                data-conversation-id="{{ $handledConversationId }}"
            >
                {{ __('Save tags') }}
            </button>
        </div>
    @endif
    <p class="form-help handled-tag-picker-help">
        @if ($handledCanManageTags)
            {{ __('Tags update the conversation itself. Saved replies can add more tags into this selection before the draft or reply is saved.') }}
        @else
            {{ __('Tags attached to this conversation will be shown here.') }}
        @endif
    </p>
</div>
