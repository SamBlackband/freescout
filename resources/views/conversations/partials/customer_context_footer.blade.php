@php
    $handled_support_context = $handled_support_context ?? (isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null);
    $handled_business = $handled_business ?? (is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null);
    $handled_business_metrics = is_array($handled_support_context) ? ($handled_support_context['business_metrics'] ?? null) : null;
    $handled_setup = $handled_setup ?? (is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null);
    $handled_support_summary = $handled_support_summary ?? (is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null);
    $handled_ticket = $handled_ticket ?? (is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null);
    $handled_actions = is_array($handled_support_context) ? ($handled_support_context['actions'] ?? null) : null;
    $handled_matched_by = is_array($handled_support_context) ? ($handled_support_context['matched_by'] ?? null) : null;
    $customer_location = array_filter([$customer->city, $customer->state, $customer->getCountryName()]);
    $websites = $customer->getWebsites();
    $social_profiles = $customer->getSocialProfiles();
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
    $handled_currency = $handled_business['currency'] ?? '£';
    $handled_bookings_value = number_format(((int) ($handled_business_metrics['bookings_value_pence'] ?? 0)) / 100, 2);
@endphp

@if ($handled_business || $handled_business_metrics || $handled_actions || $handled_setup || $handled_support_summary || $handled_ticket || $customer->company || $customer->job_title || $customer_location || $websites || $social_profiles || $customer->notes)
    <div class="handled-conversation-footer">
        <div class="handled-conversation-footer-grid">
            <section class="handled-conversation-footer-card handled-context-card handled-context-panel">
                <div class="handled-context-card-header">
                    <div>
                        <div class="handled-eyebrow">{{ __('Handled') }}</div>
                        <h3>{{ __('Customer context') }}</h3>
                    </div>
                </div>

                @if ($handled_business || $handled_support_summary || $handled_setup || $handled_business_metrics)
                    <div class="handled-context-metrics">
                        @if ($handled_business)
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Business') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_business['name'] ?? '—' }}</span>
                            </div>
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Owner') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_business['owner_name'] ?? '—' }}</span>
                            </div>
                        @endif
                        @if ($handled_support_summary)
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Active tickets') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_support_summary['active_tickets_total'] ?? 0 }}</span>
                            </div>
                        @endif
                        @if ($handled_setup)
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Setup progress') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_setup['completion_percentage'] ?? 0 }}%</span>
                            </div>
                        @endif
                        @if ($handled_business_metrics)
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Conversations') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_business_metrics['conversation_total'] ?? 0 }}</span>
                            </div>
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Bookings') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_business_metrics['bookings_total'] ?? 0 }}</span>
                            </div>
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Upcoming bookings') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_business_metrics['upcoming_bookings_total'] ?? 0 }}</span>
                            </div>
                            <div class="handled-context-metric">
                                <span class="handled-context-metric-label">{{ __('Booking value') }}</span>
                                <span class="handled-context-metric-value">{{ $handled_currency }}{{ $handled_bookings_value }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($handled_actions && (!empty($handled_actions['booking_url']) || !empty($handled_actions['instagram_url'])))
                    <div class="handled-customer-links">
                        @if (!empty($handled_actions['booking_url']))
                            <a href="{{ $handled_actions['booking_url'] }}" target="_blank">{{ __('Open booking page') }}</a>
                        @endif
                        @if (!empty($handled_actions['instagram_url']))
                            <a href="{{ $handled_actions['instagram_url'] }}" target="_blank">{{ __('Open Instagram') }}</a>
                        @endif
                    </div>
                @endif

                <div class="handled-context-detail-grid">
                    @if ($customer->company || $customer->job_title || $customer_location || $customer->address || $customer->zip || $ordered_emails || $phones || $websites || $social_profiles || $customer->notes)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Customer profile') }}</div>
                            <h4>{{ __('Extended details') }}</h4>
                            <dl class="handled-context-grid">
                                @if ($ordered_emails)
                                    <div>
                                        <dt>{{ __('Emails') }}</dt>
                                        <dd>{{ implode(', ', $ordered_emails) }}</dd>
                                    </div>
                                @endif
                                @if ($phones)
                                    <div>
                                        <dt>{{ __('Phones') }}</dt>
                                        <dd>{{ implode(', ', array_column($phones, 'value')) }}</dd>
                                    </div>
                                @endif
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
                                @if ($customer->address || $customer->zip)
                                    <div>
                                        <dt>{{ __('Address') }}</dt>
                                        <dd>{{ trim(($customer->address ? $customer->address : '').(($customer->address && $customer->zip) ? ', ' : '').($customer->zip ? $customer->zip : '')) }}</dd>
                                    </div>
                                @endif
                            </dl>
                            @if ($websites || $social_profiles)
                                <div class="handled-customer-links">
                                    @foreach ($websites as $website)
                                        <a href="{{ $website }}" target="_blank">{{ parse_url($website, PHP_URL_HOST) ?: __('Website') }}</a>
                                    @endforeach
                                    @foreach ($social_profiles as $sp)
                                        @php($formatted_social = App\Customer::formatSocialProfile($sp))
                                        <a href="{{ $formatted_social['value_url'] }}" target="_blank">{{ $formatted_social['type_name'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                            @if ($customer->notes)
                                <div class="handled-customer-note">{{ $customer->notes }}</div>
                            @endif
                            @action('customer.profile.extra', $customer, $conversation ?? '')
                            @action('customer.profile_data', $customer, $conversation ?? '')
                        </section>
                    @endif

                    @if ($handled_business && (!empty($handled_business['plan_tier']) || !empty($handled_business['brand_id']) || !empty($handled_business['instagram_handle']) || !empty($handled_business['booking_url']) || !empty($handled_business['subscription_status']) || array_key_exists('responses_paused', $handled_business)))
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Handled account') }}</div>
                            <h4>{{ __('Operational details') }}</h4>
                            <dl class="handled-context-grid">
                                @if (!empty($handled_business['plan_tier']))
                                    <div>
                                        <dt>{{ __('Plan') }}</dt>
                                        <dd>{{ $handled_business['plan_tier'] }}</dd>
                                    </div>
                                @endif
                                @if (!empty($handled_business['brand_id']))
                                    <div>
                                        <dt>{{ __('Brand') }}</dt>
                                        <dd>{{ $handled_business['brand_id'] }}</dd>
                                    </div>
                                @endif
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
                                @if (!empty($handled_business['subscription_status']))
                                    <div>
                                        <dt>{{ __('Subscription') }}</dt>
                                        <dd>{{ $handled_business['subscription_status'] }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt>{{ __('Responses paused') }}</dt>
                                    <dd>{{ !empty($handled_business['responses_paused']) ? __('Yes') : __('No') }}</dd>
                                </div>
                            </dl>
                        </section>
                    @endif

                    @if ($handled_setup || $handled_support_summary || $handled_business_metrics || $handled_matched_by)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Visibility') }}</div>
                            <h4>{{ __('Support + setup state') }}</h4>
                            @if ($handled_setup || $handled_support_summary || $handled_business_metrics || $handled_matched_by)
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
                                        @if (!empty($handled_support_summary['latest_activity_at']))
                                            <div>
                                                <dt>{{ __('Latest support activity') }}</dt>
                                                <dd>{{ $handled_support_summary['latest_activity_at'] }}</dd>
                                            </div>
                                        @endif
                                    @endif
                                    @if ($handled_setup)
                                        <div>
                                            <dt>{{ __('Next gap') }}</dt>
                                            <dd>{{ $handled_setup['first_incomplete'] ?? __('Complete') }}</dd>
                                        </div>
                                    @endif
                                    @if ($handled_business_metrics)
                                        <div>
                                            <dt>{{ __('Customer records') }}</dt>
                                            <dd>{{ $handled_business_metrics['customer_profiles_total'] ?? 0 }}</dd>
                                        </div>
                                        @if (!empty($handled_business_metrics['latest_booking_at']))
                                            <div>
                                                <dt>{{ __('Latest booking') }}</dt>
                                                <dd>{{ $handled_business_metrics['latest_booking_at'] }}</dd>
                                            </div>
                                        @endif
                                    @endif
                                    @if ($handled_matched_by)
                                        <div>
                                            <dt>{{ __('Context matched by') }}</dt>
                                            <dd>{{ $handled_matched_by }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @else
                                <p class="handled-context-empty">{{ __('No setup or support visibility data is available yet.') }}</p>
                            @endif
                        </section>
                    @endif
                </div>
            </section>

            <section class="handled-conversation-footer-card handled-context-card handled-context-panel">
                <div class="handled-context-card-header">
                    <div>
                        <div class="handled-eyebrow">{{ __('Ticket activity') }}</div>
                        <h4>{{ __('Recent synced support activity') }}</h4>
                    </div>
                </div>
                @if ($handled_ticket)
                    <dl class="handled-context-grid">
                        @if (!empty($handled_ticket['message_count']))
                            <div>
                                <dt>{{ __('Messages synced') }}</dt>
                                <dd>{{ $handled_ticket['message_count'] }}</dd>
                            </div>
                        @endif
                        @if (!empty($handled_ticket['last_message_at']))
                            <div>
                                <dt>{{ __('Last message') }}</dt>
                                <dd>{{ $handled_ticket['last_message_at'] }}</dd>
                            </div>
                        @endif
                        @if (!empty($handled_ticket['portal_reply_enabled']))
                            <div>
                                <dt>{{ __('Portal reply') }}</dt>
                                <dd>{{ __('Enabled') }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif
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
            </section>
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
    </div>
@endif
