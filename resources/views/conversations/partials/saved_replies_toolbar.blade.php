@php
    $handled_saved_replies = is_array($handled_saved_replies ?? null) ? array_values($handled_saved_replies) : [];
    $handled_saved_reply_trigger_text = __('Saved Replies');
    $handled_saved_reply_menu_title_text = __('Saved replies');
    $handled_saved_reply_back_text = __('Back');
    $handled_saved_reply_empty_menu_text = __('No saved replies yet.');
    $handled_saved_reply_root_replies_text = __('Replies');
    $handled_saved_reply_manage_text = __('Manage library');
    $handled_saved_reply_new_text = __('New reply');
    $handled_saved_reply_open_settings_text = __('Open settings');
    $handled_saved_reply_edit_text = __('Edit');
    $handled_saved_reply_manage_hint_text = __('Manage saved replies in settings');
    $handled_saved_reply_can_manage = Auth::user() && Auth::user()->isAdmin();
    $handled_saved_replies_settings_url = route('settings', ['section' => 'saved_replies']);
    $handled_saved_replies_return_url = \Request::fullUrl();
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
    .handled-saved-replies-menu-settings:hover {
        background: #f8fafc;
        text-decoration: none;
    }

    .handled-saved-replies-menu-edit {
        flex: 0 0 auto;
        width: 42px;
        color: #6b7280;
    }

    .handled-saved-replies-menu-empty,
    .handled-saved-replies-menu-help {
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

    .handled-saved-replies-menu-footer .btn,
    .handled-saved-replies-menu-footer .handled-saved-replies-menu-settings {
        flex: 1 1 0;
    }

    .handled-saved-replies-menu-settings {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 991px) {
        .handled-saved-replies-menu {
            width: 320px;
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
        @if ($handled_saved_reply_can_manage)
            <div class="handled-saved-replies-menu-footer">
                <button type="button" class="btn btn-default btn-sm handled-saved-replies-manage">{{ $handled_saved_reply_manage_text }}</button>
                <button type="button" class="btn btn-primary btn-sm handled-saved-replies-new">{{ $handled_saved_reply_new_text }}</button>
            </div>
        @else
            <div class="handled-saved-replies-menu-help">{{ $handled_saved_reply_manage_hint_text }}</div>
        @endif
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
        var handledSavedRepliesBackText = @json($handled_saved_reply_back_text);
        var handledSavedRepliesEmptyMenuText = @json($handled_saved_reply_empty_menu_text);
        var handledSavedRepliesRootRepliesText = @json($handled_saved_reply_root_replies_text);
        var handledSavedRepliesOpenSettingsText = @json($handled_saved_reply_open_settings_text);
        var handledSavedRepliesSettingsUrl = @json($handled_saved_replies_settings_url);
        var handledSavedRepliesReturnUrl = @json($handled_saved_replies_return_url);
        var handledSavedRepliesCanManage = @json($handled_saved_reply_can_manage);
        var currentPath = [];

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

        function buildSettingsUrl(extraParams) {
            var params = $.extend({}, extraParams || {});

            if (handledSavedRepliesReturnUrl) {
                params.handled_saved_replies_return = handledSavedRepliesReturnUrl;
            }

            return handledSavedRepliesSettingsUrl + '?' + $.param(params);
        }

        function redirectToSettings(extraParams) {
            window.location.href = buildSettingsUrl(extraParams);
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
                    _index: index
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
            var button = $('<button />', { type: 'button' })
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
            var button = $('<button />', { type: 'button' })
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
            var insertButton = $('<button />', { type: 'button' })
                .addClass('handled-saved-replies-menu-link')
                .attr('data-menu-action', 'insert-reply')
                .attr('data-reply-index', reply._index)
                .append($('<span />').text(reply.name || ''))
                .append($('<span />').addClass('text-muted').html('&nbsp;'));

            row.append(insertButton);

            if (handledSavedRepliesCanManage) {
                row.append(
                    $('<button />', {
                        type: 'button',
                        title: handledSavedRepliesOpenSettingsText
                    })
                        .addClass('handled-saved-replies-menu-edit')
                        .attr('data-menu-action', 'edit-reply')
                        .attr('data-reply-index', reply._index)
                        .append($('<i />').addClass('glyphicon glyphicon-eye-open'))
                );
            }

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
                appendSectionTitle(list, handledSavedRepliesRootRepliesText);
                $.each(replies, function(_, reply) {
                    appendReplyRow(list, reply);
                });
            }
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

            getDropdown().removeClass('open');
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
                redirectToSettings({
                    handled_saved_reply_index: parseInt($(this).data('reply-index'), 10)
                });
            })
            .on('click', '.handled-saved-replies-manage', function() {
                redirectToSettings();
            })
            .on('click', '.handled-saved-replies-new', function() {
                redirectToSettings({
                    handled_saved_reply_action: 'new',
                    handled_saved_reply_category: currentPath.join(' / ')
                });
            });

        renderMenu();
    })();
</script>
