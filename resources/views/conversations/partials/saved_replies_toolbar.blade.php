@php
    $handled_saved_replies = is_array($handled_saved_replies ?? null) ? array_values($handled_saved_replies) : [];
    $handled_saved_replies_mailbox_id = $handled_saved_replies_mailbox_id ?? null;
    $handled_saved_replies_conversation_id = $handled_saved_replies_conversation_id ?? null;
    $handled_saved_reply_trigger_text = __('Saved Replies');
    $handled_saved_reply_menu_title_text = __('Saved replies');
    $handled_saved_reply_manage_text = __('Manage library');
    $handled_saved_reply_new_text = __('New reply');
    $handled_saved_reply_back_text = __('Back');
    $handled_saved_reply_empty_menu_text = __('No saved replies yet.');
    $handled_saved_reply_uncategorized_text = __('Saved replies');
    $handled_saved_reply_library_title_text = __('Saved reply library');
    $handled_saved_reply_library_empty_text = __('No saved replies yet. Create one to start building your library.');
    $handled_saved_reply_modal_intro_text = __('Edit replies here without leaving the ticket. Use slashes in the category field to create nested menu paths.');
    $handled_saved_reply_category_text = __('Category path');
    $handled_saved_reply_category_placeholder_text = __('Billing / Refunds / Partial refund');
    $handled_saved_reply_name_text = __('Saved reply name');
    $handled_saved_reply_body_text = __('Reply body');
    $handled_saved_reply_delete_text = __('Delete');
    $handled_saved_reply_cancel_text = __('Close');
    $handled_saved_reply_save_text = __('Save library');
    $handled_saved_reply_edit_text = __('Edit');
    $handled_saved_reply_new_modal_title_text = __('New saved reply');
    $handled_saved_reply_edit_modal_title_text = __('Edit saved reply');
    $handled_saved_reply_required_text = __('Saved replies need a name and body before they can be saved.');
    $handled_saved_reply_delete_confirm_text = __('Delete this saved reply?');
    $handled_saved_reply_error_text = __('An error occurred');
@endphp

