@php
    $handled_support_context = $handled_support_context ?? (isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null);
    $handled_business = $handled_business ?? (is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null);
    $handled_business_metrics = is_array($handled_support_context) ? ($handled_support_context['business_metrics'] ?? null) : null;
    $handled_owner = is_array($handled_support_context) ? ($handled_support_context['owner'] ?? null) : null;
    $handled_account_health = is_array($handled_support_context) ? ($handled_support_context['account_health'] ?? null) : null;
    $handled_diagnostics = is_array($handled_support_context) ? ($handled_support_context['diagnostics'] ?? null) : null;
    $handled_history = is_array($handled_support_context) ? ($handled_support_context['history'] ?? null) : null;
    $handled_setup = $handled_setup ?? (is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null);
    $handled_support_summary = $handled_support_summary ?? (is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null);
    $handled_ticket = $handled_ticket ?? (is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null);
    $handled_activity = is_array($handled_support_context) ? ($handled_support_context['activity'] ?? null) : null;
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
    $handled_action_links = array_filter([
        [
            'url' => $handled_actions['admin_shell_url'] ?? null,
            'label' => __('Open admin shell'),
        ],
        [
            'url' => $handled_actions['admin_diagnostics_url'] ?? null,
            'label' => __('Open diagnostics JSON'),
        ],
        [
            'url' => $handled_actions['mailto_business'] ?? null,
            'label' => __('Email account owner'),
        ],
        [
            'url' => $handled_actions['booking_url'] ?? null,
            'label' => __('Open booking page'),
        ],
        [
            'url' => $handled_actions['instagram_url'] ?? null,
            'label' => __('Open Instagram'),
        ],
    ], function ($action) {
        return !empty($action['url']);
    });
    $handled_recent_email_events = is_array($handled_history['recent_email_events'] ?? null) ? $handled_history['recent_email_events'] : [];
    $handled_recent_retries = is_array($handled_history['recent_outbound_retries'] ?? null) ? $handled_history['recent_outbound_retries'] : [];
    $handled_channel_breakdown = is_array($handled_diagnostics['channel_breakdown'] ?? null) ? $handled_diagnostics['channel_breakdown'] : [];
    $handled_activity_items = is_array($handled_activity['items'] ?? null) ? $handled_activity['items'] : [];
    $handled_business_id = $handled_business['business_id'] ?? ($handled_business['id'] ?? null);
    $handled_ticket_id = $handled_ticket['ticket_id'] ?? ($handled_ticket['id'] ?? null);
    $handled_customer_email = !empty($conversation->customer_email) ? $conversation->customer_email : ($ordered_emails[0] ?? null);
    $handled_responses_paused = !empty($handled_business['responses_paused']);
    $handled_support_writeback_visible = (bool) config('app.handled_support_writeback_ui_enabled');
    $handled_support_action_enabled = $handled_support_writeback_visible && !empty($handled_business_id) && !empty($conversation->id);
@endphp

