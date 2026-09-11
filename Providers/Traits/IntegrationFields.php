<?php

namespace Modules\ClickupIntegration\Providers\Traits;

use Modules\ClickupIntegration\Providers\ClickupIntegrationServiceProvider as Provider;
use Option;

trait IntegrationFields
{
    // General
    public const FIELD_API_TOKEN = 'api_token';
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_ENVIRONMENT = 'environment';
    // Linking Configuration
    public const FIELD_TEAM_ID = 'team_id';
    public const FIELD_SPACE_ID = 'space_id';
    public const FIELD_LIST_ID = 'list_id';
    // Custom Fields
    public const FIELD_FREESCOUT_ID = 'freescout_id';
    public const FIELD_FREESCOUT_URL = 'freescout_url';
    public const FIELD_SUBMITTER_NAME = 'submitter_name';
    public const FIELD_SUBMITTER_EMAIL = 'submitter_email';
    public const FIELD_DEFAULT_CUSTOM_FIELDS = 'default_custom_fields';

    /**
     * Define a list of module fields, the base name prefixed by the module name.
     * Used to reference the information at the database within its own namespace
     *
     * @var array
     */
    public const MODULE_FIELDS = [
        self::FIELD_API_TOKEN       => Provider::MODULE_NAME . '.' . self::FIELD_API_TOKEN,
        self::FIELD_ENABLED         => Provider::MODULE_NAME . '.' . self::FIELD_ENABLED,
        self::FIELD_ENVIRONMENT     => Provider::MODULE_NAME . '.' . self::FIELD_ENVIRONMENT,
        self::FIELD_TEAM_ID         => Provider::MODULE_NAME . '.' . self::FIELD_TEAM_ID,
        self::FIELD_SPACE_ID        => Provider::MODULE_NAME . '.' . self::FIELD_SPACE_ID,
        self::FIELD_LIST_ID         => Provider::MODULE_NAME . '.' . self::FIELD_LIST_ID,
        self::FIELD_FREESCOUT_ID    => Provider::MODULE_NAME . '.' . self::FIELD_FREESCOUT_ID,
        self::FIELD_FREESCOUT_URL   => Provider::MODULE_NAME . '.' . self::FIELD_FREESCOUT_URL,
        self::FIELD_SUBMITTER_NAME  => Provider::MODULE_NAME . '.' . self::FIELD_SUBMITTER_NAME,
        self::FIELD_SUBMITTER_EMAIL => Provider::MODULE_NAME . '.' . self::FIELD_SUBMITTER_EMAIL,
        self::FIELD_DEFAULT_CUSTOM_FIELDS => Provider::MODULE_NAME . '.' . self::FIELD_DEFAULT_CUSTOM_FIELDS,
    ];

    /**
     * Returns the API Token stored at Settings
     *
     * @return string
     */
    public static function getApiToken()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_API_TOKEN]);
    }

    /**
     * Returns true if the integration is enabled
     *
     * @return string
     */
    public static function isEnabled()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_ENABLED]);
    }

    /**
     * Returns the Environment stored at Settings
     *
     * @return string
     */
    public static function getEnvironment()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_ENVIRONMENT]);
    }

    /**
     * Returns the Team Id stored at Settings
     *
     * @return integer
     */
    public static function getTeamId()
    {
        return (int) Option::get(self::MODULE_FIELDS[self::FIELD_TEAM_ID]);
    }

    /**
     * Returns the Space Id stored at Settings
     *
     * @return integer
     */
    public static function getSpaceId()
    {
        return (int) Option::get(self::MODULE_FIELDS[self::FIELD_SPACE_ID]);
    }

    /**
     * Returns the List Id stored at Settings
     *
     * @return integer
     */
    public static function getListId()
    {
        return (int) Option::get(self::MODULE_FIELDS[self::FIELD_LIST_ID]);
    }

    /**
     * Returns the custom field id for "Freescout Id"
     *
     * @return string
     */
    public static function getFreescoutIdFID()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_FREESCOUT_ID]);
    }

    /**
     * Returns the custom field id for "Freescout URL"
     *
     * @return string
     */
    public static function getFreescoutURLFID()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_FREESCOUT_URL]);
    }

    /**
     * Returns the custom field if for "Submitter Name"
     *
     * @return string
     */
    public static function getSubmitterNameFID()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_SUBMITTER_NAME]);
    }

    /**
     * Returns the custom field id for "Submitter Name"
     *
     * @return string
     */
    public static function getSubmitterEmailFID()
    {
        return Option::get(self::MODULE_FIELDS[self::FIELD_SUBMITTER_EMAIL]);
    }

    /**
     * Returns the default custom fields to set on every task this module creates,
     * parsed from the "Default Custom Fields" textarea setting.
     *
     * @return array<string,string>
     */
    public static function getDefaultCustomFields()
    {
        return self::parseDefaultCustomFields(Option::get(self::MODULE_FIELDS[self::FIELD_DEFAULT_CUSTOM_FIELDS]));
    }

    /**
     * Parses the "field_id=value" per line setting into an array.
     * Blank lines and lines starting with # are ignored, a line is split on
     * the first "=" only, a line with an empty id or value is skipped, and
     * the four FreeScout custom field ids always win over a default.
     *
     * @param string|null $raw
     * @return array<string,string>
     */
    private static function parseDefaultCustomFields($raw)
    {
        $reserved = [
            self::getFreescoutIdFID(),
            self::getFreescoutURLFID(),
            self::getSubmitterNameFID(),
            self::getSubmitterEmailFID(),
        ];

        $fields = [];

        foreach (explode("\n", (string) $raw) as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $separator = strpos($line, '=');
            if ($separator === false) {
                continue;
            }

            $fieldId = trim(substr($line, 0, $separator));
            $value = trim(substr($line, $separator + 1));

            if ($fieldId === '' || $value === '' || in_array($fieldId, $reserved, true)) {
                continue;
            }

            $fields[$fieldId] = $value;
        }

        return $fields;
    }
}