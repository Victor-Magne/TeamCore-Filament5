<?php

use Spatie\Activitylog\Models\Activity;

return [
    /*
     * If set to false, no activities will be recorded.
     */
    'enabled' => true,

    /*
     * When set to true, the subject and causer will be recorded as well as the log name and event.
     */
    'log_fill_data' => true,

    /*
     * Each activity will be logged into this table.
     */
    'table_name' => 'activity_log',

    /*
     * This model will be used to log activity.
     * The only requirement is that it should extend `Spatie\Activitylog\Models\Activity`.
     */
    'activity_model' => Activity::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model returned above.
     */
    'activities_table_name' => 'activity_log',

    'default_log_name' => 'default',

    /*
     * When logging updates the old values will be stored automatically.
     */
    'log_only_dirty' => false,

    /*
     * When set to true, the Laravel dispatcher will be used to dispatch
     * the ActivityLoggedEvent, ActivityCreatedEvent, ActivityUpdatedEvent and ActivityDeletedEvent.
     */
    'use_database_transactions' => true,

    'activity_logger' => '\\Spatie\\Activitylog\\ActivityLogger',
];