<style {!! \Helper::cspNonceAttr() !!}>
    .handled-saved-replies-dropdown {
        display: inline-block;
        margin-right: 12px;
        vertical-align: middle;
    }

    .handled-saved-replies-trigger {
        min-width: 150px;
    }

    .handled-saved-replies-menu {
        width: 360px;
        padding: 0;
        overflow: hidden;
    }

    .handled-saved-replies-menu-header {
        padding: 12px 14px 8px;
        border-bottom: 1px solid #e1e8f0;
        background: #f8fafc;
    }

    .handled-saved-replies-menu-title {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }

    .handled-saved-replies-menu-breadcrumb {
        display: block;
        margin-top: 2px;
        color: #6b7280;
        font-size: 11px;
    }

    .handled-saved-replies-menu-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .handled-saved-replies-menu-section {
        padding: 8px 14px 4px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .handled-saved-replies-menu-row {
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid #eef2f7;
    }

    .handled-saved-replies-menu-row:last-child {
        border-bottom: 0;
    }

    .handled-saved-replies-menu-link,
    .handled-saved-replies-menu-edit,
    .handled-saved-replies-menu-back {
        background: transparent;
        border: 0;
    }

    .handled-saved-replies-menu-link,
    .handled-saved-replies-menu-back {
        display: flex;
        flex: 1 1 auto;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 14px;
        color: #111827;
        text-align: left;
        width: 100%;
    }

    .handled-saved-replies-menu-link:hover,
    .handled-saved-replies-menu-back:hover,
    .handled-saved-replies-menu-edit:hover,
    .handled-saved-replies-library-item:hover {
        background: #f8fafc;
        text-decoration: none;
    }

    .handled-saved-replies-menu-edit {
        flex: 0 0 auto;
        width: 42px;
        color: #6b7280;
    }

    .handled-saved-replies-menu-empty {
        padding: 14px;
        color: #6b7280;
        font-size: 12px;
    }

    .handled-saved-replies-menu-footer {
        display: flex;
        gap: 8px;
        padding: 10px 12px;
        border-top: 1px solid #e1e8f0;
        background: #f8fafc;
    }

    .handled-saved-replies-menu-footer .btn {
        flex: 1 1 0;
    }

    .handled-saved-replies-modal-intro {
        margin-bottom: 12px;
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

    .handled-saved-replies-library-wrap {
        border-right: 1px solid #eef2f7;
        max-height: 420px;
        overflow-y: auto;
    }

    .handled-saved-replies-library-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .handled-saved-replies-library-item {
        display: block;
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        margin-bottom: 8px;
        background: #fff;
        color: #111827;
        text-align: left;
    }

    .handled-saved-replies-library-item.is-active {
        border-color: #93c5fd;
        background: #eff6ff;
    }

    .handled-saved-replies-library-item strong,
    .handled-saved-replies-library-item span {
        display: block;
    }

    .handled-saved-replies-library-item span {
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }

    .handled-saved-replies-library-empty {
        color: #6b7280;
        font-size: 12px;
        padding: 10px 0;
    }

    .handled-saved-replies-editor textarea {
        min-height: 220px;
        resize: vertical;
    }

    .handled-saved-replies-modal-delete {
        margin-right: auto;
    }

    @media (max-width: 991px) {
        .handled-saved-replies-menu {
            width: 320px;
        }

        .handled-saved-replies-library-wrap {
            border-right: 0;
            border-bottom: 1px solid #eef2f7;
            margin-bottom: 16px;
            max-height: none;
            overflow: visible;
            padding-bottom: 12px;
        }
    }
</style>

<div class="btn-group handled-saved-replies-dropdown">
    <button type="button" class="btn btn-default dropdown-toggle handled-saved-replies-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ $handled_saved_reply_trigger_text }} <span class="caret"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-right handled-saved-replies-menu">
        <div class="handled-saved-replies-menu-header">
            <span class="handled-saved-replies-menu-title">{{ $handled_saved_reply_menu_title_text }}</span>
            <span class="handled-saved-replies-menu-breadcrumb"></span>
        </div>
        <div class="handled-saved-replies-menu-list"></div>
        <div class="handled-saved-replies-menu-footer">
            <button type="button" class="btn btn-default btn-sm handled-saved-replies-manage">{{ $handled_saved_reply_manage_text }}</button>
            <button type="button" class="btn btn-primary btn-sm handled-saved-replies-new">{{ $handled_saved_reply_new_text }}</button>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="handled-saved-replies-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ $handled_saved_reply_library_title_text }}</h4>
            </div>
            <div class="modal-body">
                <div class="handled-saved-replies-modal-intro">
                    {{ $handled_saved_reply_modal_intro_text }}
                    <div class="handled-saved-replies-placeholder-list">
                        <span class="handled-saved-replies-placeholder">{%customer.firstName%}</span>
                        <span class="handled-saved-replies-placeholder">@{{handled.business_name}}</span>
                        <span class="handled-saved-replies-placeholder">@{{handled.owner_name}}</span>
                        <span class="handled-saved-replies-placeholder">@{{handled.customer_name}}</span>
                        <span class="handled-saved-replies-placeholder">@{{handled.customer_email}}</span>
                        <span class="handled-saved-replies-placeholder">@{{handled.ticket_id}}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 handled-saved-replies-library-wrap">
                        <div class="handled-saved-replies-library-header">
                            <strong>{{ $handled_saved_reply_library_title_text }}</strong>
                            <button type="button" class="btn btn-link handled-saved-replies-library-new">{{ $handled_saved_reply_new_text }}</button>
                        </div>
                        <div class="handled-saved-replies-library-list"></div>
                    </div>
                    <div class="col-sm-8 handled-saved-replies-editor">
                        <div class="form-group">
                            <label for="handled_saved_replies_modal_category">{{ $handled_saved_reply_category_text }}</label>
                            <input
                                id="handled_saved_replies_modal_category"
                                type="text"
                                class="form-control handled-saved-replies-modal-category"
                                maxlength="160"
                                placeholder="{{ $handled_saved_reply_category_placeholder_text }}"
                            >
                        </div>
                        <div class="form-group">
                            <label for="handled_saved_replies_modal_name">{{ $handled_saved_reply_name_text }}</label>
                            <input
                                id="handled_saved_replies_modal_name"
                                type="text"
                                class="form-control handled-saved-replies-modal-name"
                                maxlength="80"
                            >
                        </div>
                        <div class="form-group">
                            <label for="handled_saved_replies_modal_body">{{ $handled_saved_reply_body_text }}</label>
                            <textarea
                                id="handled_saved_replies_modal_body"
                                class="form-control handled-saved-replies-modal-body"
                                rows="10"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link text-danger handled-saved-replies-modal-delete">{{ $handled_saved_reply_delete_text }}</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">{{ $handled_saved_reply_cancel_text }}</button>
                <button type="button" class="btn btn-primary handled-saved-replies-modal-save">{{ $handled_saved_reply_save_text }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.handledSavedReplies = @json($handled_saved_replies);

    (function() {
        if (window.handledSavedRepliesToolbarBound) {
            return;
        }
        window.handledSavedRepliesToolbarBound = true;

        var handledSavedRepliesMenuTitleText = @json($handled_saved_reply_menu_title_text);
        var handledSavedRepliesManageText = @json($handled_saved_reply_manage_text);
        var handledSavedRepliesNewText = @json($handled_saved_reply_new_text);
        var handledSavedRepliesBackText = @json($handled_saved_reply_back_text);
        var handledSavedRepliesEmptyMenuText = @json($handled_saved_reply_empty_menu_text);
        var handledSavedRepliesUncategorizedText = @json($handled_saved_reply_uncategorized_text);
        var handledSavedRepliesLibraryTitleText = @json($handled_saved_reply_library_title_text);
        var handledSavedRepliesLibraryEmptyText = @json($handled_saved_reply_library_empty_text);
        var handledSavedRepliesNewModalTitleText = @json($handled_saved_reply_new_modal_title_text);
        var handledSavedRepliesEditModalTitleText = @json($handled_saved_reply_edit_modal_title_text);
        var handledSavedRepliesEditText = @json($handled_saved_reply_edit_text);
        var handledSavedRepliesDeleteText = @json($handled_saved_reply_delete_text);
        var handledSavedRepliesRequiredText = @json($handled_saved_reply_required_text);
        var handledSavedRepliesDeleteConfirmText = @json($handled_saved_reply_delete_confirm_text);
        var handledSavedRepliesErrorText = @json($handled_saved_reply_error_text);
        var handledSavedRepliesMailboxId = @json($handled_saved_replies_mailbox_id);
        var handledSavedRepliesConversationId = @json($handled_saved_replies_conversation_id);
        var currentPath = [];
        var currentEditIndex = null;

        function getDropdown() {
            return $('.handled-saved-replies-dropdown:first');
        }

        function getMenu() {
            return getDropdown().find('.handled-saved-replies-menu:first');
        }

        function getMenuList() {
            return getMenu().find('.handled-saved-replies-menu-list');
        }

        function getBreadcrumb() {
            return getMenu().find('.handled-saved-replies-menu-breadcrumb');
        }

        function getTrigger() {
            return getDropdown().find('.handled-saved-replies-trigger');
        }

        function getModal() {
            return $('#handled-saved-replies-modal');
        }

        function getModalTitle() {
            return getModal().find('.modal-title');
        }

        function getLibraryList() {
            return getModal().find('.handled-saved-replies-library-list');
        }

        function getModalCategory() {
            return getModal().find('.handled-saved-replies-modal-category');
        }

        function getModalName() {
            return getModal().find('.handled-saved-replies-modal-name');
        }

        function getModalBody() {
            return getModal().find('.handled-saved-replies-modal-body');
        }

        function getDeleteButton() {
            return getModal().find('.handled-saved-replies-modal-delete');
        }

        function closeDropdown() {
            getDropdown().removeClass('open');
            getTrigger().attr('aria-expanded', 'false');
        }

        function escapeHtml(value) {
            return $('<div/>').text(value || '').html();
        }

        function normalizeCategoryPath(value) {
            return $.map(String(value || '').split('/'), function(segment) {
                segment = $.trim(segment);
                return segment ? segment : null;
            }).join(' / ');
        }

        function getCategorySegments(category) {
            var normalized = normalizeCategoryPath(category);

            if (!normalized) {
                return [];
            }

            return normalized.split(' / ');
        }

        function formatReplyLabel(reply) {
            var category = normalizeCategoryPath(reply.category);

            if (!category) {
                return reply.name || '';
            }

            return category + ' / ' + (reply.name || '');
        }

        function buildReplyTree() {
            var root = {
                categories: {},
                replies: []
            };

            $.each(window.handledSavedReplies || [], function(index, reply) {
                var node = root;
                var segments = getCategorySegments(reply.category);

                $.each(segments, function(_, segment) {
                    if (!node.categories[segment]) {
                        node.categories[segment] = {
                            name: segment,
                            categories: {},
                            replies: []
                        };
                    }

                    node = node.categories[segment];
                });

                node.replies.push($.extend({
                    _index: index,
                    _segments: segments
                }, reply));
            });

            return root;
        }

        function getTreeNode(path) {
            var node = buildReplyTree();

            $.each(path, function(_, segment) {
                if (!node.categories[segment]) {
                    node = null;
                    return false;
                }

                node = node.categories[segment];
            });

            return node;
        }

        function appendSectionTitle(container, text) {
            container.append(
                $('<div />')
                    .addClass('handled-saved-replies-menu-section')
                    .text(text)
            );
        }

        function appendBackRow(container) {
            var row = $('<div />').addClass('handled-saved-replies-menu-row');
            var button = $('<button />', {
                type: 'button'
            })
                .addClass('handled-saved-replies-menu-back')
                .attr('data-menu-action', 'back')
                .append(
                    $('<span />')
                        .append($('<i />').addClass('glyphicon glyphicon-chevron-left'))
                        .append(' ' + handledSavedRepliesBackText)
                );

            row.append(button);
            container.append(row);
        }

        function appendCategoryRow(container, name) {
            var row = $('<div />').addClass('handled-saved-replies-menu-row');
            var button = $('<button />', {
                type: 'button'
            })
                .addClass('handled-saved-replies-menu-link')
                .attr('data-menu-action', 'open-category')
                .data('category-segment', name)
                .append($('<span />').text(name))
                .append($('<i />').addClass('glyphicon glyphicon-chevron-right'));

            row.append(button);
            container.append(row);
        }

        function appendReplyRow(container, reply) {
            var row = $('<div />').addClass('handled-saved-replies-menu-row');
            var insertButton = $('<button />', {
                type: 'button'
            })
                .addClass('handled-saved-replies-menu-link')
                .attr('data-menu-action', 'insert-reply')
                .attr('data-reply-index', reply._index)
                .append($('<span />').text(reply.name || ''))
                .append($('<span />').addClass('text-muted').html('&nbsp;'));
            var editButton = $('<button />', {
                type: 'button',
                title: handledSavedRepliesEditText
            })
                .addClass('handled-saved-replies-menu-edit')
                .attr('data-menu-action', 'edit-reply')
                .attr('data-reply-index', reply._index)
                .append($('<i />').addClass('glyphicon glyphicon-eye-open'));

            row.append(insertButton).append(editButton);
            container.append(row);
        }

        function renderMenu() {
            var list = getMenuList();
            var node = getTreeNode(currentPath);
            var categoryNames = [];
            var replies = [];

            if (!node) {
                currentPath = [];
                node = getTreeNode(currentPath);
            }

            getBreadcrumb().text(currentPath.length ? currentPath.join(' / ') : '');
            list.empty();

            if (currentPath.length) {
                appendBackRow(list);
            }

            categoryNames = Object.keys(node.categories).sort(function(a, b) {
                return a.localeCompare(b);
            });
            replies = (node.replies || []).slice().sort(function(a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });

            if (!categoryNames.length && !replies.length) {
                list.append(
                    $('<div />')
                        .addClass('handled-saved-replies-menu-empty')
                        .text(handledSavedRepliesEmptyMenuText)
                );
                return;
            }

            if (categoryNames.length) {
                appendSectionTitle(list, handledSavedRepliesMenuTitleText);
                $.each(categoryNames, function(_, categoryName) {
                    appendCategoryRow(list, categoryName);
                });
            }

            if (replies.length) {
                appendSectionTitle(list, currentPath.length ? handledSavedRepliesUncategorizedText : handledSavedRepliesUncategorizedText);
                $.each(replies, function(_, reply) {
                    appendReplyRow(list, reply);
                });
            }
        }

        function renderLibraryList() {
            var list = getLibraryList();
            var replies = (window.handledSavedReplies || []).slice().map(function(reply, index) {
                return $.extend({ _index: index }, reply);
            }).sort(function(a, b) {
                return formatReplyLabel(a).localeCompare(formatReplyLabel(b));
            });

            list.empty();

            if (!replies.length) {
                list.append(
                    $('<div />')
                        .addClass('handled-saved-replies-library-empty')
                        .text(handledSavedRepliesLibraryEmptyText)
                );
                return;
            }

            $.each(replies, function(_, reply) {
                var button = $('<button />', {
                    type: 'button'
                })
                    .addClass('handled-saved-replies-library-item')
                    .toggleClass('is-active', currentEditIndex === reply._index)
                    .attr('data-library-index', reply._index)
                    .append($('<strong />').text(reply.name || ''))
                    .append($('<span />').text(normalizeCategoryPath(reply.category) || handledSavedRepliesUncategorizedText));

                list.append(button);
            });
        }

        function fillModalForm(reply) {
            getModalCategory().val(reply ? normalizeCategoryPath(reply.category) : '');
            getModalName().val(reply ? (reply.name || '') : '');
            getModalBody().val(reply ? (reply.body || '') : '');
            getDeleteButton().toggle(!!reply);
            getModalTitle().text(reply ? handledSavedRepliesEditModalTitleText : handledSavedRepliesNewModalTitleText);
        }

        function openModalForEdit(index) {
            var reply = window.handledSavedReplies[index];

            if (!reply) {
                return;
            }

            currentEditIndex = index;
            renderLibraryList();
            fillModalForm(reply);
            closeDropdown();
            getModal().modal('show');
        }

        function openModalForNew(prefillCategory) {
            currentEditIndex = null;
            renderLibraryList();
            fillModalForm(null);
            getModalCategory().val(normalizeCategoryPath(prefillCategory || currentPath.join(' / ')));
            closeDropdown();
            getModal().modal('show');
            window.setTimeout(function() {
                getModalName().focus();
            }, 150);
        }

        function openLibraryModal() {
            if (window.handledSavedReplies.length) {
                openModalForEdit(currentEditIndex !== null && window.handledSavedReplies[currentEditIndex] ? currentEditIndex : 0);
                return;
            }

            openModalForNew('');
        }

        function persistReplies(callback) {
            var saveButton = getModal().find('.handled-saved-replies-modal-save');
            var data = {
                action: 'handled_saved_replies_save',
                mailbox_id: handledSavedRepliesMailboxId,
                conversation_id: handledSavedRepliesConversationId,
                saved_replies_json: JSON.stringify(window.handledSavedReplies || [])
            };

            saveButton.prop('disabled', true);

            fsAjax(data, laroute.route('conversations.ajax'), function(response) {
                saveButton.prop('disabled', false);

                if (isAjaxSuccess(response)) {
                    window.handledSavedReplies = $.isArray(response.saved_replies) ? response.saved_replies : [];
                    renderMenu();
                    renderLibraryList();
                    if ($.isFunction(callback)) {
                        callback();
                    }
                    showFloatingAlert('success', response.msg, true);
                } else {
                    showAjaxError(response, true);
                }
            }, true, function() {
                saveButton.prop('disabled', false);
                showFloatingAlert('error', handledSavedRepliesErrorText, true);
            });
        }

        function saveModalReply() {
            var category = normalizeCategoryPath(getModalCategory().val());
            var name = $.trim(getModalName().val());
            var body = $.trim(getModalBody().val());
            var targetIndex = currentEditIndex;

            if (!name || !body) {
                showFloatingAlert('error', handledSavedRepliesRequiredText, true);
                return;
            }

            if (targetIndex !== null && window.handledSavedReplies[targetIndex]) {
                window.handledSavedReplies[targetIndex] = {
                    category: category,
                    name: name,
                    body: body
                };
            } else {
                window.handledSavedReplies.push({
                    category: category,
                    name: name,
                    body: body
                });
                targetIndex = window.handledSavedReplies.length - 1;
            }

            currentEditIndex = targetIndex;
            persistReplies(function() {
                fillModalForm(window.handledSavedReplies[currentEditIndex] || null);
                renderLibraryList();
            });
        }

        function deleteCurrentReply() {
            if (currentEditIndex === null || !window.handledSavedReplies[currentEditIndex]) {
                return;
            }

            if (!window.confirm(handledSavedRepliesDeleteConfirmText)) {
                return;
            }

            window.handledSavedReplies.splice(currentEditIndex, 1);
            if (window.handledSavedReplies.length) {
                currentEditIndex = Math.min(currentEditIndex, window.handledSavedReplies.length - 1);
            } else {
                currentEditIndex = null;
            }

            persistReplies(function() {
                renderLibraryList();
                fillModalForm(currentEditIndex !== null ? window.handledSavedReplies[currentEditIndex] : null);
            });
        }

        function insertReply(index) {
            var savedReply = window.handledSavedReplies[index];
            var body;

            if (!savedReply) {
                return;
            }

            body = savedReply.rendered_body || savedReply.body || '';
            if (!body) {
                return;
            }

            closeDropdown();
            $('#body').summernote('focus');
            $('#body').summernote('pasteHTML', body);
            onReplyChange();
        }

        $(document)
            .on('show.bs.dropdown', '.handled-saved-replies-dropdown', function() {
                currentPath = [];
                renderMenu();
            })
            .on('click', '.handled-saved-replies-menu [data-menu-action], .handled-saved-replies-manage, .handled-saved-replies-new', function(event) {
                event.preventDefault();
                event.stopPropagation();
            })
            .on('click', '.handled-saved-replies-menu-back', function() {
                currentPath = currentPath.slice(0, -1);
                renderMenu();
            })
            .on('click', '.handled-saved-replies-menu-link[data-menu-action="open-category"]', function() {
                currentPath.push($(this).data('category-segment'));
                renderMenu();
            })
            .on('click', '.handled-saved-replies-menu-link[data-menu-action="insert-reply"]', function() {
                insertReply(parseInt($(this).data('reply-index'), 10));
            })
            .on('click', '.handled-saved-replies-menu-edit[data-menu-action="edit-reply"]', function() {
                openModalForEdit(parseInt($(this).data('reply-index'), 10));
            })
            .on('click', '.handled-saved-replies-manage', function() {
                openLibraryModal();
            })
            .on('click', '.handled-saved-replies-new', function() {
                openModalForNew(currentPath.join(' / '));
            })
            .on('click', '.handled-saved-replies-library-new', function() {
                openModalForNew(currentPath.join(' / '));
            })
            .on('click', '.handled-saved-replies-library-item', function() {
                openModalForEdit(parseInt($(this).data('library-index'), 10));
            })
            .on('click', '.handled-saved-replies-modal-save', function(event) {
                event.preventDefault();
                saveModalReply();
            })
            .on('click', '.handled-saved-replies-modal-delete', function(event) {
                event.preventDefault();
                deleteCurrentReply();
            });

        renderMenu();
    })();
</script>
