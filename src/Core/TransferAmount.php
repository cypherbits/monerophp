<?php


namespace MoneroIntegrations\MoneroPhp\Core;


class TransferAmount
{
    private int $amount;

    public static string $TYPE_MONERO = "MONERO";
    public static string $TYPE_PICONERO = "PICONERO";

    /**
     * TransferAmount constructor.
     * @param string $amount
     * @param string $amount_type Amount type must be TransferAmount::$TYPE_PICONERO or TransferAmount::$TYPE_MONERO
     * @throws \Exception
     */
    public function __construct(string $amount, string $amount_type)
    {
        $this->amount = match ($amount_type) {
            self::$TYPE_PICONERO => ((float)$amount !== floor((float)$amount)) ? throw new \Exception("Error: Piconero type amount cannot have decimals") : (int)$amount,
            self::$TYPE_MONERO => self::transformToPiconero($amount),
            default => throw new \Exception("Error: Amount type must be TransferAmount::\$TYPE_PICONERO or TransferAmount::\$TYPE_MONERO"),
        };
    }

    public function getAsPiconero(): int
    {
        return $this->amount;
    }

    public function getAsMonero(): string
    {
        return bcdiv($this->amount, 1000000000000);
    }


    /**
     *
     * Convert from moneroj to tacoshi (piconero)
     *
     * @param string $amount
     * @return int
     * @throws \Exception
     */
    public static function transformToPiconero(string $amount) : int
    {
        return ((int) $amount > 9223372) ? throw new \Exception("Error: FIXME: too much Monero to send. Max Monero Amount is 9223372") : (int) bcmul($amount, 1000000000000);
    }


}