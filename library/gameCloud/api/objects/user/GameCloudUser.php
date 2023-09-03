<?php

class GameCloudUser
{
    private ?int $platform, $license;
    private GameCloudActions $actions;
    private GameCloudInformation $information;
    private GameCloudVerification $verification;
    private GameCloudEmail $email;

    public function __construct($platform, $license)
    {
        $this->platform = $platform;
        $this->license = $license;
        $this->actions = new GameCloudActions($this);
        $this->information = new GameCloudInformation($this);
        $this->verification = new GameCloudVerification($this);
        $this->email = new GameCloudEmail($this);
    }

    public function getPlatform(): ?int
    {
        return $this->platform;
    }

    public function getLicense(): ?int
    {
        return $this->license;
    }

    public function isValid(): bool
    {
        return $this->platform !== null
            && $this->platform > 0
            && $this->license !== null
            && $this->license > 0;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function getActions(): GameCloudActions
    {
        return $this->actions;
    }

    public function getInformation(): GameCloudInformation
    {
        return $this->information;
    }

    public function getVerification(): GameCloudVerification
    {
        return $this->verification;
    }

    public function getEmail(): GameCloudEmail
    {
        return $this->email;
    }
}
