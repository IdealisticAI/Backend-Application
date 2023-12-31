<?php

class MinecraftPlatform
{

    private ?string $username;
    private ?int $id, $platform;

    public function __construct(string $url)
    {
        if (!empty($url)
            && (str_contains($url, "/members/")
                || str_contains($url, "/member/")
                || str_contains($url, "/users/")
                || str_contains($url, "/user/"))) {
            $containsHTTP = str_contains($url, "http://")
                || str_contains($url, "https://");

            if (!$containsHTTP) {
                return;
            }
            $explode = explode("/", $url, 7);
            $size = sizeof($explode);
            $username = null;
            $id = null;
            $platform = null;

            if ($size === 5 || $size === 6) {
                $trial = $explode[4];
                $explode = explode(".", $trial, 2); // Do not look further than the first dot

                if (sizeof($explode) === 2) {
                    $trial = $explode[1];

                    if (is_numeric($trial)) {
                        $id = $trial;
                        $username = $explode[0];

                        if (empty($username)) {
                            $username = null;
                        } else {
                            $url = str_replace($username, "", $url); // Remove username from URL for the search that follows
                        }
                    }
                } else if (is_numeric($trial)) {
                    $id = $trial;
                }
            }

            if ($id !== null) {
                $query = get_accepted_platforms(array("id", "name", "domain"));

                if (!empty($query)) {
                    foreach ($query as $acceptedPlatform) {
                        if (str_contains($url, $acceptedPlatform->name . "." . $acceptedPlatform->domain)) {
                            $platform = $acceptedPlatform->id;
                            break;
                        }
                    }
                    if ($platform !== null) {
                        $this->username = $username;
                        $this->id = $id;
                        $this->platform = $platform;
                    } else {
                        $this->username = null;
                        $this->id = null;
                        $this->platform = null;
                    }
                } else {
                    $this->username = null;
                    $this->id = null;
                    $this->platform = null;
                }
            } else {
                $this->username = null;
                $this->id = null;
                $this->platform = null;
            }
        } else {
            $this->username = null;
            $this->id = null;
            $this->platform = null;
        }
    }

    public function isValid(): bool // Username is optional
    {
        return $this->id != null
            && $this->platform != null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getID(): ?int
    {
        return $this->id;
    }

    public function getPlatform(): ?int
    {
        return $this->platform;
    }
}
