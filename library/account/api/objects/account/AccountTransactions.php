<?php

class AccountTransactions
{
    private Account $account;

    public function __construct($account)
    {
        $this->account = $account;
    }

    public function getSuccessful($types = null, $limit = PaymentProcessor::limit): array
    {
        $cacheKey = array(self::class, $this->account->getDetail("id"), $types, $limit, "successful");
        $cache = get_key_value_pair($cacheKey);

        if (is_array($cache)) {
            return $cache;
        }
        $array = array();

        foreach ($this->getTypes($types) as $transactionType) {
            $loopArray = array();

            switch ($transactionType) {
                case PaymentProcessor::PAYPAL:
                    $credential = $this->account->getAccounts()->hasAdded($transactionType);

                    if ($credential->isPositiveOutcome()) {
                        foreach ($credential->getObject() as $credential) {
                            foreach (find_paypal_transactions_by_data_pair(array("EMAIL" => extra_sql_encode($credential)), $limit, true) as $transactionID => $transaction) {
                                $loopArray[$transactionID] = $transaction;
                                $this->process($transaction);
                            }
                        }
                    }
                    break;
                case PaymentProcessor::STRIPE:
                    $credential = $this->account->getAccounts()->hasAdded($transactionType);

                    if ($credential->isPositiveOutcome()) {
                        foreach ($credential->getObject() as $credential) {
                            foreach (find_stripe_transactions_by_data_pair(array("source.billing_details.email" => extra_sql_encode($credential)), $limit) as $transactionID => $transaction) {
                                $loopArray[$transactionID] = $transaction;
                                $this->process($transaction);
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
            $array = array_merge($array, $loopArray);
        }
        set_key_value_pair($cacheKey, $array);
        return $array;
    }

    public function getFailed($types = null, $limit = PaymentProcessor::limit): array
    {
        $cacheKey = array(self::class, $this->account->getDetail("id"), $types, $limit, "failed");
        $cache = get_key_value_pair($cacheKey);

        if (is_array($cache)) {
            return $cache;
        }
        $array = array();

        foreach ($this->getTypes($types) as $transactionType) {
            foreach (get_failed_paypal_transactions($this->getSuccessful($transactionType, $limit), $limit) as $transaction) {
                $array[] = $transaction;
            }
        }
        set_key_value_pair($cacheKey, $array);
        return $array;
    }

    // Utilities

    private function process($transaction)
    {
        $paymentProcessor = new PaymentProcessor();
        $paymentProcessor = $paymentProcessor->getSource($transaction);

        if (!empty($paymentProcessor)) {
            $this->account->getAccounts()->add($paymentProcessor[0], $paymentProcessor[1]);
        }
    }

    private function getTypes($type): array
    {
        if ($type === null) {
            $type = PaymentProcessor::ALL_TYPES;
        } else if (!is_array($type)) {
            $type = array($type);
        }
        return $type;
    }
}
