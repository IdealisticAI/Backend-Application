<?php

function running_memory_process($identifier, $time = true, $hash = true): bool
{
    global $memory_processes_table;
    load_sql_database(SqlDatabaseCredentials::MEMORY);
    $query = get_sql_query(
        $memory_processes_table,
        array("running", "next_repetition"),
        array(
            array("identifier", $hash ? string_to_integer($identifier) : $identifier)
        ),
        null,
        1
    );
    load_previous_sql_database();
    return !empty($query)
        && ($query[0]->running !== null
            || $time && $query[0]->next_repetition >= time());
}

function start_memory_process($identifier, $processSeconds = 0, $forceful = false, $hash = true): bool
{
    global $memory_processes_table;

    if ($hash) {
        $identifier = string_to_integer($identifier);
    }
    if ($processSeconds === 0) {
        $processSeconds = 60; // Max script execution time in seconds
    }
    load_sql_database(SqlDatabaseCredentials::MEMORY);
    $query = get_sql_query(
        $memory_processes_table,
        array("running", "next_repetition"),
        array(
            array("identifier", $identifier)
        ),
        null,
        1
    );

    if (empty($query)) {
        if (sql_insert(
            $memory_processes_table,
            array(
                "identifier" => $identifier,
                "running" => 1,
                "next_repetition" => (time() + $processSeconds)
            )
        )) {
            load_previous_sql_database();
            return true;
        }
    } else if (($query[0]->running === null || $query[0]->next_repetition < time())
        && set_sql_query(
            $memory_processes_table,
            array(
                "running" => 1,
                "next_repetition" => (time() + $processSeconds)
            ),
            array(
                array("identifier", $identifier)
            )
        )) {
        load_previous_sql_database();
        return true;
    }
    if ($forceful) {
        return start_memory_process($identifier, $processSeconds, $forceful, $hash);
    } else {
        load_previous_sql_database();
    }
    return false;
}

function end_memory_process($identifier, $hash = true): void
{
    global $memory_processes_table;
    load_sql_database(SqlDatabaseCredentials::MEMORY);
    set_sql_query(
        $memory_processes_table,
        array(
            "running" => null
        ),
        array(
            array("identifier", $hash ? string_to_integer($identifier) : $identifier)
        ),
        null,
        1
    );
    load_previous_sql_database();
}
