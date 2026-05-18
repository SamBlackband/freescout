<span class="editor-btm-text">{{ __('Saved Reply') }}:</span>
<select class="form-control parsley-exclude handled-saved-replies-select">
    <option value="">{{ __('Insert saved reply…') }}</option>
    @foreach ($handled_saved_replies as $index => $savedReply)
        <option value="{{ $index }}">{{ $savedReply['name'] }}</option>
    @endforeach
</select>
<button type="button" class="btn btn-default handled-saved-replies-insert">{{ __('Insert') }}</button>

<script>
    window.handledSavedReplies = @json($handled_saved_replies);

    (function() {
        if (window.handledSavedRepliesToolbarBound) {
            return;
        }
        window.handledSavedRepliesToolbarBound = true;

        function insertHandledSavedReply() {
            var index = $('.handled-saved-replies-select:first').val();
            if (index === '') {
                return;
            }

            var savedReply = window.handledSavedReplies[index];
            if (!savedReply || !savedReply.body) {
                return;
            }

            $('#body').summernote('focus');
            $('#body').summernote('pasteHTML', savedReply.body);
            onReplyChange();
        }

        $(document).on('click', '.handled-saved-replies-insert', function(e) {
            insertHandledSavedReply();
            e.preventDefault();
        });

    })();
</script>
