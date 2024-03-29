<?php

class CustomerSupport
{
    private ?array $ignoreSoftwareInformation;
    public const EMAIL = "contact@idealistic.ai";

    public function __construct(?array $ignoreSoftwareInformation = null)
    {
        $this->ignoreSoftwareInformation = $ignoreSoftwareInformation;
    }

    public function clearCache(): void
    {
        clear_memory(array(self::class), true, 1, function ($value) {
            return is_array($value);
        });
    }

    public function listTickets(int|string $time = "7 days"): array
    {
        global $customer_support_table;
        set_sql_cache(null, self::class);
        $query = get_sql_query(
            $customer_support_table,
            null,
            array(
                array("resolution_date", null),
                array("creation_date", ">", get_past_date($time))
            ),
            array(
                "DESC",
                "id"
            )
        );

        if (!empty($query)) {
            $array = array();
            $softwareInformation = "software_information";
            $ignore = is_array($this->ignoreSoftwareInformation);

            foreach ($query as $row) {
                foreach (array($softwareInformation, "user_information") as $key) {
                    $value = $row->{$key};

                    if ($value !== null) {
                        $functionality = strtolower($row->functionality);

                        if ($ignore
                            && $key == $softwareInformation
                            && array_key_exists($functionality, $this->ignoreSoftwareInformation)) {
                            $counter = 0;
                            $lines = explode("\\n", $value);

                            foreach ($lines as $linesKey => $linesValue) {
                                foreach ($this->ignoreSoftwareInformation[$functionality] as $value) {
                                    if (str_contains($linesValue, $value)) {
                                        unset($lines[$linesKey]);
                                        $counter++;
                                        break;
                                    }
                                }
                            }
                            if ($counter > 0) {
                                array_unshift($lines, $counter . " CORRECTED LINES REMOVED");
                            }
                            $row->{$key} = $lines;
                        } else {
                            $row->{$key} = explode("\\n", $value);
                        }
                    }
                }
                $array[$row->id] = $row;
                unset($row->id);
            }
            return $array;
        } else {
            return array();
        }
    }
}
