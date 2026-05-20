@if (!empty($customer))
    @php
        $customer_location = $customer_location ?? array_filter([$customer->city, $customer->state, $customer->getCountryName()]);
        $handled_support_context = $handled_support_context ?? (isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null);
        $handled_business = $handled_business ?? (is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null);
        $handled_setup = $handled_setup ?? (is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null);
        $handled_support_summary = $handled_support_summary ?? (is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null);
        $handled_ticket = $handled_ticket ?? (is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null);
        $handled_business_name = is_array($handled_business) ? ($handled_business['name'] ?? null) : null;
        $handled_business_owner_name = is_array($handled_business) ? ($handled_business['owner_name'] ?? null) : null;
        $handled_ticket_title = is_array($handled_ticket) ? ($handled_ticket['subject'] ?? null) : null;
        $handled_ticket_number = is_array($handled_ticket) ? ($handled_ticket['id'] ?? null) : null;
        $handled_ticket_status = is_array($handled_ticket) ? ($handled_ticket['status'] ?? null) : null;
        $handled_ticket_priority = is_array($handled_ticket) ? ($handled_ticket['priority'] ?? null) : null;
        $handled_ticket_sync_status = is_array($handled_ticket) ? ($handled_ticket['sync_status'] ?? null) : null;
    @endphp
    <style {!! \Helper::cspNonceAttr() !!}>
        .handled-context-panel {
            display: flow-root;
            padding-bottom: 0px;
            overflow: visible;
        }

        .handled-customer-summary.conv-sidebar-block {
            margin-bottom: 0;
        }

        .handled-context-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 10px;
        }

        .handled-context-card-header h3 {
            margin: 0;
            color: #00c2a8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .handled-context-section {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(216, 223, 230, 0.9);
        }

        .handled-customer-summary .handled-customer-identity {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .handled-customer-summary .handled-customer-avatar {
            flex: 0 0 auto;
        }

        .handled-customer-summary .handled-customer-avatar img {
            display: block;
            width: 56px;
            height: 56px;
            border: 2px solid #e6eef7;
            border-radius: 50%;
            object-fit: cover;
        }

        .handled-customer-summary .handled-customer-primary {
            flex: 1 1 auto;
            min-width: 0;
        }

        .handled-customer-summary .handled-customer-name {
            display: block;
            margin: 0 0 8px;
            color: #0f1923;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .handled-customer-summary .handled-customer-channels {
            margin-bottom: 8px;
        }

        .handled-customer-summary .handled-customer-channels .fs-tag {
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .handled-customer-summary .handled-customer-contacts {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .handled-customer-summary .handled-customer-contacts li {
            display: block;
        }

        .handled-customer-summary .handled-customer-contacts li + li {
            margin-top: 4px;
        }

        .handled-customer-summary .handled-customer-contact {
            color: #0f1923;
            font-size: 12px;
            line-height: 1.45;
        }

        .handled-customer-summary .handled-customer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 10px;
            margin-top: 10px;
        }

        .handled-customer-summary .handled-customer-links a {
            color: #607d9f;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .handled-customer-summary .handled-customer-links a:hover,
        .handled-customer-summary .handled-customer-links a:focus {
            color: #0f1923;
            text-decoration: none;
        }

        .handled-customer-summary .handled-customer-note {
            margin-top: 12px;
            color: #5f6f82;
            font-size: 13px;
            font-style: italic;
            line-height: 1.5;
        }

        .handled-context-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
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
            font-size: 13px;
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
                <h3>{{ __('Customer') }}</h3>
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
        @php
            $channels = $customer->getChannels();
            $phones = $customer->getPhones();
            $ordered_emails = [];
            if (!empty($conversation->customer_email)) {
                foreach ($customer->emails as $email) {
                    if ($email->email == $conversation->customer_email) {
                        $ordered_emails[] = $email->email;
                    }
                }
            }
            foreach ($customer->emails as $email) {
                if (!in_array($email->email, $ordered_emails, true)) {
                    $ordered_emails[] = $email->email;
                }
            }
            $primary_email = $ordered_emails[0] ?? null;
            $primary_phone = !empty($phones[0]['value']) ? $phones[0]['value'] : null;
        @endphp
        <div class="handled-customer-identity">
            <div class="handled-customer-avatar">
                <img src="{{ $customer->getPhotoUrl() }}" alt="">
            </div>
            <div class="handled-customer-primary">
                @if ($customer->getFullName(true, true))
                    <a href="{{ route('customers.update', ['id' => $customer->id]) }}" class="handled-customer-name">{{ $customer->getFullName(true, true) }}</a>
                @endif
                @if (count($channels))
                    <div class="handled-customer-channels">
                        @foreach ($channels as $channel)
                            <span class="fs-tag"><span class="fs-tag-name">{{ $channel->getChannelName() }}</span></span>
                        @endforeach
                        @action('customer.tags', $customer, $conversation ?? null)
                    </div>
                @endif
                <ul class="handled-customer-contacts">
                    @if ($primary_email)
                        <li><span class="handled-customer-contact">{{ $primary_email }}</span></li>
                    @endif
                    @if ($primary_phone)
                        <li><span class="handled-customer-contact">{{ $primary_phone }}</span></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="handled-context-section">
            <div>
                <div class="handled-eyebrow">{{ __('Handled') }}</div>
                <h4>{{ __('Account in app') }}</h4>
            </div>
            @if ($handled_business)
                <dl class="handled-context-grid">
                    <div>
                        <dt>{{ __('Business') }}</dt>
                        <dd>{{ $handled_business_name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Owner') }}</dt>
                        <dd>{{ $handled_business_owner_name ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="handled-context-empty">{{ __('No Handled account is linked to this conversation yet.') }}</p>
            @endif
        </div>
        <div class="handled-context-section">
            <div>
                <div class="handled-eyebrow">{{ __('Linked ticket') }}</div>
                <h4>@if ($handled_ticket)#{{ $handled_ticket_number ?: '—' }} {{ $handled_ticket_title ?: __('Support request') }}@else{{ __('Support request') }}@endif</h4>
            </div>
            @if ($handled_ticket)
                <dl class="handled-context-grid">
                    <div>
                        <dt>{{ __('Status') }}</dt>
                        <dd>{{ $handled_ticket_status ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Priority') }}</dt>
                        <dd>{{ $handled_ticket_priority ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Sync state') }}</dt>
                        <dd>{{ $handled_ticket_sync_status ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="handled-context-empty">{{ __('No linked Handled ticket is available yet.') }}</p>
            @endif
        </div>
    </div>
@endif
