@php
    $savedReplies = old('settings.handled_saved_replies', $settings['handled_saved_replies'] ?? []);
    $savedRepliesReturnUrl = request()->query('handled_saved_replies_return');
    $savedRepliesAction = request()->query('handled_saved_reply_action');
    $savedRepliesCategoryPrefill = request()->query('handled_saved_reply_category');
    $savedRepliesEditIndex = request()->query('handled_saved_reply_index');
    if ($savedRepliesReturnUrl) {
        $parsedReturnUrl = parse_url($savedRepliesReturnUrl);
        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $appScheme = parse_url(url('/'), PHP_URL_SCHEME);

        $isRelativeReturnUrl = !empty($parsedReturnUrl['path'])
            && empty($parsedReturnUrl['scheme'])
            && empty($parsedReturnUrl['host'])
            && \Illuminate\Support\Str::startsWith($savedRepliesReturnUrl, '/');
        $isSameOriginReturnUrl = !empty($parsedReturnUrl['host'])
            && !empty($parsedReturnUrl['scheme'])
            && $parsedReturnUrl['host'] === $appHost
            && $parsedReturnUrl['scheme'] === $appScheme;

        if (!$isRelativeReturnUrl && !$isSameOriginReturnUrl) {
            $savedRepliesReturnUrl = null;
        }
    }
    if (!is_array($savedReplies) || !count($savedReplies)) {
        $savedReplies = [
            ['category' => '', 'name' => '', 'body' => ''],
        ];
    }
@endphp

