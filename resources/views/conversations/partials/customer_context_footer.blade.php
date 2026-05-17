@php
    $handled_support_context = $handled_support_context ?? (isset($conversation) ? app(\App\Services\HandledSupportContextService::class)->lookupForConversation($conversation) : null);
    $handled_business = $handled_business ?? (is_array($handled_support_context) ? ($handled_support_context['business'] ?? null) : null);
    $handled_setup = $handled_setup ?? (is_array($handled_support_context) ? ($handled_support_context['setup'] ?? null) : null);
    $handled_support_summary = $handled_support_summary ?? (is_array($handled_support_context) ? ($handled_support_context['support_summary'] ?? null) : null);
    $handled_ticket = $handled_ticket ?? (is_array($handled_support_context) ? ($handled_support_context['ticket'] ?? null) : null);
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
@endphp

@if ($handled_setup || $handled_support_summary || $handled_ticket || $customer->company || $customer->job_title || $customer_location || $websites || $social_profiles || $customer->notes)
    <div class="handled-context-wide-panel handled-context-panel">
        <div class="handled-context-card-header">
            <div>
                <div class="handled-eyebrow">{{ __('Handled') }}</div>
                <h3>{{ __('Customer context') }}</h3>
            </div>
        </div>

        <div class="handled-context-wide-grid">
            <div>
                @if ($customer->company || $customer->job_title || $customer_location || $customer->address || $customer->zip || $ordered_emails || $phones || $websites || $social_profiles || $customer->notes)
                    <div>
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
                    </div>
                @endif

                @if ($handled_business && (!empty($handled_business['plan_tier']) || !empty($handled_business['brand_id']) || !empty($handled_business['instagram_handle']) || !empty($handled_business['booking_url'])))
                    <div class="handled-context-section">
                        <div class="handled-eyebrow">{{ __('Handled account') }}</div>
                        <h4>{{ __('Extended account details') }}</h4>
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
                        </dl>
                    </div>
                @endif

                <div class="handled-context-section">
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
