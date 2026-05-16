<div class="conv-sidebar-block handled-context-card handled-history-card">
    <div class="handled-context-card-header">
        <div>
            <div class="handled-eyebrow">{{ __('History') }}</div>
            <h4>{{ __('Previous conversations') }}</h4>
        </div>
        @if ($prev_conversations->hasMorePages())
            <a href="{{ route('customers.conversations', ['id' => $customer->id])}}" class="handled-context-link">{{ __("View all :number", ['number' => $prev_conversations->total()]) }}</a>
        @endif
    </div>
    <ul class="handled-history-list">
        @foreach ($prev_conversations as $prev_conversation)
            <li>
                <a href="{{ $prev_conversation->url() }}" target="_blank" class="help-link">
                    <span class="handled-history-subject"><i class="glyphicon @if ($prev_conversation->isPhone()) glyphicon-earphone @else glyphicon-envelope @endif"></i>{{ $prev_conversation->getSubject() }}</span>
                    <span class="handled-history-meta">
                        <span>{{ $prev_conversation->getStatusName() }}</span>
                        <span>{{ App\User::dateDiffForHumans($prev_conversation->updated_at ?: $prev_conversation->created_at) }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
