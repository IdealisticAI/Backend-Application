<?php

class AccountObjectives
{

    private Account $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function get(): array
    {
        if (!$this->account->getEmail()->isVerified()) {
            return $this->create(
                array(),
                "Email Verification",
                "Verify your email by clicking the verification link we have emailed you.",
            );
        } else {
            global $website_account_url;

            $array = array();
            $paypal = $this->account->getAccounts()->hasAdded(AccountAccounts::PAYPAL_EMAIL, null, 1)->isPositiveOutcome();

            if (!$this->account->getAccounts()->hasAdded(AccountAccounts::STRIPE_EMAIL, null, 1)->isPositiveOutcome()) {
                $array = $this->create(
                    $array,
                    "Purchases & Transactions",
                    "Add your" . (!$paypal ? " PayPal and " : " ") . "Stripe email to have your purchases identified.",
                    $website_account_url . "/profile/addAccount",
                    true,
                    "7 days"
                );
            } else if (!$paypal) {
                $array = $this->create(
                    $array,
                    "Purchases & Transactions",
                    "Add your PayPal email to have your purchases identified.",
                    $website_account_url . "/profile/addAccount",
                    true,
                    "7 days"
                );
            }
            if (!$this->account->getAccounts()->hasAdded(AccountAccounts::SPIGOTMC_URL, null, 1)->isPositiveOutcome()
                && !$this->account->getAccounts()->hasAdded(AccountAccounts::BUILTBYBIT_URL, null, 1)->isPositiveOutcome()
                && !$this->account->getAccounts()->hasAdded(AccountAccounts::POLYMART_URL, null, 1)->isPositiveOutcome()) {
                $array = $this->create( // Do not mention SpigotMC, it's automatically found
                    $array,
                    "Minecraft Platform",
                    "Add your SpigotMC/BuiltByBit/Polymart account URL to have your licenses identified.",
                    $website_account_url . "/profile/addAccount",
                    true,
                    "7 days"
                );
            }
            if (!$this->account->getAccounts()->hasAdded(AccountAccounts::DISCORD_TAG, null, 1)->isPositiveOutcome()) {
                $array = $this->create(
                    $array,
                    "Discord Tag",
                    "Add your Discord-Tag so we can give you roles on Discord now or in the future.",
                    $website_account_url . "/profile/addAccount",
                    true,
                    "7 days"
                );
            }
            if (!$this->account->getAccounts()->hasAdded(AccountAccounts::PATREON_FULL_NAME, null, 1)->isPositiveOutcome()) {
                $array = $this->create(
                    $array,
                    "Patreon Full Name",
                    "Add your Patreon-Full-Name to have your purchases identified.",
                    $website_account_url . "/profile/addAccount",
                    true,
                    "7 days"
                );
            }
            return $array;
        }
    }

    private function create(array   $array,
                            string  $title, string $description,
                            ?string $url = null, bool $optionalURL = false,
                            ?string $duration = null): array
    {
        if ($duration === null || get_past_date($duration) <= $this->account->getDetail("creation_date")) {
            $object = new stdClass();
            $object->title = $title;
            $object->description = $description;
            $object->url = $url;
            $object->optional_url = $optionalURL;
            $array[] = $object;
        }
        return $array;
    }

    public function has(): bool
    {
        return !empty($this->get());
    }
}
