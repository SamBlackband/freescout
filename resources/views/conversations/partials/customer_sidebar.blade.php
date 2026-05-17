@if (!empty($customer))
    @php
        $customer_location = array_filter([$customer->city, $customer->state, $customer->getCountryName()]);
        $handled_support_context = isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null;
        $handled_business = is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null;
        $handled_setup = is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null;
        $handled_support_summary = is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null;
        $handled_ticket = is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null;
    @endphp
    <style {!! \Helper::cspNonceAttr() !!}>
        .handled-context-panel {
            padding-bottom: 18px;
            overflow: visible;
        }

        .handled-context-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .handled-context-section {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid rgba(216, 223, 230, 0.9);
        }

        .handled-customer-summary .customer-snippet {
            display: flex;
            align-items: flex-start;
            min-height: 0;
            gap: 16px;
        }

        .handled-customer-summary .customer-photo-container {
            flex: 0 0 auto;
            float: none;
            width: auto;
            max-width: none;
            height: auto;
            margin: 0;
        }

        .handled-customer-summary .customer-photo {
            width: 64px;
            height: 64px;
            max-width: none;
            max-height: none;
            border: 3px solid #e6eef7;
            object-fit: cover;
        }

        .handled-customer-summary .customer-data {
            flex: 1 1 auto;
            min-width: 0;
            margin: 0;
            padding: 0;
        }

        .handled-customer-summary .customer-name {
            display: block;
            float: none;
            margin: 0 0 8px;
            color: #0f1923;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        .handled-customer-summary .customer-tags .fs-tag {
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .handled-customer-summary .customer-contacts {
            margin: 0;
            padding-top: 0;
        }

        .handled-customer-summary .customer-contacts li {
            display: block;
        }

        .handled-customer-summary .customer-contacts li + li {
            margin-top: 4px;
        }

        .handled-customer-summary .customer-extra {
            margin-top: 12px;
        }

        .handled-customer-summary .customer-social-profiles {
            position: static;
            max-width: none;
            margin: 8px 0 0;
            overflow: visible;
            white-space: normal;
        }

        .handled-context-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 12px;
            margin: 0;
        }

        .handled-context-grid > div {
            min-width: 0;
        }

        .handled-context-grid dt {
            margin-bottom: 5px;
            color: #5f6f82;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .handled-context-grid dd {
            margin: 0;
            color: #0f1923;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
        }

        .handled-support-timeline {
            margin-top: 14px;
            display: grid;
            gap: 12px;
        }

        .handled-support-timeline-item {
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
        }

        .handled-support-timeline-item + .handled-support-timeline-item {
            padding-top: 12px;
            border-top: 1px solid rgba(216, 223, 230, 0.75);
        }

        .handled-support-timeline-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
            color: #5f6f82;
            font-size: 12px;
        }

        .handled-support-timeline-item p,
        .handled-context-empty {
            margin: 0;
            color: #0f1923;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
    <div class="conv-customer-header"></div>
    <div class="conv-customer-block conv-sidebar-block handled-context-panel handled-customer-summary">
        <div class="handled-context-card-header">
            <div>
                <div class="handled-eyebrow">{{ __('Customer') }}</div>
                <h3>{{ __('Customer context') }}</h3>
            </div>
            @if (isset($conversation))
                <div class="dropdown customer-trigger" data-toggle="tooltip" title="{{ __("Settings") }}">
                    <a href="#" class="dropdown-toggle glyphicon glyphicon-cog" data-toggle="dropdown"></a>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li role="presentation"><a href="{{ route('customers.update', ['id' => $customer->id]) }}" tabindex="-1" role="menuitem">{{ __("Edit Profile") }}</a></li>
                        @if (!$conversation->isChat())
                            <li role="presentation"><a href="{{ route('conversations.ajax_html', array_merge(['action' =>
                    'change_customer'], \Request::all(), ['conversation_id' => $conversation->id]) ) }}" data-trigger="modal" data-modal-title="{{ __("Change Customer") }}" data-modal-no-footer="true" data-modal-on-show="changeCustomerInit" tabindex="-1" role="menuitem">{{ __("Change Customer") }}</a></li>
                        @endif
                        {{ \Eventy::action('conversation.customer.menu', $customer, $conversation) }}
                        {{-- No need to use this --}}
                        {{ \Eventy::action('customer_profile.menu', $customer, $conversation) }}
                    </ul>
                </div>
            @endif
        </div>
        @include('customers/profile_snippet', ['customer' => $customer, 'main_email' => $conversation->customer_email ?? '', 'conversation' => $conversation ?? null])
        @if ($customer->company || $customer->job_title || $customer_location)
            <div class="handled-context-section">
                <div>
                    <div class="handled-eyebrow">{{ __('Business') }}</div>
                    <h4>{{ __('Account details') }}</h4>
                </div>
                <dl class="handled-context-grid">
                    @if ($customer->company)
                        <div>
                            <dt>{{ __('Company') }}</dt>
                            <dd>{{ $customer->company }}</dd>
                        </div>
                    @endif
                    @if ($customer->job_title)
                        <div>
                            <dt>{{ __('Role') }}</dt>
                            <dd>{{ $customer->job_title }}</dd>
                        </div>
                    @endif
                    @if ($customer_location)
                        <div>
                            <dt>{{ __('Location') }}</dt>
                            <dd>{{ implode(', ', $customer_location) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
        <div class="handled-context-section">
            <div>
                <div class="handled-eyebrow">{{ __('Handled') }}</div>
                <h4>{{ __('Account in app') }}</h4>
            </div>
            @if ($handled_business)
                <dl class="handled-context-grid">
                    <div>
                        <dt>{{ __('Business') }}</dt>
                        <dd>{{ $handled_business['name'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Owner') }}</dt>
                        <dd>{{ $handled_business['owner_name'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Plan') }}</dt>
                        <dd>{{ $handled_business['plan_tier'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Brand') }}</dt>
                        <dd>{{ $handled_business['brand_id'] ?? '—' }}</dd>
                    </div>
                    @if (!empty($handled_business['instagram_handle']))
                        <div>
                            <dt>{{ __('Instagram') }}</dt>
                            <dd>{{ $handled_business['instagram_handle'] }}</dd>
                        </div>
                    @endif
                    @if (!empty($handled_business['booking_url']))
                        <div>
                            <dt>{{ __('Booking') }}</dt>
                            <dd><a class="handled-context-link" href="{{ $handled_business['booking_url'] }}" target="_blank">{{ __('Open link') }}</a></dd>
                        </div>
                    @endif
                </dl>
            @else
                <p class="handled-context-empty">{{ __('No Handled account is linked to this conversation yet.') }}</p>
            @endif
        </div>
        @if ($handled_setup || $handled_support_summary)
            <div class="handled-context-section">
                <div>
                    <div class="handled-eyebrow">{{ __('Visibility') }}</div>
                    <h4>{{ __('Support + setup state') }}</h4>
                </div>
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
            </div>
        @endif
        <div class="handled-context-section">
            <div>
                <div class="handled-eyebrow">{{ __('Linked ticket') }}</div>
                <h4>@if ($handled_ticket)#{{ $handled_ticket['id'] ?? '—' }} {{ $handled_ticket['subject'] ?? __('Support request') }}@else{{ __('Support request') }}@endif</h4>
            </div>
            @if ($handled_ticket)
                <dl class="handled-context-grid">
                    <div>
                        <dt>{{ __('Status') }}</dt>
                        <dd>{{ $handled_ticket['status'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Priority') }}</dt>
                        <dd>{{ $handled_ticket['priority'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Last channel') }}</dt>
                        <dd>{{ $handled_ticket['last_channel'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Sync state') }}</dt>
                        <dd>{{ $handled_ticket['sync_status'] ?? '—' }}</dd>
                    </div>
                </dl>
                @if (!empty($handled_ticket['messages']) && is_array($handled_ticket['messages']))
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
                @endif
            @else
                <p class="handled-context-empty">{{ __('No linked Handled ticket is available yet.') }}</p>
            @endif
        </div>
    </div>
    @if (isset($conversation) && isset($mailbox))
     	@action('conversation.before_prev_convs', $customer, $conversation, $mailbox)
    @endif
    @if (count($prev_conversations))
        @include('conversations/partials/prev_convs_short')
    @endif
    @if (isset($conversation) && isset($mailbox))
    	@action('conversation.after_prev_convs', $customer, $conversation, $mailbox)
    @endif
@endif
