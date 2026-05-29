<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // To avoid MySQL error in packages:
        // "SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes"
        Schema::defaultStringLength(191);

        // Models observers
        \App\Mailbox::observe(\App\Observers\MailboxObserver::class);
        // Eloquent events for this table are not called automatically, so need to be called manually.
        //\App\MailboxUser::observe(\App\Observers\MailboxUserObserver::class);
        \App\Email::observe(\App\Observers\EmailObserver::class);
        \App\User::observe(\App\Observers\UserObserver::class);
        \App\Conversation::observe(\App\Observers\ConversationObserver::class);
        \App\Customer::observe(\App\Observers\CustomerObserver::class);
        \App\Thread::observe(\App\Observers\ThreadObserver::class);
        \App\Attachment::observe(\App\Observers\AttachmentObserver::class);
        \App\Follower::observe(\App\Observers\FollowerObserver::class);
        \Illuminate\Notifications\DatabaseNotification::observe(\App\Observers\DatabaseNotificationObserver::class);

        \Eventy::addAction('thread.created', function ($thread) {
            app(\App\Services\HandledSupportSyncEmitter::class)->emitThreadCreated($thread);
        }, 20, 1);

        \Eventy::addAction('conversation.updated', function ($conversation) {
            app(\App\Services\HandledSupportSyncEmitter::class)->emitConversationUpdated($conversation);
        }, 20, 1);

        \Eventy::addFilter('settings.sections', function ($sections) {
            $sections['saved_replies'] = ['title' => __('Saved Replies'), 'icon' => 'comment', 'order' => 350];
            $sections['tags'] = ['title' => __('Tags'), 'icon' => 'tag', 'order' => 360];

            return $sections;
        }, 20, 1);

        \Eventy::addFilter('settings.section_settings', function ($settings, $section) {
            if ($section === 'saved_replies') {
                return [
                    'handled_saved_replies' => app(\App\Services\HandledSavedRepliesService::class)->getStoredReplies(),
                ];
            }

            if ($section === 'tags') {
                return [
                    'handled_tags_page' => true,
                ];
            }

            return $settings;
        }, 20, 2);

        \Eventy::addFilter('settings.section_params', function ($params, $section) {
            if ($section === 'saved_replies') {
                return [
                    'validator_rules' => [
                        'settings.handled_saved_replies' => 'array',
                        'settings.handled_saved_replies.*.category' => 'nullable|string|max:160',
                        'settings.handled_saved_replies.*.name' => 'nullable|string|max:80',
                        'settings.handled_saved_replies.*.body' => 'nullable|string|max:20000',
                        'settings.handled_saved_replies.*.tag_ids' => 'nullable|array',
                    ],
                    'settings' => [
                        'handled_saved_replies' => [
                            'default' => [],
                        ],
                    ],
                    'template_vars' => [
                        'handled_tag_options' => app(\App\Services\HandledTagsService::class)->getTagOptions(),
                    ],
                ];
            }

            if ($section === 'tags') {
                return [
                    'settings' => [
                        'handled_tags_page' => [
                            'default' => true,
                        ],
                    ],
                    'template_vars' => [
                        'handled_tags' => app(\App\Services\HandledTagsService::class)->getManagementTags(),
                        'handled_tag_options' => app(\App\Services\HandledTagsService::class)->getTagOptions(),
                    ],
                ];
            }

            return $params;
        }, 20, 2);

        \Eventy::addFilter('settings.view', function ($view, $section) {
            if ($section === 'saved_replies') {
                return 'settings.saved_replies';
            }

            if ($section === 'tags') {
                return 'settings.tags';
            }

            return $view;
        }, 20, 2);

        \Eventy::addFilter('settings.before_save', function ($request, $section) {
            if ($section !== 'saved_replies') {
                return $request;
            }

            $savedReplies = $request->input('settings.handled_saved_replies', []);
            $sanitizedReplies = app(\App\Services\HandledSavedRepliesService::class)->normalizeReplies(is_array($savedReplies) ? $savedReplies : []);

            $settings = $request->input('settings', []);
            $settings['handled_saved_replies'] = $sanitizedReplies;
            $request->merge(['settings' => $settings]);

            return $request;
        }, 20, 2);

        \Eventy::addAction('conv_editor.editor_toolbar_prepend', function ($mailbox, $conversation) {
            echo view('conversations.partials.saved_replies_toolbar', [
                'handled_saved_replies' => app(\App\Services\HandledSavedRepliesService::class)->getComposerReplies($conversation),
                'handled_saved_replies_mailbox_id' => $mailbox ? $mailbox->id : null,
                'handled_saved_replies_conversation_id' => $conversation ? $conversation->id : null,
            ])->render();
        }, 20, 2);

        \Eventy::addAction('conversation.after_subject', function ($conversation) {
            echo view('conversations.partials.handled_tags_summary', [
                'conversation' => $conversation,
            ])->render();
        }, 20, 1);

        \Eventy::addAction('reply_form.after', function ($conversation) {
            $user = auth()->user();
            $tagsService = app(\App\Services\HandledTagsService::class);

            if (!$conversation || !$tagsService->canManageTags($user) || !$user->can('update', $conversation)) {
                return;
            }

            echo view('conversations.partials.handled_tags_picker', [
                'handled_can_manage_tags' => true,
                'handled_conversation_id' => $conversation->id,
                'handled_tag_options' => $tagsService->getTagOptions(),
                'handled_tag_selected_ids' => $conversation->handledTags()->pluck('handled_tags.id')->all(),
            ])->render();
        }, 20, 1);

        \Eventy::addAction('new_conversation_form.after', function ($conversation) {
            $user = auth()->user();
            $tagsService = app(\App\Services\HandledTagsService::class);

            if (!$tagsService->canManageTags($user)) {
                return;
            }

            echo view('conversations.partials.handled_tags_picker', [
                'handled_can_manage_tags' => true,
                'handled_tag_options' => $tagsService->getTagOptions(),
                'handled_tag_selected_ids' => request()->input('handled_tag_ids', []),
            ])->render();
        }, 20, 1);

        \Eventy::addFilter('conversations_table.preload_table_data', function ($conversations) {
            return app(\App\Services\HandledTagsService::class)->preloadConversationTags($conversations);
        }, 20, 1);

        \Eventy::addAction('conversations_table.after_subject', function ($conversation) {
            echo view('conversations.partials.handled_tags_summary', [
                'conversation' => $conversation,
                'handled_show_empty_state' => false,
            ])->render();
        }, 20, 1);

        \Eventy::addAction('search.display_filters', function ($filters, $filters_data, $mode) {
            if ($mode !== \App\Conversation::SEARCH_MODE_CONV) {
                return;
            }

            echo view('conversations.partials.handled_tags_search_filter', [
                'filters' => $filters,
                'handled_tags' => app(\App\Services\HandledTagsService::class)->getTagOptions(),
            ])->render();
        }, 20, 3);

        \Eventy::addFilter('search.filters_list', function ($filtersList, $mode) {
            if ($mode === \App\Conversation::SEARCH_MODE_CONV && !in_array('tag', $filtersList)) {
                $filtersList[] = 'tag';
            }

            return $filtersList;
        }, 20, 2);

        \Eventy::addFilter('search.filters', function ($filters, $mode) {
            if ($mode !== \App\Conversation::SEARCH_MODE_CONV) {
                return $filters;
            }

            if (!empty($filters['tag'])) {
                $filters['tag'] = app(\App\Services\HandledTagsService::class)->getValidTagIds($filters['tag']);
            }

            return $filters;
        }, 20, 2);

        \Eventy::addFilter('search.conversations.apply_filters', function ($query, $filters) {
            if (empty($filters['tag'])) {
                return $query;
            }

            $tagIds = app(\App\Services\HandledTagsService::class)->getValidTagIds($filters['tag']);
            if (!count($tagIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereExists(function ($subQuery) use ($tagIds) {
                $subQuery->select(\DB::raw(1))
                    ->from('handled_conversation_tag')
                    ->whereColumn('handled_conversation_tag.conversation_id', 'conversations.id')
                    ->whereIn('handled_conversation_tag.tag_id', $tagIds);
            });
        }, 20, 2);

        \Eventy::addAction('bulk_actions.before_delete', function ($folder) {
            $tagsService = app(\App\Services\HandledTagsService::class);

            if (!$tagsService->canManageTags(auth()->user())) {
                return;
            }

            echo view('conversations.partials.handled_tags_bulk_actions', [
                'handled_tag_options' => $tagsService->getTagOptions(),
            ])->render();
        }, 20, 1);

        \Validator::extend('safehost', function ($attribute, $value, $parameters, $validator) {
            if (!$value) {
                return true;
            }
            $msg = '';
            try {
                $url = $value;
                if (!preg_match("#^https?://#", $value)) {
                    $url = 'https://'.$url;
                }
                \Helper::checkUrlIpAndHost($url, true);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
            if ($msg) {
                $validator->errors()->add($attribute, $msg);
                return false;
            }

            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Forse HTTPS if using CloudFlare "Flexible SSL"
        // https://support.cloudflare.com/hc/en-us/articles/200170416-What-do-the-SSL-options-mean-
        if (\Helper::isHttps()) {
            // $_SERVER['HTTPS'] = 'on';
            // $_SERVER['SERVER_PORT'] = '443';
            $this->app['url']->forceScheme('https');
        }

        // If APP_KEY is not set, redirect to /install.php
        if (!\Config::get('app.key') && !app()->runningInConsole() && !file_exists(storage_path('.installed'))) {
            // Not defined here yet
            //\Artisan::call("freescout:clear-cache");
            redirect(\Helper::getSubdirectory().'/install.php')->send();
        }

        // Process module registration error - disable module and show error to admin
        \Eventy::addFilter('modules.register_error', function ($exception, $module) {

            $msg = __('The :module_name module has been deactivated due to an error: :error_message', ['module_name' => $module->getName(), 'error_message' => $exception->getMessage()]);

            \Log::error($msg);

            // request() does is empty at this stage
            if (!empty($_POST['action']) && $_POST['action'] == 'activate') {

                // During module activation in case of any error we have to deactivate module.
                \App\Module::deactiveModule($module->getAlias());

                \Session::flash('flashes_floating', [[
                    'text' => $msg,
                    'type' => 'danger',
                    'role' => \App\User::ROLE_ADMIN,
                ]]);

                return;
            } elseif (empty($_POST)) {

                // failed to open stream: No such file or directory
                if (strstr($exception->getMessage(), 'No such file or directory')) {
                    \App\Module::deactiveModule($module->getAlias());

                    \Session::flash('flashes_floating', [[
                        'text' => $msg,
                        'type' => 'danger',
                        'role' => \App\User::ROLE_ADMIN,
                    ]]);
                }

                return;
            }

            return $exception;
        }, 10, 2);
    }
}
