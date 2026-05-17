@php
    $handled_support_context = $handled_support_context ?? (isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null);
    $handled_setup = $handled_setup ?? (is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null);
    $handled_support_summary = $handled_support_summary ?? (is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null);
    $handled_ticket = $handled_ticket ?? (is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null);
@endphp

@if ($handled_setup || $handled_support_summary || $handled_ticket)
    <div class="handled-context-wide-panel handled-context-panel">
        <div class="handled-context-card-header">
            <div>
                <div class="handled-eyebrow">{{ __('Handled') }}</div>
                <h3>{{ __('Customer context') }}</h3>
            </div>
        </div>

        <div class="handled-context-wide-grid">
            <div>
                <div class="handled-eyebrow">{{ __('Visibility') }}</div>
                <h4>{{ __('Support + setup state') }}</h4>
                @if ($handled_setup || $handled_support_summary)
                    <dl class="handled-context-grid">
                        @if ($handled_support_summary)
                            <div>
                                <dt>{{ __('Active tickets') }}</dt>
                                <dd>{{ $handled_support_summary['active_tickets_total'] ?? 0 }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('Ticket history') }}</dt>
                                <dd>{{ $handled_support_summary['tickets_total'] ?? 0 }}</dd>
                            </div>
                        @endif
                        @if ($handled_setup)
                            <div>
                                <dt>{{ __('Setup progress') }}</dt>
                                <dd>{{ $handled_setup['completion_percentage'] ?? 0 }}%</dd>
                            </div>
                            <div>
                                <dt>{{ __('Next gap') }}</dt>
                                <dd>{{ $handled_setup['first_incomplete'] ?? __('Complete') }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="handled-context-empty">{{ __('No setup or support visibility data is available yet.') }}</p>
                @endif
            </div>

            <div>
                <div class="handled-eyebrow">{{ __('Linked ticket') }}</div>
                <h4>@if ($handled_ticket)#{{ $handled_ticket['id'] ?? '—' }} {{ $handled_ticket['subject'] ?? __('Support request') }}@else{{ __('Support request activity') }}@endif</h4>
                @if ($handled_ticket && !empty($handled_ticket['messages']) && is_array($handled_ticket['messages']))
                    <div class="handled-support-timeline">
                        @foreach ($handled_ticket['messages'] as $handled_message)
                            <div class="handled-support-timeline-item">
                                <div class="handled-support-timeline-meta">
                                    <strong>{{ ($handled_message['direction'] ?? '') === 'outbound' ? __('Support reply') : __('Customer message') }}</strong>
                                    <span>{{ $handled_message['created_at'] ?? '' }}</span>
                                </div>
                                <p>{{ $handled_message['preview'] ?? $handled_message['body_text'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @elseif ($handled_ticket)
                    <p class="handled-context-empty">{{ __('No linked message activity is available yet.') }}</p>
                @else
                    <p class="handled-context-empty">{{ __('No linked Handled ticket is available yet.') }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
