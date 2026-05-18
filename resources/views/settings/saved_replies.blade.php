@php
    $savedReplies = old('settings.handled_saved_replies', $settings['handled_saved_replies'] ?? []);
    if (!is_array($savedReplies) || !count($savedReplies)) {
        $savedReplies = [
            ['name' => '', 'body' => ''],
        ];
    }
@endphp

<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <div class="form-group">
        <label class="col-sm-2 control-label">{{ __('Saved Replies') }}</label>

        <div class="col-sm-8">
            <p class="form-help margin-top-0">
                {{ __('Add reusable reply snippets here. Agents can insert them from the reply composer without changing native send or draft behavior.') }}
            </p>
        </div>
    </div>

    <div id="handled-saved-replies-list">
        @foreach ($savedReplies as $index => $savedReply)
            <div class="panel panel-default handled-saved-reply-item" data-index="{{ $index }}">
                <div class="panel-body">
                    <div class="form-group{{ $errors->has('settings.handled_saved_replies.'.$index.'.name') ? ' has-error' : '' }}">
                        <label for="handled_saved_reply_name_{{ $index }}" class="col-sm-2 control-label">{{ __('Name') }}</label>

                        <div class="col-sm-6">
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
                                {{ __('HTML is allowed and standard FreeScout placeholders such as {%customer.firstName%} can be used.') }}
                            </p>
                            @include('partials/field_error', ['field' => 'settings.handled_saved_replies.'.$index.'.body'])
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
                <label for="handled_saved_reply_name___INDEX__" class="col-sm-2 control-label">{{ __('Name') }}</label>

                <div class="col-sm-6">
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
                        {{ __('HTML is allowed and standard FreeScout placeholders such as {%customer.firstName%} can be used.') }}
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

            $('#handled-saved-reply-add').on('click', function() {
                list.append(template.replace(/__INDEX__/g, nextIndex));
                nextIndex++;
            });

            list.on('click', '.handled-saved-reply-remove', function() {
                var items = list.find('.handled-saved-reply-item');
                if (items.length <= 1) {
                    items.find('input, textarea').val('');
                    return;
                }

                $(this).closest('.handled-saved-reply-item').remove();
            });
        });
    })();
@endsection
