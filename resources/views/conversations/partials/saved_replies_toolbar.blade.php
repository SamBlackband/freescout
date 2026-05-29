@php
    $handled_saved_replies = is_array($handled_saved_replies ?? null) ? array_values($handled_saved_replies) : [];
    $handled_saved_reply_trigger_text = __('Saved Replies');
    $handled_saved_reply_menu_title_text = __('Saved replies');
    $handled_saved_reply_back_text = __('Back');
    $handled_saved_reply_empty_menu_text = __('No saved replies yet.');
    $handled_saved_reply_root_replies_text = __('Replies');
    $handled_saved_reply_manage_text = __('Manage library');
    $handled_saved_reply_new_text = __('New reply');
    $handled_saved_reply_preview_text = __('Preview reply');
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

    .handled-tag-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .handled-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .handled-tag-chip-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        opacity: 0.72;
    }

    @media (max-width: 991px) {
        .handled-saved-replies-menu {
            width: 320px;
        }
    }
</style>

<div
    class="btn-group handled-saved-replies-dropdown"
    data-settings-url="{{ $handled_saved_replies_settings_url }}"
    data-return-url="{{ $handled_saved_replies_return_url }}"
    data-can-manage="{{ $handled_saved_reply_can_manage ? 1 : 0 }}"
    data-menu-title="{{ $handled_saved_reply_menu_title_text }}"
    data-back-text="{{ $handled_saved_reply_back_text }}"
    data-empty-text="{{ $handled_saved_reply_empty_menu_text }}"
    data-root-replies-text="{{ $handled_saved_reply_root_replies_text }}"
    data-open-settings-text="{{ $handled_saved_reply_open_settings_text }}"
>
    <script type="application/json" class="handled-saved-replies-data">{!! json_encode($handled_saved_replies, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
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
