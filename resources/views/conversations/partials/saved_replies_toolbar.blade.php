@php
    $handled_saved_replies = is_array($handled_saved_replies ?? null) ? array_values($handled_saved_replies) : [];
    $handled_saved_replies_mailbox_id = $handled_saved_replies_mailbox_id ?? null;
    $handled_saved_replies_conversation_id = $handled_saved_replies_conversation_id ?? null;
@endphp

<style {!! \Helper::cspNonceAttr() !!}>
    .handled-saved-replies-toolbar {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-right: 12px;
        vertical-align: middle;
    }

    .handled-saved-replies-toolbar .form-control {
        min-width: 160px;
    }

    .handled-saved-replies-panel {
        display: none;
        width: 100%;
        margin-top: 12px;
        padding: 16px;
        border: 1px solid #d8dfe6;
        background: #f8fafc;
    }

    .handled-saved-replies-panel.is-open {
        display: block;
    }

    .handled-saved-replies-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .handled-saved-replies-panel-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
    }

    .handled-saved-replies-help {
        margin-bottom: 14px;
        color: #607d9f;
        font-size: 12px;
        line-height: 1.5;
    }

    .handled-saved-replies-placeholder-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .handled-saved-replies-placeholder {
        display: inline-block;
        padding: 2px 8px;
        border: 1px solid #d8dfe6;
        background: #fff;
        color: #425466;
        font-family: monospace;
        font-size: 11px;
    }

    .handled-saved-reply-editor-item {
        margin-bottom: 12px;
        padding: 12px;
        border: 1px solid #d8dfe6;
        background: #fff;
    }

    .handled-saved-reply-editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 180px) minmax(0, 1fr) auto;
        gap: 10px;
        align-items: start;
        margin-bottom: 10px;
    }

    .handled-saved-reply-editor-item textarea {
        min-height: 110px;
        resize: vertical;
    }

    .handled-saved-replies-panel-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    @media (max-width: 900px) {
        .handled-saved-reply-editor-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<span class="handled-saved-replies-toolbar">
    <span class="editor-btm-text">{{ __('Saved Reply') }}:</span>
    <select class="form-control parsley-exclude handled-saved-replies-category">
        <option value="">{{ __('All categories') }}</option>
    </select>
    <select class="form-control parsley-exclude handled-saved-replies-select">
        <option value="">{{ __('Select saved reply') }}</option>
    </select>
    <button type="button" class="btn btn-default handled-saved-replies-insert" disabled>{{ __('Insert') }}</button>
    <button type="button" class="btn btn-default handled-saved-replies-manage">{{ __('Manage') }}</button>
    <button type="button" class="btn btn-default handled-saved-replies-new">{{ __('New') }}</button>
</span>

<div class="handled-saved-replies-panel">
    <div class="handled-saved-replies-panel-header">
        <h5>{{ __('Saved reply library') }}</h5>
        <button type="button" class="btn btn-link handled-saved-replies-close">{{ __('Close') }}</button>
    </div>
    <div class="handled-saved-replies-help">
        {{ __('Create and edit replies here without leaving the ticket. Categories drive the first dropdown, and the selected reply inserts into the composer.') }}
        <div class="handled-saved-replies-placeholder-list">
            <span class="handled-saved-replies-placeholder">{%customer.firstName%}</span>
            <span class="handled-saved-replies-placeholder">{{ '{{handled.business_name}}' }}</span>
            <span class="handled-saved-replies-placeholder">{{ '{{handled.owner_name}}' }}</span>
            <span class="handled-saved-replies-placeholder">{{ '{{handled.customer_name}}' }}</span>
            <span class="handled-saved-replies-placeholder">{{ '{{handled.customer_email}}' }}</span>
            <span class="handled-saved-replies-placeholder">{{ '{{handled.ticket_id}}' }}</span>
        </div>
    </div>
    <div class="handled-saved-replies-editor-list"></div>
    <div class="handled-saved-replies-panel-actions">
        <button type="button" class="btn btn-default handled-saved-replies-add">{{ __('Add saved reply') }}</button>
        <button type="button" class="btn btn-primary handled-saved-replies-save">{{ __('Save library') }}</button>
    </div>
</div>

<script>
    window.handledSavedReplies = @json($handled_saved_replies);

    (function() {
        if (window.handledSavedRepliesToolbarBound) {
            return;
        }
        window.handledSavedRepliesToolbarBound = true;

        function getToolbar() {
            return $('.handled-saved-replies-toolbar:first');
        }

        function getPanel() {
            return $('.handled-saved-replies-panel:first');
        }

        function getEditorList() {
            return getPanel().find('.handled-saved-replies-editor-list');
        }

        function buildEmptyReply(category) {
            return {
                category: category || '',
                name: '',
                body: '',
                rendered_body: ''
            };
        }

        function escapeHtml(value) {
            return $('<div/>').text(value || '').html();
        }

        function currentCategoryValue() {
            return getToolbar().find('.handled-saved-replies-category').val() || '';
        }

        function filteredReplies() {
            var category = currentCategoryValue();

            return $.map(window.handledSavedReplies || [], function(reply, index) {
                if (category && (reply.category || '') !== category) {
                    return null;
                }

                return $.extend({ _index: index }, reply);
            });
        }

        function renderCategorySelect() {
            var select = getToolbar().find('.handled-saved-replies-category');
            var currentValue = select.val() || '';
            var categories = [];

            $.each(window.handledSavedReplies || [], function(_, reply) {
                var category = (reply.category || '').trim();
                if (category && $.inArray(category, categories) === -1) {
                    categories.push(category);
                }
            });

            categories.sort(function(a, b) {
                return a.localeCompare(b);
            });

            select.empty().append($('<option />').val('').text('{{ addslashes(__('All categories')) }}'));

            $.each(categories, function(_, category) {
                select.append($('<option />').val(category).text(category));
            });

            if (currentValue && $.inArray(currentValue, categories) === -1) {
                currentValue = '';
            }

            select.val(currentValue);
        }

        function renderReplySelect() {
            var select = getToolbar().find('.handled-saved-replies-select');
            var insertButton = getToolbar().find('.handled-saved-replies-insert');
            var currentValue = select.val() || '';
            var replies = filteredReplies();
            var category = currentCategoryValue();

            select.empty();

            if (!replies.length) {
                select.append($('<option />').val('').text('{{ addslashes(__('No saved replies in this category')) }}'));
                select.prop('disabled', true);
                insertButton.prop('disabled', true);
                return;
            }

            select.append($('<option />').val('').text('{{ addslashes(__('Select saved reply')) }}'));

            $.each(replies, function(_, reply) {
                var label = reply.name;

                if (!category && reply.category) {
                    label = reply.category + ' / ' + reply.name;
                }

                select.append($('<option />').val(reply._index).text(label));
            });

            if (currentValue && select.find('option[value="' + currentValue + '"]').length) {
                select.val(currentValue);
            } else {
                select.val('');
            }

            select.prop('disabled', false);
            insertButton.prop('disabled', !select.val());
        }

        function renderSelectors() {
            renderCategorySelect();
            renderReplySelect();
        }

        function renderEditor() {
            var list = getEditorList();

            list.empty();

            if (!window.handledSavedReplies.length) {
                list.append(
                    '<div class="handled-saved-replies-empty">' +
                        '<div class="handled-saved-replies-help">{{ addslashes(__('No saved replies yet. Add one below to start building your library.')) }}</div>' +
                    '</div>'
                );
                return;
            }

            $.each(window.handledSavedReplies, function(index, reply) {
                list.append(
                    '<div class="handled-saved-reply-editor-item" data-index="' + index + '">' +
                        '<div class="handled-saved-reply-editor-grid">' +
                            '<input type="text" class="form-control handled-saved-reply-category-input" placeholder="{{ addslashes(__('Category')) }}" maxlength="80" value="' + escapeHtml(reply.category || '') + '">' +
                            '<input type="text" class="form-control handled-saved-reply-name-input" placeholder="{{ addslashes(__('Saved reply name')) }}" maxlength="80" value="' + escapeHtml(reply.name || '') + '">' +
                            '<button type="button" class="btn btn-link text-danger handled-saved-reply-delete">{{ addslashes(__('Delete')) }}</button>' +
                        '</div>' +
                        '<textarea class="form-control handled-saved-reply-body-input" rows="6" placeholder="{{ addslashes(__('Reply body')) }}">' + escapeHtml(reply.body || '') + '</textarea>' +
                    '</div>'
                );
            });
        }

        function openPanel() {
            getPanel().addClass('is-open');
            renderEditor();
        }

        function closePanel() {
            getPanel().removeClass('is-open');
        }

        function collectEditorReplies() {
            var replies = [];

            getEditorList().find('.handled-saved-reply-editor-item[data-index]').each(function() {
                var item = $(this);

                replies.push({
                    category: $.trim(item.find('.handled-saved-reply-category-input').val()),
                    name: $.trim(item.find('.handled-saved-reply-name-input').val()),
                    body: $.trim(item.find('.handled-saved-reply-body-input').val())
                });
            });

            return replies;
        }

        function replaceSavedReplies(replies) {
            window.handledSavedReplies = $.isArray(replies) ? replies : [];
            renderSelectors();
            if (getPanel().hasClass('is-open')) {
                renderEditor();
            }
        }

        function addReply(category) {
            window.handledSavedReplies.push(buildEmptyReply(category));
            openPanel();
            renderEditor();

            var lastItem = getEditorList().find('.handled-saved-reply-editor-item:last');
            lastItem.find('.handled-saved-reply-name-input').focus();
        }

        function insertSelectedReply() {
            var index = getToolbar().find('.handled-saved-replies-select').val();
            var savedReply;
            var body;

            if (index === '') {
                return;
            }

            savedReply = window.handledSavedReplies[index];
            if (!savedReply) {
                return;
            }

            body = savedReply.rendered_body || savedReply.body || '';
            if (!body) {
                return;
            }

            $('#body').summernote('focus');
            $('#body').summernote('pasteHTML', body);
            onReplyChange();
        }

        function saveReplies() {
            var toolbar = getToolbar();
            var saveButton = getPanel().find('.handled-saved-replies-save');
            var data = {
                action: 'handled_saved_replies_save',
                mailbox_id: @json($handled_saved_replies_mailbox_id),
                conversation_id: @json($handled_saved_replies_conversation_id),
                saved_replies_json: JSON.stringify(collectEditorReplies())
            };

            saveButton.prop('disabled', true);

            fsAjax(data, laroute.route('conversations.ajax'), function(response) {
                saveButton.prop('disabled', false);

                if (isAjaxSuccess(response)) {
                    replaceSavedReplies(response.saved_replies || []);
                    showFloatingAlert('success', response.msg, true);
                } else {
                    showAjaxError(response, true);
                }
            }, true, function() {
                saveButton.prop('disabled', false);
                showFloatingAlert('error', '{{ addslashes(__('An error occurred')) }}', true);
            });
        }

        $(document)
            .on('change', '.handled-saved-replies-category', function() {
                renderReplySelect();
            })
            .on('change', '.handled-saved-replies-select', function() {
                getToolbar().find('.handled-saved-replies-insert').prop('disabled', !$(this).val());
            })
            .on('click', '.handled-saved-replies-insert', function(e) {
                insertSelectedReply();
                e.preventDefault();
            })
            .on('click', '.handled-saved-replies-manage', function(e) {
                openPanel();
                e.preventDefault();
            })
            .on('click', '.handled-saved-replies-new', function(e) {
                addReply(currentCategoryValue());
                e.preventDefault();
            })
            .on('click', '.handled-saved-replies-close', function(e) {
                closePanel();
                e.preventDefault();
            })
            .on('click', '.handled-saved-replies-add', function(e) {
                addReply(currentCategoryValue());
                e.preventDefault();
            })
            .on('click', '.handled-saved-replies-save', function(e) {
                saveReplies();
                e.preventDefault();
            })
            .on('click', '.handled-saved-reply-delete', function(e) {
                var item = $(this).closest('.handled-saved-reply-editor-item');
                var index = parseInt(item.data('index'), 10);

                if (!isNaN(index)) {
                    window.handledSavedReplies.splice(index, 1);
                    renderEditor();
                    renderSelectors();
                }

                e.preventDefault();
            });

        renderSelectors();
    })();
</script>