@if ($handled_business || $handled_business_metrics || $handled_owner || $handled_account_health || $handled_diagnostics || $handled_history || $handled_actions || $handled_setup || $handled_support_summary || $handled_ticket || $handled_activity || $customer->company || $customer->job_title || $customer_location || $websites || $social_profiles || $customer->notes)
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

                    @if ($handled_owner && (!empty($handled_owner['user_emails']) || array_key_exists('verified_user_count', $handled_owner) || !empty($handled_owner['last_login_at']) || array_key_exists('days_since_last_login', $handled_owner)))
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Owner coverage') }}</div>
                            <h4>{{ __('Owner + operator signals') }}</h4>
                            <dl class="handled-context-grid">
                                @if (!empty($handled_owner['user_emails']) && is_array($handled_owner['user_emails']))
                                    <div>
                                        <dt>{{ __('Owner emails') }}</dt>
                                        <dd>{{ implode(', ', $handled_owner['user_emails']) }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt>{{ __('Verified users') }}</dt>
                                    <dd>{{ $handled_owner['verified_user_count'] ?? 0 }} / {{ $handled_owner['user_count'] ?? 0 }}</dd>
                                </div>
                                @if (!empty($handled_owner['last_login_at']))
                                    <div>
                                        <dt>{{ __('Last login') }}</dt>
                                        <dd>{{ $handled_owner['last_login_at'] }}</dd>
                                    </div>
                                @endif
                                @if (array_key_exists('days_since_last_login', $handled_owner) && $handled_owner['days_since_last_login'] !== null)
                                    <div>
                                        <dt>{{ __('Days since login') }}</dt>
                                        <dd>{{ $handled_owner['days_since_last_login'] }}</dd>
                                    </div>
                                @endif
                                @if (array_key_exists('is_operator', $handled_business))
                                    <div>
                                        <dt>{{ __('Operator entity') }}</dt>
                                        <dd>{{ !empty($handled_business['is_operator']) ? __('Yes') : __('No') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </section>
                    @endif

                    @if ($handled_account_health && (!empty($handled_account_health['holiday_return_date']) || array_key_exists('holiday_mode_active', $handled_account_health) || array_key_exists('onboarding_seen', $handled_account_health) || !empty($handled_account_health['setup_started_at']) || !empty($handled_account_health['setup_last_activity_at']) || !empty($handled_account_health['setup_completed_at']) || !empty($handled_account_health['low_confidence_behaviour'])))
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Account health') }}</div>
                            <h4>{{ __('Read-only operating state') }}</h4>
                            <dl class="handled-context-grid">
                                <div>
                                    <dt>{{ __('Holiday mode') }}</dt>
                                    <dd>{{ !empty($handled_account_health['holiday_mode_active']) ? __('Active') : __('Off') }}</dd>
                                </div>
                                @if (!empty($handled_account_health['holiday_return_date']))
                                    <div>
                                        <dt>{{ __('Holiday return') }}</dt>
                                        <dd>{{ $handled_account_health['holiday_return_date'] }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt>{{ __('Onboarding seen') }}</dt>
                                    <dd>{{ !empty($handled_account_health['onboarding_seen']) ? __('Yes') : __('No') }}</dd>
                                </div>
                                @if (!empty($handled_account_health['low_confidence_behaviour']))
                                    <div>
                                        <dt>{{ __('Low-confidence mode') }}</dt>
                                        <dd>{{ str_replace('_', ' ', $handled_account_health['low_confidence_behaviour']) }}</dd>
                                    </div>
                                @endif
                                @if (!empty($handled_account_health['setup_started_at']))
                                    <div>
                                        <dt>{{ __('Setup started') }}</dt>
                                        <dd>{{ $handled_account_health['setup_started_at'] }}</dd>
                                    </div>
                                @endif
                                @if (!empty($handled_account_health['setup_last_activity_at']))
                                    <div>
                                        <dt>{{ __('Setup last active') }}</dt>
                                        <dd>{{ $handled_account_health['setup_last_activity_at'] }}</dd>
                                    </div>
                                @endif
                                @if (!empty($handled_account_health['setup_completed_at']))
                                    <div>
                                        <dt>{{ __('Setup completed') }}</dt>
                                        <dd>{{ $handled_account_health['setup_completed_at'] }}</dd>
                                    </div>
                                @endif
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
                        <div class="handled-eyebrow">{{ __('Support diagnostics') }}</div>
                        <h4>{{ __('History + safe actions') }}</h4>
                    </div>
                </div>
                @if ($handled_diagnostics)
                    <div class="handled-context-metrics">
                        <div class="handled-context-metric">
                            <span class="handled-context-metric-label">{{ __('Open alerts') }}</span>
                            <span class="handled-context-metric-value">{{ $handled_diagnostics['unresolved_alerts_total'] ?? 0 }}</span>
                        </div>
                        <div class="handled-context-metric">
                            <span class="handled-context-metric-label">{{ __('Customer msgs (7d)') }}</span>
                            <span class="handled-context-metric-value">{{ $handled_diagnostics['customer_messages_7d'] ?? 0 }}</span>
                        </div>
                        <div class="handled-context-metric">
                            <span class="handled-context-metric-label">{{ __('Pending retries') }}</span>
                            <span class="handled-context-metric-value">{{ $handled_diagnostics['pending_retries_total'] ?? 0 }}</span>
                        </div>
                        <div class="handled-context-metric">
                            <span class="handled-context-metric-label">{{ __('Failed emails (14d)') }}</span>
                            <span class="handled-context-metric-value">{{ $handled_diagnostics['failed_email_events_14d'] ?? 0 }}</span>
                        </div>
                    </div>
                @endif

                <div class="handled-context-detail-grid">
                    <section class="handled-context-subcard">
                        <div class="handled-eyebrow">{{ __('Writeback actions') }}</div>
                        <h4>{{ __('Backend-managed controls') }}</h4>
                        <dl class="handled-context-grid">
                            <div>
                                <dt>{{ __('Responses paused') }}</dt>
                                <dd>{{ $handled_responses_paused ? __('Yes') : __('No') }}</dd>
                            </div>
                            @if ($handled_customer_email)
                                <div>
                                    <dt>{{ __('Reset email target') }}</dt>
                                    <dd>{{ $handled_customer_email }}</dd>
                                </div>
                            @endif
                        </dl>
                        @if ($handled_support_action_enabled)
                            <p class="handled-context-empty">{{ __('Confirm each action to proxy it through Handled securely.') }}</p>
                            <div class="handled-writeback-actions">
                                <button
                                    type="button"
                                    class="btn btn-default btn-sm handled-support-action"
                                    data-action="handled_support_password_reset"
                                    data-business-id="{{ $handled_business_id }}"
                                    data-ticket-id="{{ $handled_ticket_id }}"
                                    data-customer-email="{{ $handled_customer_email }}"
                                    data-confirm="{{ __('Send the standard password reset email now?') }}"
                                >{{ __('Send password reset email') }}</button>
                                <button
                                    type="button"
                                    class="btn btn-default btn-sm handled-support-action"
                                    data-action="handled_support_account_state"
                                    data-business-id="{{ $handled_business_id }}"
                                    data-ticket-id="{{ $handled_ticket_id }}"
                                    data-customer-email="{{ $handled_customer_email }}"
                                    data-responses-paused="{{ $handled_responses_paused ? 0 : 1 }}"
                                    data-confirm="{{ $handled_responses_paused ? __('Resume automated responses for this business?') : __('Pause automated responses for this business?') }}"
                                >{{ $handled_responses_paused ? __('Resume responses') : __('Pause responses') }}</button>
                            </div>
                        @else
                            <p class="handled-context-empty">{{ __('Writeback controls are unavailable until Handled business context is loaded.') }}</p>
                        @endif
                    </section>

                    @if ($handled_action_links)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Safe actions') }}</div>
                            <h4>{{ __('Support-owned links') }}</h4>
                            <div class="handled-customer-links">
                                @foreach ($handled_action_links as $handled_action)
                                    <a href="{{ $handled_action['url'] }}" target="_blank">{{ $handled_action['label'] }}</a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($handled_diagnostics)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Diagnostics') }}</div>
                            <h4>{{ __('Filtered platform signals') }}</h4>
                            <dl class="handled-context-grid">
                                <div>
                                    <dt>{{ __('Recent alerts (7d)') }}</dt>
                                    <dd>{{ $handled_diagnostics['recent_alerts_total'] ?? 0 }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('Assistant msgs (7d)') }}</dt>
                                    <dd>{{ $handled_diagnostics['assistant_messages_7d'] ?? 0 }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('Support msgs (14d)') }}</dt>
                                    <dd>{{ $handled_diagnostics['support_messages_14d'] ?? 0 }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('Failed retries') }}</dt>
                                    <dd>{{ $handled_diagnostics['failed_retries_total'] ?? 0 }}</dd>
                                </div>
                            </dl>
                            @if ($handled_channel_breakdown)
                                <div class="handled-support-timeline">
                                    @foreach ($handled_channel_breakdown as $channel)
                                        <div class="handled-support-timeline-item">
                                            <div class="handled-support-timeline-meta">
                                                <strong>{{ ucfirst($channel['channel'] ?? __('Unknown')) }}</strong>
                                                <span>{{ $channel['count'] ?? 0 }} {{ __('conversations') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    @if ($handled_recent_retries || $handled_recent_email_events)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Delivery history') }}</div>
                            <h4>{{ __('Recent retries + email events') }}</h4>
                            <div class="handled-support-timeline">
                                @foreach ($handled_recent_retries as $retry)
                                    <div class="handled-support-timeline-item">
                                        <div class="handled-support-timeline-meta">
                                            <strong>{{ strtoupper($retry['channel'] ?? __('Retry')) }} · {{ ucfirst($retry['status'] ?? __('Unknown')) }}</strong>
                                            <span>{{ $retry['last_attempted_at'] ?? $retry['created_at'] ?? '' }}</span>
                                        </div>
                                        <p>
                                            {{ __('Attempt :attempt of :max', ['attempt' => $retry['attempt_count'] ?? 0, 'max' => $retry['max_attempts'] ?? 0]) }}
                                            @if (!empty($retry['error_last']))
                                                — {{ $retry['error_last'] }}
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                                @foreach ($handled_recent_email_events as $event)
                                    <div class="handled-support-timeline-item">
                                        <div class="handled-support-timeline-meta">
                                            <strong>{{ ucfirst($event['event_type'] ?? __('Email')) }} · {{ ucfirst($event['status'] ?? __('Unknown')) }}</strong>
                                            <span>{{ $event['sent_at'] ?? '' }}</span>
                                        </div>
                                        <p>{{ $event['email'] ?? '—' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($handled_support_writeback_visible)
                        <section class="handled-context-subcard">
                            <div class="handled-eyebrow">{{ __('Backend activity') }}</div>
                            <h4>{{ __('Recent writeback activity') }}</h4>
                            @if ($handled_activity_items)
                                <div class="handled-support-timeline">
                                    @foreach ($handled_activity_items as $handled_activity_item)
                                        @php
                                            $handled_activity_title = $handled_activity_item['title']
                                                ?? $handled_activity_item['message']
                                                ?? $handled_activity_item['event']
                                                ?? $handled_activity_item['type']
                                                ?? __('Activity');
                                            $handled_activity_status = $handled_activity_item['status'] ?? null;
                                            $handled_activity_time = $handled_activity_item['occurred_at']
                                                ?? $handled_activity_item['created_at']
                                                ?? $handled_activity_item['timestamp']
                                                ?? null;
                                            $handled_activity_detail = $handled_activity_item['description']
                                                ?? $handled_activity_item['detail']
                                                ?? $handled_activity_item['summary']
                                                ?? null;
                                            $handled_activity_actor = $handled_activity_item['actor']
                                                ?? $handled_activity_item['initiated_by']
                                                ?? null;
                                        @endphp
                                        <div class="handled-support-timeline-item">
                                            <div class="handled-support-timeline-meta">
                                                <strong>
                                                    {{ ucfirst(str_replace(['_', '-'], ' ', $handled_activity_title)) }}
                                                    @if ($handled_activity_status)
                                                        · {{ ucfirst(str_replace(['_', '-'], ' ', $handled_activity_status)) }}
                                                    @endif
                                                </strong>
                                                <span>{{ $handled_activity_time ?: '—' }}</span>
                                            </div>
                                            @if ($handled_activity_detail)
                                                <p>{{ \Illuminate\Support\Str::limit($handled_activity_detail, 240) }}</p>
                                            @elseif ($handled_activity_actor)
                                                <p>{{ $handled_activity_actor }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="handled-context-empty">{{ __('No backend activity is available yet.') }}</p>
                            @endif
                        </section>
                    @endif

                    <section class="handled-context-subcard">
                        <div class="handled-eyebrow">{{ __('Ticket activity') }}</div>
                        <h4>{{ __('Recent synced support activity') }}</h4>
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

    @if ($handled_support_action_enabled)
        <script>
            jQuery(function($) {
                $(document)
                    .off('click.handledSupportAction', '.handled-support-action')
                    .on('click.handledSupportAction', '.handled-support-action', function(event) {
                        event.preventDefault();

                        var button = $(this);
                        var confirmMessage = button.data('confirm');

                        if (button.prop('disabled')) {
                            return;
                        }

                        if (confirmMessage && !window.confirm(confirmMessage)) {
                            return;
                        }

                        var data = {
                            action: button.data('action'),
                            conversation_id: {{ (int) $conversation->id }},
                            business_id: button.data('business-id')
                        };

                        if (button.data('ticket-id')) {
                            data.ticket_id = button.data('ticket-id');
                        }

                        if (button.data('customer-email')) {
                            data.customer_email = button.data('customer-email');
                        }

                        if (typeof button.data('responses-paused') !== 'undefined') {
                            data.responses_paused = button.data('responses-paused');
                        }

                        button.prop('disabled', true);

                        fsAjax(data, laroute.route('conversations.ajax'), function(response) {
                            if (isAjaxSuccess(response)) {
                                showFloatingAlert('success', response.msg, true);
                                window.setTimeout(function() {
                                    window.location.reload();
                                }, 900);
                            } else {
                                button.prop('disabled', false);
                                showAjaxError(response, true);
                            }
                        }, true, function() {
                            button.prop('disabled', false);
                            showFloatingAlert('error', '{{ addslashes(__('An error occurred')) }}', true);
                        });
                    });
            });
        </script>
    @endif
@endif
