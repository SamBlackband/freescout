@php
    $handledConversation = $conversation ?? null;
    $handledConversationTags = collect();
    $handledShowEmptyState = $handled_show_empty_state ?? true;

    if ($handledConversation) {
        $handledConversationTags = $handledConversation->relationLoaded('handledTags')
            ? $handledConversation->handledTags
            : $handledConversation->handledTags()->orderBy('name')->get();
    }
@endphp

<style {!! \Helper::cspNonceAttr() !!}>
    .handled-tag-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .handled-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .handled-tag-chip-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        opacity: 0.72;
    }
 </style>

<div class="handled-tag-chip-list" @if ($handledConversation) data-conversation-id="{{ $handledConversation->id }}" @endif>
    @forelse ($handledConversationTags as $handledTag)
        <a
            href="{{ route('conversations.search', ['f' => ['tag' => [$handledTag->id]]]) }}"
            class="handled-tag-chip"
            style="background: {{ $handledTag->color }}20; color: {{ $handledTag->color }};"
        >
            <span class="handled-tag-chip-dot"></span>
            <span>{{ $handledTag->name }}</span>
        </a>
    @empty
        @if ($handledShowEmptyState)
            <span class="text-help">{{ __('No tags attached yet.') }}</span>
        @endif
    @endforelse
</div>