<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <div class="form-group">
        <label class="col-sm-2 control-label">{{ __('Saved Replies') }}</label>

        <div class="col-sm-8">
            @if ($savedRepliesReturnUrl)
                <p class="form-help margin-top-0">
                    <a href="{{ $savedRepliesReturnUrl }}" class="btn btn-default btn-sm">{{ __('Back to conversation') }}</a>
                </p>
            @endif
            <p class="form-help margin-top-0">
                {{ __('Add reusable reply snippets here. Agents can insert them from the reply composer without changing native send or draft behavior. Use slashes in the category field to create nested dropdown paths.') }}
            </p>
        </div>
    </div>

    <div id="handled-saved-replies-list">
        @foreach ($savedReplies as $index => $savedReply)
            <div class="panel panel-default handled-saved-reply-item" data-index="{{ $index }}">
                <div class="panel-body">
                    <div class="form-group{{ $errors->has('settings.handled_saved_replies.'.$index.'.name') ? ' has-error' : '' }}">
                        <label for="handled_saved_reply_category_{{ $index }}" class="col-sm-2 control-label">{{ __('Category') }}</label>

                        <div class="col-sm-3">
                            <input
                                id="handled_saved_reply_category_{{ $index }}"
                                type="text"
                                class="form-control"
                                name="settings[handled_saved_replies][{{ $index }}][category]"
                                value="{{ $savedReply['category'] ?? '' }}"
                                maxlength="160"
                                placeholder="{{ __('Billing / Refunds / Partial refund') }}"
                            >
                            @include('partials/field_error', ['field' => 'settings.handled_saved_replies.'.$index.'.category'])
                        </div>

                        <label for="handled_saved_reply_name_{{ $index }}" class="col-sm-2 control-label">{{ __('Name') }}</label>

                        <div class="col-sm-3">
                            <input
                                id="handled_saved_reply_name_{{ $index }}"
                                type="text"
                                class="form-control"
                                name="settings[handled_saved_replies][{{ $index }}][name]"
                                value="{{ $savedReply['name'] ?? '' }}"
                                maxlength="80"
                            >
                            @include('partials/field_error', ['field' => 'settings.handled_saved_replies.'.$index.'.name'])
                        </div>

                        <div class="col-sm-2 text-right">
                            <button type="button" class="btn btn-link text-danger handled-saved-reply-remove">{{ __('Remove') }}</button>
                        </div>
                    </div>

                    <div class="form-group{{ $errors->has('settings.handled_saved_replies.'.$index.'.body') ? ' has-error' : '' }}">
                        <label for="handled_saved_reply_body_{{ $index }}" class="col-sm-2 control-label">{{ __('Body') }}</label>

                        <div class="col-sm-8">
                            <textarea
                                id="handled_saved_reply_body_{{ $index }}"
                                class="form-control"
                                name="settings[handled_saved_replies][{{ $index }}][body]"
                                rows="6"
                            >{{ $savedReply['body'] ?? '' }}</textarea>
                            <p class="form-help">
                                {{ __('HTML is allowed and standard FreeScout placeholders such as {%customer.firstName%} can be used. Category paths with slashes become nested dropdown levels in the composer.') }}
                            </p>
                            @include('partials/field_error', ['field' => 'settings.handled_saved_replies.'.$index.'.body'])
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="handled_saved_reply_tags_{{ $index }}" class="col-sm-2 control-label">{{ __('Tags') }}</label>

                        <div class="col-sm-8">
                            <select
                                id="handled_saved_reply_tags_{{ $index }}"
                                class="form-control handled-saved-reply-tags"
                                name="settings[handled_saved_replies][{{ $index }}][tag_ids][]"
                                multiple
                                data-placeholder="{{ __('Select tags to apply with this reply') }}"
                            >
                                @foreach (($handled_tag_options ?? []) as $handledTagOption)
                                    <option
                                        value="{{ $handledTagOption['id'] }}"
                                        @if (in_array((int) $handledTagOption['id'], array_map('intval', $savedReply['tag_ids'] ?? []))) selected="selected" @endif
                                    >
                                        {{ $handledTagOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="form-help">
                                {{ __('When this reply is used, its tags are merged into the ticket tag selection before the draft or reply is saved.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="form-group margin-top">
        <div class="col-sm-8 col-sm-offset-2">
            <button type="button" class="btn btn-default" id="handled-saved-reply-add">{{ __('Add Saved Reply') }}</button>
        </div>
    </div>

    <div class="form-group margin-top">
        <div class="col-sm-8 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>

<script type="text/template" id="handled-saved-reply-template">
    <div class="panel panel-default handled-saved-reply-item" data-index="__INDEX__">
        <div class="panel-body">
            <div class="form-group">
                <label for="handled_saved_reply_category___INDEX__" class="col-sm-2 control-label">{{ __('Category') }}</label>

                <div class="col-sm-3">
                    <input
                        id="handled_saved_reply_category___INDEX__"
                        type="text"
                        class="form-control"
                        name="settings[handled_saved_replies][__INDEX__][category]"
                        maxlength="160"
                        placeholder="{{ __('Billing / Refunds / Partial refund') }}"
                    >
                </div>

                <label for="handled_saved_reply_name___INDEX__" class="col-sm-2 control-label">{{ __('Name') }}</label>

                <div class="col-sm-3">
                    <input
                        id="handled_saved_reply_name___INDEX__"
                        type="text"
                        class="form-control"
                        name="settings[handled_saved_replies][__INDEX__][name]"
                        maxlength="80"
                    >
                </div>

                <div class="col-sm-2 text-right">
                    <button type="button" class="btn btn-link text-danger handled-saved-reply-remove">{{ __('Remove') }}</button>
                </div>
            </div>

            <div class="form-group">
                <label for="handled_saved_reply_body___INDEX__" class="col-sm-2 control-label">{{ __('Body') }}</label>

                <div class="col-sm-8">
                    <textarea
                        id="handled_saved_reply_body___INDEX__"
                        class="form-control"
                        name="settings[handled_saved_replies][__INDEX__][body]"
                        rows="6"
                    ></textarea>
                    <p class="form-help">
                        {{ __('HTML is allowed and standard FreeScout placeholders such as {%customer.firstName%} can be used. Category paths with slashes become nested dropdown levels in the composer.') }}
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label for="handled_saved_reply_tags___INDEX__" class="col-sm-2 control-label">{{ __('Tags') }}</label>

                <div class="col-sm-8">
                    <select
                        id="handled_saved_reply_tags___INDEX__"
                        class="form-control handled-saved-reply-tags"
                        name="settings[handled_saved_replies][__INDEX__][tag_ids][]"
                        multiple
                        data-placeholder="{{ __('Select tags to apply with this reply') }}"
                    >
                        @foreach (($handled_tag_options ?? []) as $handledTagOption)
                            <option value="{{ $handledTagOption['id'] }}">{{ $handledTagOption['name'] }}</option>
                        @endforeach
                    </select>
                    <p class="form-help">
                        {{ __('When this reply is used, its tags are merged into the ticket tag selection before the draft or reply is saved.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</script>

@section('javascript')
    @parent
    (function() {
        $(document).ready(function() {
            var list = $('#handled-saved-replies-list');
            var template = $('#handled-saved-reply-template').html();
            var nextIndex = list.find('.handled-saved-reply-item').length;
            var requestedAction = @json($savedRepliesAction);
            var requestedCategory = @json($savedRepliesCategoryPrefill);
            var requestedEditIndex = @json($savedRepliesEditIndex);

            function focusReplyItem(item, focusSelector) {
                if (!item || !item.length) {
                    return;
                }

                $('.handled-saved-reply-item').removeClass('panel-info');
                item.addClass('panel-info');
                $('html, body').animate({
                    scrollTop: Math.max(item.offset().top - 120, 0)
                }, 0);
                item.find(focusSelector || 'input:first').focus();
            }

            function addReplyItem() {
                list.append(template.replace(/__INDEX__/g, nextIndex));
                initReplyTagSelect(list.find('.handled-saved-reply-item:last').find('.handled-saved-reply-tags'));
                nextIndex++;

                return list.find('.handled-saved-reply-item:last');
            }

            function initReplyTagSelect(input) {
                if (!input || !input.length || input.hasClass('select2-hidden-accessible')) {
                    return;
                }

                input.select2({
                    width: '100%',
                    placeholder: input.attr('data-placeholder') || '',
                    closeOnSelect: false
                });
            }

            $('#handled-saved-reply-add').on('click', function() {
                addReplyItem();
            });

            list.on('click', '.handled-saved-reply-remove', function() {
                var items = list.find('.handled-saved-reply-item');
                if (items.length <= 1) {
                    items.find('input, textarea').val('');
                    return;
                }

                $(this).closest('.handled-saved-reply-item').remove();
            });

            if (requestedAction === 'new') {
                var newItem = addReplyItem();
                if (requestedCategory) {
                    newItem.find('input[name$="[category]"]').val(requestedCategory);
                }
                focusReplyItem(newItem, 'input[name$="[name]"]');
            } else if (requestedEditIndex !== null && requestedEditIndex !== '') {
                requestedEditIndex = parseInt(requestedEditIndex, 10);
                if (!isNaN(requestedEditIndex) && requestedEditIndex >= 0) {
                    var existingItem = list.find('.handled-saved-reply-item[data-index="' + requestedEditIndex + '"]');
                    if (existingItem.length) {
                        focusReplyItem(existingItem, 'input[name$="[name]"]');
                    }
                }
            }

            initReplyTagSelect($('.handled-saved-reply-tags'));
        });
    })();
@endsection
