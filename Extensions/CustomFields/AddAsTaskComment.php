<?php

namespace Modules\ClickupIntegration\Extensions\CustomFields;

use Illuminate\Support\Collection;
use App\Conversation;
use Modules\ClickupIntegration\Extensions\Base;
use Modules\ClickupIntegration\Services\ClickupService;
use Eventy;
use Option;

class AddAsTaskComment extends Base
{
    public const OPTION_ENABLED = 'clickupintegration.extension.custom_fields.add_as_task_comment.enabled';

    /**
     * ClickUp Service
     *
     * @var ClickupService
     */
    private ClickupService $clickupService;

    /**
     * @inheritDoc
     *
     * @var string
     */
    protected static $dependentClasses = [
        'CF' => "Modules\CustomFields\Entities\CustomField"
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clickupService = new ClickupService;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function load() {
        /**
         * Triggers after a new ClickUp Task was created
         */
        Eventy::addAction('clickupintegration.on_create_task', function($conversationId, $taskId) {
            // Checking if extension is enabled
            if (! Option::get(self::OPTION_ENABLED)) {
                return;
            }

            /**
             * @var Conversation
             */
            $conversation = Conversation::find($conversationId);
            $customFields = (new self::$dependentClasses['CF'])
                                ->getCustomFieldsWithValues($conversation->mailbox_id, $conversationId);

            # Prepending initial information related to the Conversation URL
            $customFields->prepend((Object) [
                'options'   => [],
                'name'      => 'FreeScout URL',
                'value'     => $conversation->url()
            ]);

            $comment = $this->customFieldsAsComment($customFields);
            $this->clickupService->addComment($taskId, $comment);
        }, 10, 2);

        /**
         * Register new configuration settings for this specific extension
         */
        Eventy::addFilter('settings.clickupintegration.options', function(array $options) {
            $options[] = self::OPTION_ENABLED;
            return $options;
        });

        /**
         * Registers specific extension setting HTML
         */
        Eventy::addFilter('settings.clickupintegration.extensions.views', function(array $extensions) {
            $extensions['clickupintegration::extensions.customfields.add-as-task-comment'] = [
                'enabled' => self::OPTION_ENABLED,
            ];
            return $extensions;
        });
    }

    /**
     * Returns a collection of custom fields as a ClickUp comment
     *
     * @param Collection $customFields
     * @return array
     */
    private function customFieldsAsComment(Collection $customFields)
    {
        $comment = [];
        $realIndex = 1;

        $customFields->each(function ($customField) use (&$comment, &$realIndex) {
            # It could come as part of a dropdown options, otherwise it's simple text
            $answer = $customField->options[$customField->value] ?? $customField->value;

            if ($answer) {
                # Question
                $comment[] = [
                    'text' => sprintf("%d. %s\n", $realIndex++, $customField->name),
                    'attributes' => [
                        'bold' => true
                    ]
                ];
                # Answer
                $comment[] = [
                    'text' => sprintf("\t• %s\n\n", $answer),
                    'attributes' => []
                ];
            }
        });

        return $comment;
    }
}
