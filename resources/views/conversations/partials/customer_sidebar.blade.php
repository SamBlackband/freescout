@if (!empty($customer))
    @php
        $customer_location = array_filter([$customer->city, $customer->state, $customer->getCountryName()]);
        $previous_conversations_total = method_exists($prev_conversations, 'total') ? $prev_conversations->total() : count($prev_conversations);
        $handled_support_context = isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null;
        $handled_business = is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null;
        $handled_setup = is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null;
        $handled_support_summary = is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null;
        $handled_ticket = is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null;
    @endphp
    <div class="conv-customer-header"></div>
    <div class="handled-customer-sidebar">
        <div class="conv-customer-block conv-sidebar-block handled-context-card handled-customer-summary">
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
        </div>
        @if (isset($conversation))
            <div class="conv-sidebar-block handled-context-card">
                <div class="handled-context-card-header">
                    <div>
                        <div class="handled-eyebrow">{{ __('Case') }}</div>
                        <h4>#{{ $conversation->number }} {{ __('Overview') }}</h4>
                    </div>
                </div>
                <dl class="handled-context-grid">
                    <div>
                        <dt>{{ __('Status') }}</dt>
                        <dd>{{ $conversation->getStatusName() }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Mailbox') }}</dt>
                        <dd>{{ $mailbox->name ?? __('Shared inbox') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Assignee') }}</dt>
                        <dd>{{ $conversation->getAssigneeName(true) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Type') }}</dt>
                        <dd>{{ $conversation->getTypeName() }}</dd>
                    </div>
                    @if ($conversation->getChannelName())
                        <div>
                            <dt>{{ __('Channel') }}</dt>
                            <dd>{{ $conversation->getChannelName() }}</dd>
                        </div>
                    @endif
                    @if ($conversation->getWaitingSince())
                        <div>
                            <dt>{{ __('Waiting') }}</dt>
                            <dd>{{ $conversation->getWaitingSince() }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>{{ __('Opened') }}</dt>
                        <dd>{{ App\User::dateDiffForHumans($conversation->created_at) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('Last touch') }}</dt>
                        <dd>{{ App\User::dateDiffForHumans($conversation->last_reply_at ?: $conversation->updated_at) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('History') }}</dt>
                        <dd>{{ $previous_conversations_total }} {{ __('conversations') }}</dd>
                    </div>
                </dl>
            </div>
        @endif
        @if ($customer->company || $customer->job_title || $customer_location)
            <div class="conv-sidebar-block handled-context-card">
                <div class="handled-context-card-header">
                    <div>
                        <div class="handled-eyebrow">{{ __('Business') }}</div>
                        <h4>{{ __('Account details') }}</h4>
                    </div>
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
        <div class="conv-sidebar-block handled-context-card">
            <div class="handled-context-card-header">
                <div>
                    <div class="handled-eyebrow">{{ __('Handled') }}</div>
                    <h4>{{ __('Account in app') }}</h4>
                </div>
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
            <div class="conv-sidebar-block handled-context-card">
                <div class="handled-context-card-header">
                    <div>
                        <div class="handled-eyebrow">{{ __('Visibility') }}</div>
                        <h4>{{ __('Support + setup state') }}</h4>
                    </div>
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
        <div class="conv-sidebar-block handled-context-card">
            <div class="handled-context-card-header">
                <div>
                    <div class="handled-eyebrow">{{ __('Linked ticket') }}</div>
                    <h4>@if ($handled_ticket)#{{ $handled_ticket['id'] ?? '—' }} {{ $handled_ticket['subject'] ?? __('Support request') }}@else{{ __('Support request') }}@endif</h4>
                </div>
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
