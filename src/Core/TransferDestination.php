<?php declare(strict_types=1);
/**
 * @author Cypherbits <info@avanix.es>
 * PHP8 porting and refactor
 */

namespace MoneroIntegrations\MoneroPhp\Core;

class TransferDestination
{

    private TransferAmount $amount;
    private string $address;

    /**
     * TransferDestination constructor.
     * @param TransferAmount $amount
     * @param String $address
     */
    public function __construct(TransferAmount $amount, string $address)
    {
        $this->amount = $amount;
        $this->address = $address;
    }

    /**
     * @param string $amount
     * @param string $amount_type Amount type must be TransferAmount::$TYPE_PICONERO or TransferAmount::$TYPE_MONERO
     * @param string $address
     * @return TransferDestination
     * @throws \Exception
     */
    public static function create(string $amount, string $amount_type, string $address): TransferDestination
    {
        return new TransferDestination(new TransferAmount($amount, $amount_type), $address);
    }

    /**
     * @return \MoneroIntegrations\MoneroPhp\Core\TransferAmount
     */
    public function getAmount(): TransferAmount
    {
        return $this->amount;
    }

    /**
     * @param TransferAmount $amount
     */
    public function setAmount(TransferAmount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return String
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param String $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function toArray(): array
    {
        return ['amount' => $this->amount->getAsPiconero(), 'address' => $this->address];
    }

}