<?php

namespace Modules\ClickupIntegration\Extensions\CustomFields;

use Modules\ClickupIntegration\Extensions\Base;
use Eventy;
use Module;
use Option;

class CopyTitleToField extends Base
{
    public const OPTION_TITLE_ENABLED = 'clickupintegration.extension.custom_fields.copy_title_to_field.enabled';
    public const OPTION_TITLE_NAME = 'clickupintegration.extension.custom_fields.copy_title_to_field.name';

    /**
     * @inheritDoc
     *
     * @var string
     */
    protected static $dependentClasses = [
        'CF' => "Modules\CustomFields\Entities\CustomField",
        'CCF' => "Modules\CustomFields\Entities\ConversationCustomField"
    ];

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function load() { // NOSONAR
        /**
         * Triggers when a new conversation is created by the Customer
         */
        Eventy::addAction('conversation.created_by_customer', function($conversation, $thread) {
            // Checking if extension is enabled
            if (! Option::get(self::OPTION_TITLE_ENABLED)) {
                return;
            }

            # Verifying if the thread of conversation is the first, meaning it was started by the customer
            if ($thread->first) {
                $customFieldModel = new self::$dependentClasses['CF'];

                # Criteria to identify which field will contains the title
                $customFieldName = Option::get(self::OPTION_TITLE_NAME);
                $customField = $customFieldModel
                                ->where('name', $customFieldName)
                                ->where('mailbox_id', $conversation->mailbox_id)
                                ->first();

                # Processing only if there is an exact match
                if ($customField) {
                    $conversationCustomFieldModel = new self::$dependentClasses['CCF'];

                    # Searching for an already match at the conversation
                    $conversationCustomField = $conversationCustomFieldModel
                                                ->where('conversation_id', $conversation->id)
                                                ->where('custom_field_id', $customField->id)
                                                ->first();

                    # If it is found
                    if ($conversationCustomField) {
                        # But does not contains a value yet
                        if ($conversationCustomField->value === '') { // NOSONAR
                            # Let's update it with the conversation subject
                            $conversationCustomField->value = $conversation->subject;
                            $conversation->save();
                        }
                    } else {
                        # Otherwise we create a new reference
                        $conversationCustomFieldModel->conversation_id = $conversation->id;
                        $conversationCustomFieldModel->custom_field_id = $customField->id;
                        $conversationCustomFieldModel->value = $conversation->subject;
                        $conversationCustomFieldModel->save();
                    }
                }
            }
        }, 100, 2);

        /**
         * Register new configuration settings for this specific extension
         */
        Eventy::addFilter('settings.clickupintegration.options', function(array $options) {
            $options[] = self::OPTION_TITLE_ENABLED;
            $options[] = self::OPTION_TITLE_NAME;
            return $options;
        });

        /**
         * Registers specific extension setting HTML
         */
        Eventy::addFilter('settings.clickupintegration.extensions.views', function(array $extensions) {
            $extensions['clickupintegration::extensions.customfields.copy-title-to-field'] = [
                'title' => [
                    'enabled' => self::OPTION_TITLE_ENABLED,
                    'name' => self::OPTION_TITLE_NAME
                ]
            ];
            return $extensions;
        });

        // Add extension JS file
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = Module::getPublicPath('clickupintegration').'/js/extensions/copy-title-to-field.js';
            return $javascripts;
        });
    }
}
