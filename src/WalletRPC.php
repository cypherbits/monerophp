<?php declare(strict_types=1);
/**
 *
 * monerophp/walletRPC
 *
 * A class for making calls to monero-wallet-rpc using PHP
 * https://github.com/monero-integrations/monerophp
 *
 * Using work from
 *   CryptoChangements [Monero_RPC] <bW9uZXJv@gmail.com> (https://github.com/cryptochangements34)
 *   Serhack [Monero Integrations] <nico@serhack.me> (https://serhack.me)
 *   TheKoziTwo [xmr-integration] <thekozitwo@gmail.com>
 *   Kacper Rowinski [jsonRPCClient] <krowinski@implix.com>
 *   Cypherbits <info@avanix.es>
 *
 * @author     Monero Integrations Team <support@monerointegrations.com> (https://github.com/monero-integrations)
 * @copyright  2018
 * @license    MIT
 *
 * ============================================================================
 *
 * // See example.php for more examples
 *
 * // Initialize class
 * $walletRPC = new walletRPC();
 *
 * // Examples:
 * $address = $walletRPC->get_address();
 * $signed = $walletRPC->sign('The Times 03/Jan/2009 Chancellor on brink of second bailout for banks');
 *
 */

namespace MoneroIntegrations\MoneroPhp;

use Exception;
use MoneroIntegrations\MoneroPhp\Core\JsonRPCClient;
use MoneroIntegrations\MoneroPhp\Core\TransferAmount;
use MoneroIntegrations\MoneroPhp\Core\TransferDestination;

class WalletRPC
{
    private JsonRPCClient $client;
    private bool $checkSSL;
    private string $protocol;
    private string $host;
    private int $port;
    private string $url;
    private null|string $user;
    private null|string $password;
    private bool $useGMP;

    /**
     *
     * Start a connection with the Monero wallet RPC interface (monero-wallet-rpc)
     *
     * @param string|array $host monero-wallet-rpc hostname               (optional)
     * @param int $port monero-wallet-rpc port                   (optional)
     * @param bool $checkSSL
     * @param string|null $user monero-wallet-rpc username               (optional)
     * @param string|null $password monero-wallet-rpc passphrase             (optional)
     */
    public function __construct(string|array $host = '127.0.0.1', int $port = 18081, bool $checkSSL = true, string $user = null, string $password = null)
    {
        if (is_array($host)) { // Parameters passed in as object/dictionary
            $this->host = array_key_exists('host', $host) ? $host['host'] : '127.0.0.1';
            $this->port = array_key_exists('port', $host) ? (int)$host['port'] : 18081;
            $this->checkSSL = !array_key_exists('checkSSL', $host) || $host['checkSSL'];
            $this->user = array_key_exists('user', $host) ? $host['user'] : '';
            $this->password = array_key_exists('password', $host) ? $host['password'] : '';
        } else {
            $this->host = $host;
            $this->port = $port;
            $this->checkSSL = $checkSSL;
            $this->user = $user;
            $this->password = $password;
        }

        $this->protocol = match ($this->checkSSL) {
            true => 'https',
            false => 'http'
        };

        $this->url = $this->protocol . '://' . $this->host . ':' . $this->port . '/';
        $this->client = new JsonRPCClient($this->url, $this->user, $this->password, $this->checkSSL);
        $this->useGMP = extension_loaded('gmp');
    }

    /**
     * @return JsonRPCClient
     */
    public function get_client(): JsonRPCClient
    {
        return $this->client;
    }

    /**
     * @return JsonRPCClient
     */
    public function getClient(): JsonRPCClient
    {
        return $this->client;
    }

    /**
     *
     * Execute command via JsonRPCClient
     *
     * @param string $method RPC method to call
     * @param array|null $params Parameters to pass  (optional)
     *
     * @return array Call result
     */
    private function _run(string $method, array $params = null): array
    {
        return $this->client->_run($method, $params, 'json_rpc');
    }

    /**
     *
     * Print JSON object (for API)
     *
     * @param object $json JSON object to print
     *
     * @return void
     *
     */
    public function _print(mixed $json): void
    {
        echo json_encode($json, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     *
     * Look up an account's balance
     *
     * @param int $account_index Index of account to look up  (optional)
     *
     * @return array  Example: {
     *   "balance": 140000000000,
     *   "unlocked_balance": 50000000000
     * }
     *
     */
    public function get_balance(int $account_index = 0): array
    {
        $params = array('account_index' => $account_index);
        return $this->_run('get_balance', $params);
    }

    /**
     *
     * Alias of get_balance()
     *
     * @param int $account_index
     * @return array
     */
    public function getbalance(int $account_index = 0): array
    {
        return $this->get_balance($account_index);
    }

    /**
     *
     * Look up wallet address(es)
     *
     * @param int $account_index Index of account to look up     (optional)
     * @param int $address_index Index of subaddress to look up  (optional)
     *
     * @return array  Example: {
     *   "address": "A2XE6ArhRkVZqepY2DQ5QpW8p8P2dhDQLhPJ9scSkW6q9aYUHhrhXVvE8sjg7vHRx2HnRv53zLQH4ATSiHHrDzcSFqHpARF",
     *   "addresses": [
     *     {
     *       "address": "A2XE6ArhRkVZqepY2DQ5QpW8p8P2dhDQLhPJ9scSkW6q9aYUHhrhXVvE8sjg7vHRx2HnRv53zLQH4ATSiHHrDzcSFqHpARF",
     *       "address_index": 0,
     *       "label": "Primary account",
     *       "used": true
     *     }, {
     *       "address": "Bh3ttLbjGFnVGCeGJF1HgVh4DfCaBNpDt7PQAgsC2GFug7WKskgfbTmB6e7UupyiijiHDQPmDC7wSCo9eLoGgbAFJQaAaDS",
     *       "address_index": 1,
     *       "label": "",
     *       "used": true
     *     }
     *   ]
     * }
     */
    public function get_address(int $account_index = 0, int $address_index = 0): array
    {
        $params = array('account_index' => $account_index, 'address_index' => $address_index);
        return $this->_run('get_address', $params);
    }

    /**
     * @param string $address Monero address
     * @return array Example: {
     * "index": {
     * "major": 0,
     * "minor": 1
     * }
     * }
     */
    public function get_address_index(string $address): array
    {
        $params = array('address' => $address);
        return $this->_run('get_address_index', $params);
    }

    /**
     *
     * Alias of get_address()
     *
     * @param int $account_index Index of account to look up     (optional)
     * @param int $address_index Index of subaddress to look up  (optional)
     *
     * @return array  Example: {
     *   "address": "427ZuEhNJQRXoyJAeEoBaNW56ScQaLXyyQWgxeRL9KgAUhVzkvfiELZV7fCPBuuB2CGuJiWFQjhnhhwiH1FsHYGQGaDsaBA"
     * }
     */
    public function getaddress(int $account_index = 0, int $address_index = 0): array
    {
        return $this->get_address($account_index = 0, $address_index = 0);
    }

    /**
     *
     * Create a new subaddress
     *
     * @param int $account_index The subaddress account index
     * @param string $label A label to apply to the new subaddress
     *
     * @return array  Example: {
     *   "address": "Bh3ttLbjGFnVGCeGJF1HgVh4DfCaBNpDt7PQAgsC2GFug7WKskgfbTmB6e7UupyiijiHDQPmDC7wSCo9eLoGgbAFJQaAaDS"
     *   "address_index": 1
     * }
     *
     */
    public function create_address(int $account_index = 0, string $label = ''): array
    {
        $params = array('account_index' => $account_index, 'label' => $label);
        $create_address_method = $this->_run('create_address', $params);

        $this->store(); // Save wallet state after subaddress creation

        return $create_address_method;
    }

    /**
     *
     * Label a subaddress
     *
     * @param int  The index of the subaddress to label
     * @param string  The label to apply
     *
     * @return array
     *
     */
    public function label_address(int $index, string $label): array
    {
        $params = array('index' => $index, 'label' => $label);
        return $this->_run('label_address', $params);
    }

    /**
     *
     * Validate a wallet address
     *
     * @param string $address The address to validate.
     * @param bool $strict_nettype boolean (Optional); If true, consider addresses belonging to any of the three Monero networks (mainnet, stagenet, and testnet) valid. Otherwise, only consider an address valid if it belongs to the network on which the rpc-wallet's current daemon is running (Defaults to false).
     * @param bool $allow_openalias boolean (Optional); If true, consider OpenAlias-formatted addresses valid (Defaults to false).
     * @return array Example: {
     * "valid": true, boolean; True if the input address is a valid Monero address.
     * "integrated": false, boolean; True if the given address is an integrated address.
     * "subaddress": false, boolean; True if the given address is a subaddress
     * "nettype": "mainnet", string; Specifies which of the three Monero networks (mainnet, stagenet, and testnet) the address belongs to.
     * "openalias_address": false boolean; True if the address is OpenAlias-formatted.
     * }
     */
    public function validate_address(string $address, bool $strict_nettype = false, bool $allow_openalias = false): array
    {
        $params = array(
            'address' => $address,
            'any_net_type' => $strict_nettype,
            'allow_openalias' => $allow_openalias
        );
        return $this->_run('validate_address', $params);
    }

    /**
     *
     * Look up wallet accounts
     *
     * @param string|null $tag Optional filtering by tag
     *
     * @return array Example: {
     *   "subaddress_accounts": {
     *     "0": {
     *       "account_index": 0,
     *       "balance": 2808597352948771,
     *       "base_address": "A2XE6ArhRkVZqepY2DQ5QpW8p8P2dhDQLhPJ9scSkW6q9aYUHhrhXVvE8sjg7vHRx2HnRv53zLQH4ATSiHHrDzcSFqHpARF",
     *       "label": "Primary account",
     *       "tag": "",
     *       "unlocked_balance": 2717153096298162
     *     },
     *     "1": {
     *       "account_index": 1,
     *       "balance": 0,
     *       "base_address": "BcXKsfrvffKYVoNGN4HUFfaruAMRdk5DrLZDmJBnYgXrTFrXyudn81xMj7rsmU5P9dX56kRZGqSaigUxUYoaFETo9gfDKx5",
     *       "label": "Secondary account",
     *       "tag": "",
     *       "unlocked_balance": 0
     *    },
     *    "total_balance": 2808597352948771,
     *    "total_unlocked_balance": 2717153096298162
     * }
     */
    public function get_accounts(string $tag = null): array
    {
        return (is_null($tag)) ? $this->_run('get_accounts') : $this->_run('get_accounts', array('tag' => $tag));
    }

    /**
     *
     * Create a new account
     *
     * @param string $label Label to apply to new account
     *
     * @return array
     *
     */
    public function create_account(string $label = ''): array
    {
        $params = array('label' => $label);
        $create_account_method = $this->_run('create_account', $params);

        $this->store(); // Save wallet state after account creation

        return $create_account_method;
    }

    /**
     *
     * Label an account
     *
     * @param int $account_index Index of account to label
     * @param string $label Label to apply
     *
     * @return array
     */
    public function label_account(int $account_index, string $label): array
    {
        $params = array('account_index' => $account_index, 'label' => $label);
        $label_account_method = $this->_run('label_account', $params);

        $this->store(); // Save wallet state after account label

        return $label_account_method;
    }

    /**
     *
     * Look up account tags
     *
     * @return array  Example: {
     *   "account_tags": {
     *     "0": {
     *       "accounts": {
     *         "0": 0,
     *         "1": 1
     *       },
     *       "label": "",
     *       "tag": "Example tag"
     *     }
     *   }
     * }
     *
     */
    public function get_account_tags(): array
    {
        return $this->_run('get_account_tags');
    }

    /**
     *
     * Tag accounts
     *
     * @param array $accounts The indices of the accounts to tag
     * @param string $tag Tag to apply
     *
     * @return array
     *
     */
    public function tag_accounts(array $accounts, string $tag): array
    {
        $params = array('accounts' => $accounts, 'tag' => $tag);
        $tag_accounts_method = $this->_run('tag_accounts', $params);

        $this->store(); // Save wallet state after account tagging

        return $tag_accounts_method;
    }

    /**
     *
     * Untag accounts
     *
     * @param array $accounts The indices of the accounts to untag
     *
     * @return array
     *
     */
    public function untag_accounts(array $accounts): array
    {
        $params = array('accounts' => $accounts);
        $untag_accounts_method = $this->_run('untag_accounts', $params);

        $this->store(); // Save wallet state after untagging accounts

        return $untag_accounts_method;
    }

    /**
     *
     * Describe a tag
     *
     * @param string $tag Tag to describe
     * @param string $description Description to apply to tag
     *
     * @return array  Example: {
     *   // TODO example
     * }
     *
     */
    public function set_account_tag_description(string $tag, string $description): array
    {
        $params = array('tag' => $tag, 'description' => $description);
        $set_account_tag_description_method = $this->_run('set_account_tag_description', $params);

        $this->store(); // Save wallet state after describing tag

        return $set_account_tag_description_method;
    }

    /**
     *
     * Look up how many blocks are in the longest chain known to the wallet
     *
     * @return array  Example: {
     *   "height": 994310
     * }
     *
     */
    public function get_height(): array
    {
        return $this->_run('get_height');
    }

    /**
     *
     * Alias of get_height()
     *
     */
    public function getheight(): array
    {
        return $this->get_height();
    }

    /**
     *
     * Send monero
     * Parameters can be passed in individually (as listed below) or as an object/dictionary (as listed at bottom)
     * To send to multiple recipients, use the object/dictionary (bottom) format and pass an array of recipient addresses and amount arrays in the destinations field (as in "destinations = [['amount' => 1, 'address' => ...], ['amount' => 2, 'address' => ...]]")
     *
     * @param array $destinations Each destination can be an Array or TransferDestination object
     * @param int $account_index Account to send from                                      (optional)
     * @param array $subaddr_indices Comma-separated list of subaddress indices to spend from  (optional)
     * @param int $priority Transaction priority                                      (optional)
     * @param int $mixin Mixin number (ringsize - 1)                               (optional)
     * @param int $ring_size
     * @param int $unlock_time UNIX time or block height to unlock output                (optional)
     * @param bool $get_tx_key
     * @param boolean $do_not_relay Do not relay transaction                                  (optional)
     *
     *   OR
     * @param bool $get_tx_hex
     * @param bool $get_tx_metadata
     * @return array Example: {
     *   "amount": "1000000000000",
     *   "fee": "1000020000",
     *   "tx_hash": "c60a64ddae46154a75af65544f73a7064911289a7760be8fb5390cb57c06f2db",
     *   "tx_key": "805abdb3882d9440b6c80490c2d6b95a79dbc6d1b05e514131a91768e8040b04"
     * }
     * @throws \Exception
     */
    public function transfer(array $destinations, int $account_index = 0, array $subaddr_indices = [], int $priority = 2, int $mixin = 10, int $ring_size = 11, int $unlock_time = 0, bool $get_tx_key = true,
                             bool $do_not_relay = false, bool $get_tx_hex = false, bool $get_tx_metadata = false): array
    {
        if (count($destinations) === 0) {
            throw new Exception('Error: At least one destination required. Each destination can be an Array or TransferDestination object.');
        }

        foreach ($destinations as $index => $destination) {
            if ($destination instanceof TransferDestination) {
                $destinations[$index] = $destination->toArray();
            } elseif (!is_array($destination)) {
                throw new Exception("Error: destination items must be an array['amount' => 'piconero', 'address' => 'adress'] or an instance of TransferDestination");
            }
        }
        $params = array('destinations' => $destinations,
            'account_index' => $account_index,
            'subaddr_indices' => $subaddr_indices,
            'priority' => $priority,
            'mixin' => $mixin,
            'ring_size' => $ring_size,
            'unlock_time' => $unlock_time,
            'get_tx_key' => $get_tx_key,
            'do_not_relay' => $do_not_relay,
            'get_tx_hex' => $get_tx_hex,
            'get_tx_metadata' => $get_tx_metadata);

        $transfer_method = $this->_run('transfer', $params);

        $this->store(); // Save wallet state after transfer

        return $transfer_method;
    }

    /**
     *
     * Same as transfer, but splits transfer into more than one transaction if necessary
     *
     */
    public function transfer_split(array $destinations, int $account_index = 0, array $subaddr_indices = [], int $mixin = 10, int $ring_size = 11, int $unlock_time = 0, bool $get_tx_key = true,
                                   int $priority = 2, bool $do_not_relay = false, bool $get_tx_hex = false, bool $new_algorithm = false, bool $get_tx_metadata = false): array
    {
        if (count($destinations) === 0) {
            throw new Exception('Error: At least one destination required. Each destination can be an Array or TransferDestination object.');
        }

        foreach ($destinations as $index => $destination) {
            if ($destination instanceof TransferDestination) {
                $destinations[$index] = $destination->toArray();
            } elseif (!is_array($destination)) {
                throw new Exception("Error: destination items must be an array['amount' => 'piconero', 'address' => 'adress'] or an instance of TransferDestination");
            }
        }
        $params = array('destinations' => $destinations,
            'account_index' => $account_index,
            'subaddr_indices' => $subaddr_indices,
            'mixin' => $mixin,
            'ring_size' => $ring_size,
            'unlock_time' => $unlock_time,
            'get_tx_key' => $get_tx_key,
            'priority' => $priority,
            'do_not_relay' => $do_not_relay,
            'get_tx_hex' => $get_tx_hex,
            'new_algorithm' => $new_algorithm,
            'get_tx_metadata' => $get_tx_metadata);

        $transfer_method = $this->_run('transfer_split', $params);

        $this->store(); // Save wallet state after transfer

        return $transfer_method;
    }

    /**
     *
     * Send all dust outputs back to the wallet
     *
     * @return array  Example: {
     *   // TODO example
     * }
     *
     */
    public function sweep_dust(): array
    {
        return $this->_run('sweep_dust');
    }

    /**
     *
     * Alias of sweep_dust
     *
     * @return array  Example: {
     *   // TODO example
     * }
     *
     */
    public function sweep_unmixable(): array
    {
        return $this->_run('sweep_unmixable');
    }

    /**
     *
     * Send all unlocked outputs from an account to an address
     *
     * @param string $address Address to receive funds
     * @param int $account_index Index of the account to sweep                        (optional)
     * @param array $subaddr_indices Comma-separated list of subaddress indices to sweep  (optional)
     * @param int $priority Payment ID                                           (optional)
     * @param int $mixin Mixin number (ringsize - 1)                          (optional)
     * @param int $ring_size
     * @param int $unlock_time UNIX time or block height to unlock output           (optional)
     * @param bool $get_tx_keys
     * @param string|TransferAmount $below_amount Only send outputs below this amount. String in Monero format.                  (optional)
     * @param boolean $do_not_relay Do not relay transaction                             (optional)
     *
     *   OR
     * @param bool $get_tx_hex
     * @param bool $get_tx_metadata
     * @return array Example: {
     *   "amount": "1000000000000",
     *   "fee": "1000020000",
     *   "tx_hash": "c60a64ddae46154a75af65544f73a7064911289a7760be8fb5390cb57c06f2db",
     *   "tx_key": "805abdb3882d9440b6c80490c2d6b95a79dbc6d1b05e514131a91768e8040b04"
     * }
     * @throws Exception
     */
    public function sweep_all(string $address, int $account_index = 0, array $subaddr_indices = [], int $priority = 2, int $mixin = 10, int $ring_size = 11, int $unlock_time = 0,
                              bool $get_tx_keys = true, string|TransferAmount $below_amount = '0', bool $do_not_relay = false, bool $get_tx_hex = false, bool $get_tx_metadata = false): array
    {
        //TODO: as array
        $params = array('address' => $address,
            'account_index' => $account_index,
            'subaddr_indices' => $subaddr_indices,
            'priority' => $priority,
            'mixin' => $mixin,
            'ring_size' => $ring_size,
            'unlock_time' => $unlock_time,
            'get_tx_keys' => $get_tx_keys,
            'below_amount' => ($below_amount instanceof TransferAmount) ? $below_amount->getAsPiconero() : TransferAmount::transformToPiconero($below_amount),
            'do_not_relay' => $do_not_relay,
            'get_tx_hex' => $get_tx_hex,
            'get_tx_metadata' => $get_tx_metadata);
        $sweep_all_method = $this->_run('sweep_all', $params);

        $this->store(); // Save wallet state after transfer

        return $sweep_all_method;
    }

    /**
     *
     * Sweep a single key image to an address
     *
     * @param string $address Address to receive funds
     * @param string $key_image Key image to sweep
     * @param int $account_index
     * @param array $subaddr_indices
     * @param int $priority Payment ID                                  (optional)
     * @param int $mixin Mixin number (ringsize - 1)                 (optional)
     * @param int $ring_size
     * @param int $unlock_time UNIX time or block height to unlock output  (optional)
     * @param bool $get_tx_keys
     * @param string|TransferAmount $below_amount Only send outputs below this amount         (optional)
     * @param boolean $do_not_relay Do not relay transaction                    (optional)
     *
     *   OR
     * @param bool $get_tx_hex
     * @param bool $get_tx_metadata
     * @return array Example: {
     *   "amount": "1000000000000",
     *   "fee": "1000020000",
     *   "tx_hash": "c60a64ddae46154a75af65544f73a7064911289a7760be8fb5390cb57c06f2db",
     *   "tx_key": "805abdb3882d9440b6c80490c2d6b95a79dbc6d1b05e514131a91768e8040b04"
     * }
     * @throws Exception
     */
    public function sweep_single(string $address, string $key_image, int $account_index = 0, array $subaddr_indices = [], int $priority = 2, int $mixin = 10, int $ring_size = 11, int $unlock_time = 0,
                                 bool $get_tx_keys = true, string|TransferAmount $below_amount = '0', bool $do_not_relay = false, bool $get_tx_hex = false, bool $get_tx_metadata = false): array
    {
        //TODO as array

        $params = array('address' => $address,
            'key_image' => $key_image,
            'account_index' => $account_index,
            'subaddr_indices' => $subaddr_indices,
            'priority' => $priority,
            'mixin' => $mixin,
            'ring_size' => $ring_size,
            'unlock_time' => $unlock_time,
            'get_tx_keys' => $get_tx_keys,
            'below_amount' => ($below_amount instanceof TransferAmount) ? $below_amount->getAsPiconero() : TransferAmount::transformToPiconero($below_amount),
            'do_not_relay' => $do_not_relay,
            'get_tx_hex' => $get_tx_hex,
            'get_tx_metadata' => $get_tx_metadata);
        $sweep_single_method = $this->_run('sweep_single', $params);

        $this->store(); // Save wallet state after transfer

        return $sweep_single_method;
    }

    /**
     *
     * Relay a transaction
     *
     * @param string $hex Blob of transaction to relay
     *
     * @return array  // TODO example
     *
     */
    public function relay_tx(string $hex): array
    {
        $params = array('hex' => $hex);
        $relay_tx_method = $this->_run('relay_tx', $params);

        $this->store(); // Save wallet state after transaction relay

        return $relay_tx_method;
    }

    /**
     *
     * Save wallet state
     *
     * @return array  Example:
     *
     */
    public function store(): array
    {
        return $this->_run('store');
    }

    /**
     * @param string $payment_id Payment ID to look up
     *
     * @return array  Example: {
     *   "payments": [{
     *     "amount": 10350000000000,
     *     "block_height": 994327,
     *     "payment_id": "4279257e0a20608e25dba8744949c9e1caff4fcdafc7d5362ecf14225f3d9030",
     *     "tx_hash": "c391089f5b1b02067acc15294e3629a463412af1f1ed0f354113dd4467e4f6c1",
     *     "unlock_time": 0
     *   }]
     * }
     *
     * Look up incoming payments by payment ID
     *
     */
    public function get_payments(string $payment_id): array
    {
        // $params = array('payment_id' => $payment_id); // does not work
        $params = [];
        $params['payment_id'] = $payment_id;
        return $this->_run('get_payments', $params);
    }

    /**
     *
     * Look up incoming payments by payment ID (or a list of payments IDs) from a given height
     *
     * @param array $payment_ids Array of payment IDs to look up
     * @param string $min_block_height Height to begin search
     *
     * @return array Example: {
     *   "payments": [{
     *     "amount": 10350000000000,
     *     "block_height": 994327,
     *     "payment_id": "4279257e0a20608e25dba8744949c9e1caff4fcdafc7d5362ecf14225f3d9030",
     *     "tx_hash": "c391089f5b1b02067acc15294e3629a463412af1f1ed0f354113dd4467e4f6c1",
     *     "unlock_time": 0
     *   }]
     * }
     * @throws Exception
     */
    public function get_bulk_payments(array $payment_ids, string $min_block_height): array
    {
        // $params = array('payment_ids' => $payment_ids, 'min_block_height' => $min_block_height); // does not work
        //$params = array('min_block_height' => $min_block_height); // does not work
        $params = [];
        if (!is_array($payment_ids)) {
            throw new Exception('Error: Payment IDs must be array.');
        }
        if ($payment_ids) {
            $params['payment_ids'] = [];
            foreach ($payment_ids as $payment_id) {
                $params['payment_ids'][] = $payment_id;
            }
        }
        return $this->_run('get_bulk_payments', $params);
    }

    /**
     *
     * Look up incoming transfers
     *
     * @param string $type Type of transfer to look up; must be 'all', 'available', or 'unavailable' (incoming transfers which have already been spent)
     * @param int $account_index Index of account to look up                                                                                                   (optional)
     * @param array $subaddr_indices array of unsigned int; (Optional) Return transfers sent to these subaddresses.
     *
     * @return array Example: {
     *   "transfers": [{
     *     "amount": 10000000000000,
     *     "global_index": 711506,
     *     "spent": false,
     *     "tx_hash": "c391089f5b1b02067acc15294e3629a463412af1f1ed0f354113dd4467e4f6c1",
     *     "tx_size": 5870
     *   },{
     *     "amount": 300000000000,
     *     "global_index": 794232,
     *     "spent": false,
     *     "tx_hash": "c391089f5b1b02067acc15294e3629a463412af1f1ed0f354113dd4467e4f6c1",
     *     "tx_size": 5870
     *   },{
     *     "amount": 50000000000,
     *     "global_index": 213659,
     *     "spent": false,
     *     "tx_hash": "c391089f5b1b02067acc15294e3629a463412af1f1ed0f354113dd4467e4f6c1",
     *     "tx_size": 5870
     *   }]
     * }
     */
    public function incoming_transfers(string $type = 'all', int $account_index = 0, array $subaddr_indices = []): array
    {
        $params = array('transfer_type' => $type, 'account_index' => $account_index, 'subaddr_indices' => $subaddr_indices);
        return $this->_run('incoming_transfers', $params);
    }

    /**
     *
     * Look up a wallet key
     *
     * @param string $key_type Type of key to look up; must be 'view_key', 'spend_key', or 'mnemonic'
     *
     * @return array Example: {
     *   "key": "7e341d..."
     * }
     */
    public function query_key(string $key_type): array
    {
        $params = array('key_type' => $key_type);
        return $this->_run('query_key', $params);
    }

    /**
     *
     * Look up wallet view key
     *
     * @return array  Example: {
     *   "key": "7e341d..."
     * }
     *
     */
    public function view_key(): array
    {
        return $this->query_key("view_key");
    }

    /**
     *
     * Look up wallet spend key
     *
     * @return array Example: {
     *   "key": "2ab810..."
     * }
     */
    public function spend_key(): array
    {
        return $this->query_key("spend_key");
    }

    /**
     *
     * Look up wallet mnemonic seed
     *
     * @return array  Example: {
     *   "key": "2ab810..."
     * }
     *
     */
    public function mnemonic(): array
    {
        return $this->query_key("mnemonic");
    }

    /**
     *
     * Create an integrated address from a given payment ID
     *
     * @param string|null $standard_address
     * @param string|null $payment_id Payment ID  (optional)
     *
     * @return array Example: {
     * "integrated_address": "5F38Rw9HKeaLQGJSPtbYDacR7dz8RBFnsfAKMaMuwUNYX6aQbBcovzDPyrQF9KXF9tVU6Xk3K8no1BywnJX6GvZXCkbHUXdPHyiUeRyokn",
     * "payment_id": "420fa29b2d9a49f5"
     * }
     */
    public function make_integrated_address(null|string $standard_address, null|string $payment_id): array
    {
        $params = array('standard_address' => $standard_address, 'payment_id' => $payment_id);
        return $this->_run('make_integrated_address', $params);
    }

    /**
     *
     * Look up the wallet address and payment ID corresponding to an integrated address
     *
     * @param string $integrated_address Integrated address to split
     *
     * @return array Example: {
     * "is_subaddress": false,
     * "payment_id": "420fa29b2d9a49f5",
     * "standard_address": "55LTR8KniP4LQGJSPtbYDacR7dz8RBFnsfAKMaMuwUNYX6aQbBcovzDPyrQF9KXF9tVU6Xk3K8no1BywnJX6GvZX8yJsXvt"
     * }
     */
    public function split_integrated_address(string $integrated_address): array
    {
        $params = array('integrated_address' => $integrated_address);
        return $this->_run('split_integrated_address', $params);
    }

    /**
     *
     * Stop the wallet, saving the state
     *
     */
    public function stop_wallet(): array
    {
        return $this->_run('stop_wallet');
    }

    /*
     *
     * Rescan the blockchain from scratch
     *
    */

    public function rescan_blockchain(): array
    {
        return $this->_run('rescan_blockchain');
    }

    /**
     *
     * Add notes to transactions
     *
     * @param array $txids Array of string transaction IDs to note
     * @param array $notes Array of notes (strings) to add
     *
     */
    public function set_tx_notes(array $txids, array $notes): array
    {
        $params = array('txids' => $txids, 'notes' => $notes);
        return $this->_run('set_tx_notes', $params);
    }

    /**
     *
     * Look up transaction note
     *
     * @param array $txids Array of transaction IDs (strings) to look up
     *
     * @return array Example: {
     * "notes": ["This is an example"]
     * }
     */
    public function get_tx_notes(array $txids): array
    {
        $params = array('txids' => $txids);
        return $this->_run('get_tx_notes', $params);
    }

    /**
     *
     * Set a wallet option
     *
     * @param string $key Option to set
     * @param string $value Value to set
     *
     */
    public function set_attribute(string $key, string $value): array
    {
        $params = array('key' => $key, 'value' => $value);
        return $this->_run('set_attribute', $params);
    }

    /**
     *
     * Look up a wallet option
     *
     * @param string $key Wallet option to query
     *
     * @return array  Example: {
     * "value": "my_value"
     * }
     *
     */
    public function get_attribute(string $key): array
    {
        $params = array('key' => $key);
        return $this->_run('get_attribute', $params);
    }

    /**
     *
     * Look up a transaction key
     *
     * @param string $txid Transaction ID to look up
     *
     * @return array Example: {
     *   "tx_key": "e8e97866b1606bd87178eada8f995bf96d2af3fec5db0bc570a451ab1d589b0f"
     * }
     */
    public function get_tx_key(string $txid): array
    {
        $params = array('txid' => $txid);
        return $this->_run('get_tx_key', $params);
    }

    /**
     *
     * Check a transaction in the blockchain with its secret key.
     *
     * @param string $address Destination public address of the transaction.
     * @param string $txid Transaction ID
     * @param string $tx_key Transaction key
     *
     * @return array Example: {
     *   "confirmations": 1,
     *   "in_pool": false,
     *   "received": 1000000000000
     * }
     */
    public function check_tx_key(string $address, string $txid, string $tx_key): array
    {
        $params = array('address' => $address, 'txid' => $txid, 'tx_key' => $tx_key);
        return $this->_run('check_tx_key', $params);
    }

    /**
     *
     * Create proof (signature) of transaction
     *
     * @param string $address Address that spent funds
     * @param string $txid Transaction ID
     *
     * @return array Example: {
     *   "signature": "InProofV1Lq4nejMXxMnAdnLeZhHe3FGCmFdnSvzVM1AiGcXjngTRi4hfHPcDL9D4th7KUuvF9ZHnzCDXysNBhfy7gFvUfSbQWiqWtzbs35yUSmtW8orRZzJpYKNjxtzfqGthy1U3puiF"
     * }
     */
    public function get_tx_proof(string $address, string $txid): array
    {
        $params = array('address' => $address, 'txid' => $txid);
        return $this->_run('get_tx_proof', $params);
    }

    /**
     *
     * Verify transaction proof
     *
     * @param string $address Address that spent funds
     * @param string $txid Transaction ID
     * @param string $signature Signature (tx_proof)
     *
     * @return array Example: {
     *   "confirmations": 2,
     *   "good": 1,
     *   "in_pool": ,
     *   "received": 15752471409492,
     * }
     */
    public function check_tx_proof(string $address, string $txid, string $signature): array
    {
        $params = array('address' => $address, 'txid' => $txid, 'signature' => $signature);
        return $this->_run('check_tx_proof', $params);
    }

    /**
     *
     * Create proof of a spend
     *
     * @param string $txid Transaction ID
     *
     * @return array  Example: {
     *   "signature": "SpendProofV1RnP6ywcDQHuQTBzXEMiHKbe5ErzRAjpUB1h4RUMfGPNv4bbR6V7EFyiYkCrURwbbrYWWxa6Kb38ZWWYTQhr2Y1cRHVoDBkK9GzBbikj6c8GWyKbu3RKi9hoYp2fA9zze7UEdeNrYrJ3tkoE6mkR3Lk5HP6X2ixnjhUTG65EzJgfCS4qZ85oGkd17UWgQo6fKRC2GRgisER8HiNwsqZdUTM313RmdUX7AYaTUNyhdhTinVLuaEw83L6hNHANb3aQds5CwdKCUQu4pkt5zn9K66z16QGDAXqL6ttHK6K9TmDHF17SGNQVPHzffENLGUf7MXqS3Pb6eijeYirFDxmisZc1n2mh6d5EW8ugyHGfNvbLEd2vjVPDk8zZYYr7NyJ8JjaHhDmDWeLYy27afXC5HyWgJH5nDyCBptoCxxDnyRuAnNddBnLsZZES399zJBYHkGb197ZJm85TV8SRC6cuYB4MdphsFdvSzygnjFtbAcZWHy62Py3QCTVhrwdUomAkeNByM8Ygc1cg245Se1V2XjaUyXuAFjj8nmDNoZG7VDxaD2GT9dXDaPd5dimCpbeDJEVoJXkeEFsZF85WwNcd67D4s5dWySFyS8RbsEnNA5UmoF3wUstZ2TtsUhiaeXmPwjNvnyLif3ASBmFTDDu2ZEsShLdddiydJcsYFJUrN8L37dyxENJN41RnmEf1FaszBHYW1HW13bUfiSrQ9sLLtqcawHAbZWnq4ZQLkCuomHaXTRNfg63hWzMjdNrQ2wrETxyXEwSRaodLmSVBn5wTFVzJe5LfSFHMx1FY1xf8kgXVGafGcijY2hg1yw8ru9wvyba9kdr16Lxfip5RJGFkiBDANqZCBkgYcKUcTaRc1aSwHEJ5m8umpFwEY2JtakvNMnShjURRA3yr7GDHKkCRTSzguYEgiFXdEiq55d6BXDfMaKNTNZzTdJXYZ9A2j6G9gRXksYKAVSDgfWVpM5FaZNRANvaJRguQyqWRRZ1gQdHgN4DqmQ589GPmStrdfoGEhk1LnfDZVwkhvDoYfiLwk9Z2JvZ4ZF4TojUupFQyvsUb5VPz2KNSzFi5wYp1pqGHKv7psYCCodWdte1waaWgKxDken44AB4k6wg2V8y1vG7Nd4hrfkvV4Y6YBhn6i45jdiQddEo5Hj2866MWNsdpmbuith7gmTmfat77Dh68GrRukSWKetPBLw7Soh2PygGU5zWEtgaX5g79FdGZg"
     * }
     *
     */
    public function get_spend_proof(string $txid, null|string $message = null): array
    {
        $params = array('txid' => $txid);
        if ($message !== null) {
            $params['message'] = $message;
        }
        return $this->_run('get_spend_proof', $params);
    }

    /**
     *
     * Verify spend proof
     *
     * @param string $txid Transaction ID
     * @param string $signature Spend proof to verify
     * @param string|null $message
     * @return array Example: {
     *   "good": true
     * }
     */
    public function check_spend_proof(string $txid, string $signature, null|string $message = null): array
    {
        $params = array('txid' => $txid, 'signature' => $signature);
        if ($message !== null) {
            $params['message'] = $message;
        }
        return $this->_run('check_spend_proof', $params);
    }

    /**
     * TODO: more parameters
     * Create proof of reserves
     *
     * @param string $account_index Comma-separated list of account indices of which to prove reserves (proves reserve of all accounts if empty)  (optional)
     *
     * @return array Example: {
     *   "signature": "ReserveProofV11BZ23sBt9sZJeGccf84mzyAmNCP3KzYbE111111111111AjsVgKzau88VxXVGACbYgPVrDGC84vBU61Gmm2eiYxdZULAE4yzBxT1D9epWgCT7qiHFvFMbdChf3CpR2YsZj8CEhp8qDbitsfdy7iBdK6d5pPUiMEwCNsCGDp8AiAc6sLRiuTsLEJcfPYEKe"
     * }
     *
     */
    public function get_reserve_proof(string $account_index = 'all'): array
    {
        if ($account_index === 'all') {
            $params = array('all' => true);
        } else {
            $params = array('account_index' => $account_index);
        }

        return $this->_run('get_reserve_proof');
    }

    /**
     *TODO: more parameters
     *
     * Verify a reserve proof
     *
     * @param string $address Wallet address
     * @param string $signature Reserve proof
     *
     * @return array Example: {
     *   "good": true,
     *   "spent": 0,
     *   "total": 100000000000
     * }
     */
    public function check_reserve_proof(string $address, string $signature): array
    {
        $params = array('address' => $address, 'signature' => $signature);
        return $this->_run('check_reserve_proof', $params);
    }

    /**
     *
     * Look up transfers
     *
     * @param array $input_types Array of transfer type strings; possible values include 'all', 'in', 'out', 'pending', 'failed', and 'pool'  (optional)
     * @param int $account_index Index of account to look up                                                                                  (optional)
     * @param string $subaddr_indices Comma-separated list of subaddress indices to look up                                                        (optional)
     * @param int $min_height Minimum block height to use when looking up transfers                                                        (optional)
     * @param int $max_height Maximum block height to use when looking up transfers                                                        (optional)
     *
     *   OR
     *
     * @return array  Example: {
     *   "pool": [{
     *     "amount": 500000000000,
     *     "fee": 0,
     *     "height": 0,
     *     "note": "",
     *     "payment_id": "758d9b225fda7b7f",
     *     "timestamp": 1488312467,
     *     "txid": "da7301d5423efa09fabacb720002e978d114ff2db6a1546f8b820644a1b96208",
     *     "type": "pool"
     *   }]
     * }
     */
    public function get_transfers(array $input_types = ['all'], int $account_index = 0, string $subaddr_indices = '', int $min_height = 0, int $max_height = 4206931337): array
    {

        $params = array('account_index' => $account_index,
            'subaddr_indices' => $subaddr_indices,
            'min_height' => $min_height,
            'max_height' => $max_height);

        if (array_key_exists('all', $input_types)) {
            $params['in'] = true;
            $params['out'] = true;
            $params['pending'] = true;
            $params['failed'] = true;
            $params['pool'] = true;
        } else {
            foreach ($input_types as $iValue) {
                $params[$iValue] = true;
            }
        }

        if ($min_height > 0 || $max_height !== 4206931337) {
            $params['filter_by_height'] = true;
        }

        return $this->_run('get_transfers', $params);
    }

    /**
     *
     * Look up transaction by transaction ID
     *
     * @param string $txid Transaction ID to look up
     * @param int $account_index Index of account to query  (optional)
     *
     * @return array Example: {
     *   "transfer": {
     *     "amount": 10000000000000,
     *     "fee": 0,
     *     "height": 1316388,
     *     "note": "",
     *     "payment_id": "0000000000000000",
     *     "timestamp": 1495539310,
     *     "txid": "f2d33ba969a09941c6671e6dfe7e9456e5f686eca72c1a94a3e63ac6d7f27baf",
     *     "type": "in"
     *   }
     * }
     */
    public function get_transfer_by_txid(string $txid, int $account_index = 0): array
    {
        $params = array('txid' => $txid, 'account_index' => $account_index);
        return $this->_run('get_transfer_by_txid', $params);
    }

    /**
     *
     * Obtain information (destination, amount) about a transfer
     *
     * @param string $txinfo
     * @return array
     */
    public function describe_transfer(string $txinfo): array
    {
        $params = array(
            'multisig_txset' => $txinfo,
        );
        return $this->_run('describe_transfer', $params);
    }

    /**
     *
     * Sign a string
     *
     * @param string $data Data to sign
     *
     * @return array Example: {
     *   "signature": "SigV1Xp61ZkGguxSCHpkYEVw9eaWfRfSoAf36PCsSCApx4DUrKWHEqM9CdNwjeuhJii6LHDVDFxvTPijFsj3L8NDQp1TV"
     * }
     */
    public function sign(string $data): array
    {
        $params = array('string' => $data);
        return $this->_run('sign', $params);
    }

    /**
     *
     * Verify a signature
     *
     * @param string $data Signed data
     * @param string $address Address that signed data
     * @param string $signature Signature to verify
     *
     * @return array Example: {
     *   "good": true
     * }
     */
    public function verify(string $data, string $address, string $signature): array
    {
        $params = array('data' => $data, 'address' => $address, 'signature' => $signature);
        return $this->_run('verify', $params);
    }

    /**
     * Export all outputs in hex format
     *
     * @return array Example: {
     * "outputs_data_hex": "...outputs..."
     * }
     */
    public function export_outputs(): array
    {
        return $this->_run('export_outputs');
    }

    /**
     *
     * Import outputs in hex format
     *
     * @param string outputs_data_hex wallet outputs in hex format
     *
     * @return array Example: {
     * "num_imported": 6400
     * }
     */
    public function import_outputs(string $outputs_data_hex): array
    {
        $params = array(
            'outputs_data_hex' => $outputs_data_hex,
        );
        return $this->_run('import_outputs', $params);
    }

    /**
     *
     * Export an array of signed key images
     *
     * @return array  Example: {
     *   // TODO example
     * }
     *
     */
    public function export_key_images(): array
    {
        return $this->_run('export_key_images');
    }

    /**
     *
     * Import a signed set of key images
     *
     * @param array $signed_key_images Array of signed key images
     *
     * @return array Example: {
     *   // TODO example
     *   height: ,
     *   spent: ,
     *   unspent:
     * }
     */
    public function import_key_images(array $signed_key_images): array
    {
        $params = array('signed_key_images' => $signed_key_images);
        return $this->_run('import_key_images', $params);
    }

    /**
     *
     * Create a payment URI using the official URI specification
     *
     * @param string $address Address to receive funds
     * @param string|TransferAmount $amount Amount of monero as Piconero to request or TransferAmount object
     * @param string|null $payment_id Payment ID                   (optional)
     * @param string|null $recipient_name Name of recipient            (optional)
     * @param string|null $tx_description Payment description          (optional)
     *
     * @return array Example: {
     * "uri": "monero:55LTR8KniP4LQGJSPtbYDacR7dz8RBFnsfAKMaMuwUNYX6aQbBcovzDPyrQF9KXF9tVU6Xk3K8no1BywnJX6GvZX8yJsXvt?tx_payment_id=420fa29b2d9a49f5&tx_amount=0.000000000010&recipient_name=el00ruobuob%20Stagenet%20wallet&tx_description=Testing%20out%20the%20make_uri%20function."
     */
    public function make_uri(string $address, string|TransferAmount $amount, string $payment_id = null, string $recipient_name = null, string $tx_description = null): array
    {
        $params = array(
            'address' => $address,
            'amount' => (($amount instanceof TransferAmount) ? $amount->getAsPiconero() : $amount),
            'payment_id' => $payment_id,
            'recipient_name' => $recipient_name,
            'tx_description' => $tx_description);
        return $this->_run('make_uri', $params);
    }

    /**
     *
     * Parse a payment URI
     *
     * @param string $uri Payment URI
     *
     * @return array Example: {
     *   "uri": {
     *     "address": "44AFFq5kSiGBoZ4NMDwYtN18obc8AemS33DBLWs3H7otXft3XjrpDtQGv7SqSsaBYBb98uNbr2VBBEt7f2wfn3RVGQBEP3A",
     *     "amount": 10,
     *     "payment_id": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
     *     "recipient_name": "Monero Project donation address",
     *     "tx_description": "Testing out the make_uri function"
     *   }
     * }
     */
    public function parse_uri(string $uri): array
    {
        $params = array('uri' => $uri);
        return $this->_run('parse_uri', $params);
    }

    /**
     *
     * Look up address book entries
     *
     * @param array $entries Array of address book entry indices to look up
     *
     * @return array Example: {
     * "entries": [{
     * "address": "77Vx9cs1VPicFndSVgYUvTdLCJEZw9h81hXLMYsjBCXSJfUehLa9TDW3Ffh45SQa7xb6dUs18mpNxfUhQGqfwXPSMrvKhVp",
     * "description": "Second account",
     * "index": 0,
     * "payment_id": "0000000000000000000000000000000000000000000000000000000000000000"
     * },{
     * "address": "78P16M3XmFRGcWFCcsgt1WcTntA1jzcq31seQX1Eg92j8VQ99NPivmdKam4J5CKNAD7KuNWcq5xUPgoWczChzdba5WLwQ4j",
     * "description": "Third account",
     * "index": 1,
     * "payment_id": "0000000000000000000000000000000000000000000000000000000000000000"
     * }]
     * }
     */
    public function get_address_book(array $entries): array
    {
        $params = array('entries' => $entries);
        return $this->_run('get_address_book', $params);
    }

    /**
     *
     * Add entry to the address book
     *
     * @param string $address Address to add to address book
     * @param string $payment_id Payment ID to use with address in address book  (optional)
     * @param string $description Description of address                          (optional)
     *
     * @return array Example: {
     * "index": 1
     * }
     */
    public function add_address_book(string $address, string $payment_id = '', string $description = ''): array
    {
        $params = array('address' => $address, 'payment_id' => $payment_id, 'description' => $description);
        return $this->_run('add_address_book', $params);
    }

    /**
     *
     * Edit an existing address book entry.
     * @param int $index Index
     * @param string $address Address to add to address book (optional)
     * @param string $payment_id Payment ID to use with address in address book  (optional)
     * @param string $description Description of address                          (optional)
     *
     * @return array Example: {
     * }
     */
    public function edit_address_book(int $index, string $address = '', string $payment_id = '', string $description = ''): array
    {
        $params = array('index' => $index, 'set_address' => ($address !== ''), 'address' => $address, 'set_payment_id' => ($payment_id !== ''), 'payment_id' => $payment_id, 'set_description' => ($description !== ''), 'description' => $description);
        return $this->_run('edit_address_book', $params);
    }

    /**
     *
     * Delete an entry from the address book
     *
     * @param int $index Index of the address book entry to remove
     *
     * @return array
     */
    public function delete_address_book(int $index): array
    {
        $params = array('index' => $index);
        return $this->_run('delete_address_book', $params);
    }

    /**
     *
     * Refresh the wallet after opening
     *
     * @param int|null $start_height Block height from which to start    (optional)
     *
     * @return array Example: {
     * "blocks_fetched": 24,
     * "received_money": true
     * }
     */
    public function refresh(int $start_height = null): array
    {
        $params = array('start_height' => $start_height);
        return $this->_run('refresh', $params);
    }

    /**
     *
     * Set whether and how often to automatically refresh the current wallet.
     *
     * @param bool $enable Enable or disable automatic refreshing (default = true)
     * @param int $period The period of the wallet refresh cycle (i.e. time between refreshes) in seconds
     * @return array Example: {
     * }
     */
    public function auto_refresh(bool $enable = true, int $period = 10): array
    {
        $params = array('enable' => $enable, 'period' => $period);
        return $this->_run('auto_refresh', $params);
    }

    /**
     *
     * Rescan the blockchain for spent outputs
     *
     */
    public function rescan_spent(): array
    {
        return $this->_run('rescan_spent');
    }

    /**
     *
     * Start mining
     *
     * @param int $threads_count Number of threads with which to mine
     * @param boolean $do_background_mining Mine in background?
     * @param boolean $ignore_battery Ignore battery?
     *
     * @return array
     */
    public function start_mining(int $threads_count, bool $do_background_mining, bool $ignore_battery): array
    {
        $params = array('threads_count' => $threads_count, 'do_background_mining' => $do_background_mining, 'ignore_battery' => $ignore_battery);
        return $this->_run('start_mining', $params);
    }

    /**
     *
     * Stop mining
     *
     *
     */
    public function stop_mining(): array
    {
        return $this->_run('stop_mining');
    }

    /**
     *
     * Look up a list of available languages for your wallet's seed
     *
     * @return array  Example: {
     * "languages": ["Deutsch","English","Español","Français","Italiano","Nederlands","Português","русский язык","日本語","简体中文 (中国)","Esperanto","Lojban"]
     * }
     *
     */
    public function get_languages(): array
    {
        return $this->_run('get_languages');
    }

    /**
     *
     * Create a new wallet
     *
     * @param string $filename Filename of new wallet to create
     * @param string|null $password Password of new wallet to create
     * @param string $language Language of new wallet to create
     *
     * @return array
     */
    public function create_wallet(string $filename = 'monero_wallet', string $password = null, string $language = 'English'): array
    {
        $params = array('filename' => $filename, 'password' => $password, 'language' => $language);
        return $this->_run('create_wallet', $params);
    }

    /**
     *
     * Create a wallet on the RPC server from an address, view key, and (optionally) spend key.
     *
     * @param string $filename
     * @param string $password
     * @param string $address
     * @param string $viewKey
     * @param string $spendKey
     * @param int $restoreHeight
     * @param bool $saveCurrent
     * @return array {"address":"42gt8cXJSHAL4up8XoZh7fikVuswDU7itAoaCjSQyo6fFoeTQpAcAwrQ1cs8KvFynLFSBdabhmk7HEe3HS7UsAz4LYnVPYM",
     * "info":"Wallet has been generated successfully."
     * }
     */
    public function generate_from_keys(string $filename, string $password, string $address, string $viewKey, string $spendKey = '', int $restoreHeight = 0, $saveCurrent = true): array
    {
        $params = array(
            'filename' => $filename,
            'password' => $password,
            'address' => $address,
            'viewkey' => $viewKey,
            'spendkey' => $spendKey,
            'restore_height' => $restoreHeight,
            'autosave_current' => $saveCurrent
        );
        return $this->_run('generate_from_keys', $params);
    }

    /**
     *
     * Open a wallet
     *
     * @param string $filename Filename of wallet to open
     * @param string|null $password Password of wallet to open
     *
     * @return array
     */
    public function open_wallet(string $filename = 'monero_wallet', string $password = null): array
    {
        $params = array('filename' => $filename, 'password' => $password);
        return $this->_run('open_wallet', $params);
    }

    /**
     *
     * Create and open a wallet on the RPC server from an existing mnemonic phrase and close the currently open wallet.
     *
     * @param string $name
     * @param string $password
     * @param string $seed
     * @param int $restoreHeight
     * @param string $language
     * @param string $seed_offset
     * @param bool $saveCurrent
     * @return array {
     * "address": "9wB1Jc5fy5hjTkFBnv4UNY3WfhUswhx8M7uWjZrwRBzH2uatJcn8AqiKEHWuSNrnapApCzzTxP4iSiV3y3pqYcRbDHNboJK",
     * "info": "Wallet has been restored successfully.",
     * "seed": "awkward vogue odometer amply bagpipe kisses poker aspire slug eluded hydrogen selfish later toolbox enigma wolf tweezers eluded gnome soprano ladder broken jukebox lordship aspire",
     * "was_deprecated": false
     * }
     */
    public function restore_deterministic_wallet(string $name, string $password, string $seed, int $restoreHeight = 0, string $language = 'English', string $seed_offset = '', $saveCurrent = true): array
    {
        $params = array(
            'name' => $name,
            'password' => $password,
            'seed' => $seed,
            'restore_height' => $restoreHeight,
            'language' => $language,
            'seed_offset' => $seed_offset,
            'autosave_current' => $saveCurrent
        );
        return $this->_run('restore_deterministic_wallet', $params);
    }

    /**
     * Close wallet
     */
    public function close_wallet(): array
    {
        return $this->_run('close_wallet');
    }

    /**
     * Change a wallet password
     *
     * @param string $old_password
     * @param string $new_password
     * @return array
     */
    public function change_wallet_password(string $old_password = '', string $new_password = ''): array
    {
        $params = array(
            'old_password' => $old_password,
            'new_password' => $new_password
        );
        return $this->_run('change_wallet_password', $params);
    }

    /**
     *
     * Check if wallet is multisig
     *
     * @return array Example: (non-multisignature wallet) {
     * "multisig": false,
     * "ready": false,
     * "threshold": 0,
     * "total": 0
     * }
     * Example for a multisig wallet: {
     * "multisig": true,
     * "ready": true,
     * "threshold": 2,
     * "total": 2
     * }
     */
    public function is_multisig(): array
    {
        return $this->_run('is_multisig');
    }

    /**
     *
     * Create information needed to create a multisignature wallet
     * Prepare a wallet for multisig by generating a multisig string to share with peers.
     *
     * @return array Example: {
     *   "multisig_info": "MultisigV1WBnkPKszceUBriuPZ6zoDsU6RYJuzQTiwUqE5gYSAD1yGTz85vqZGetawVvioaZB5cL86kYkVJmKbXvNrvEz7o5kibr7tHtenngGUSK4FgKbKhKSZxVXRYjMRKEdkcbwFBaSbsBZxJFFVYwLUrtGccSihta3F4GJfYzbPMveCFyT53oK"
     * }
     */
    public function prepare_multisig(): array
    {
        return $this->_run('prepare_multisig');
    }

    /**
     *
     * Create a multisignature wallet.
     * Make a wallet multisig by importing peers multisig string.
     *
     * @param array $multisig_info array of string; List of multisig string from peers. (from eg. prepare_multisig)
     * @param int $threshold Threshold required to spend from multisignature wallet. Amount of signatures needed to sign a transfer. Must be less or equal than the amount of signature in multisig_info.
     * @param string $password Passphrase to apply to multisignature wallet
     *
     * @return array Example for 2/2 Multisig Wallet: {
     * "address": "55SoZTKH7D39drxfgT62k8T4adVFjmDLUXnbzEKYf1MoYwnmTNKKaqGfxm4sqeKCHXQ5up7PVxrkoeRzXu83d8xYURouMod",
     * "multisig_info": ""
     * }
     *
     * Example for 2/3 Multisig Wallet: {
     * "address": "51sLpF8fWaK1111111111111111111111111111111111ABVbHNf1JFWJyFp5YZgZRQ44RiviJi1sPHgLVMbckRsDkTRgKS",
     * "multisig_info": "MultisigxV18jCaYAQQvzCMUJaAWMCaAbAoHpAD6WPmYDmLtBtazD654E8RWkLaGRf29fJ3stU471MELKxwufNYeigP7LoE4tn2Sscwn5g7PyCfcBc1V4ffRHY3Kxqq6VocSCUTncpVeUskaDKuTAWtdB9VTBGW7iG1cd7Zm1dYgur3CiemkGjRUAj9bL3xTEuyaKGYSDhtpFZFp99HQX57EawhiRHk3qq4hjWX"
     * }
     */
    public function make_multisig(array $multisig_info, int $threshold, string $password = ''): array
    {
        $params = array('multisig_info' => $multisig_info, 'threshold' => $threshold, 'password' => $password);
        return $this->_run('make_multisig', $params);
    }

    /**
     *
     * Export multisignature information
     *
     * @return array Example: {
     * "info": "4d6f6e65726f206d756c7469736967206578706f72740105cf6442b09b75f5eca9d846771fe1a879c9a97ab0553ffbcec64b1148eb7832b51e7898d7944c41cee000415c5a98f4f80dc0efdae379a98805bb6eacae743446f6f421cd03e129eb5b27d6e3b73eb6929201507c1ae706c1a9ecd26ac8601932415b0b6f49cbbfd712e47d01262c59980a8f9a8be776f2bf585f1477a6df63d6364614d941ecfdcb6e958a390eb9aa7c87f056673d73bc7c5f0ab1f74a682e902e48a3322c0413bb7f6fd67404f13fb8e313f70a0ce568c853206751a334ef490068d3c8ca0e"
     * }
     */
    public function export_multisig_info(): array
    {
        return $this->_run('export_multisig_info');
    }

    /**
     *
     * Import mutlisignature information
     *
     * @param array $info array of string; List of multisig info in hex format from other participants.
     *
     * @return array  Example: {
     * "n_outputs": 35
     * }
     */
    public function import_multisig_info(array $info): array
    {
        $params = array('info' => $info);
        return $this->_run('import_multisig_info', $params);
    }

    /**
     *
     * Finalize a multisignature wallet
     *
     * @param array $multisig_info Multisignature info (from eg. prepare_multisig)
     * @param string $password Multisignature info (from eg. prepare_multisig)
     *
     * @return array : {
     * "address": "5B9gZUTDuHTcGGuY3nL3t8K2tDnEHeRVHSBQgLZUTQxtFYVLnho5JJjWJyFp5YZgZRQ44RiviJi1sPHgLVMbckRsDqDx1gV"
     * }
     */
    public function finalize_multisig(array $multisig_info, string $password = ''): array
    {
        $params = array('multisig_info' => $multisig_info, 'password' => $password);
        return $this->_run('finalize_multisig', $params);
    }

    /**
     *
     * Sign a multisignature transaction
     *
     * @param string $tx_data_hex Blob of transaction to sign
     *
     * @return array Example: {
     * "tx_data_hex": "...multisig_txset...",
     * "tx_hash_list": ["4996091b61c1be112c1097fd5e97d8ff8b28f0e5e62e1137a8c831bacf034f2d"]
     * }
     */
    public function sign_multisig(string $tx_data_hex): array
    {
        $params = array('tx_data_hex' => $tx_data_hex);
        return $this->_run('sign_multisig', $params);
    }

    /**
     *
     * Submit (relay) a multisignature transaction
     *
     * @param string $tx_data_hex string; Multisig transaction in hex format, as returned by sign_multisig under tx_data_hex.
     *
     * @return array : {
     * "tx_hash_list": ["4996091b61c1be112c1097fd5e97d8ff8b28f0e5e62e1137a8c831bacf034f2d"]
     * }
     */
    public function submit_multisig(string $tx_data_hex): array
    {
        $params = array('tx_data_hex' => $tx_data_hex);
        return $this->_run('submit_multisig', $params);
    }




//    /**
//     *
//     * Exchange mutlisignature information
//     *
//     * @param password wallet password
//     * @param multisig_info info (from eg. prepare_multisig)
//     *
//     */
//    public function exchange_multisig_keys($password, $multisig_info)
//    {
//        $params = array(
//            'password' => $password,
//            'multisig_info' => $multisig_info
//        );
//        return $this->_run('exchange_multisig_keys', $params);
//    }


    /**
     * Get RPC version Major & Minor integer-format, where Major is the first 16 bits and Minor the last 16 bits.
     *
     * @return array Example: {
     * "version": 65539
     * }
     */
    public function get_version(): array
    {
        return $this->_run('get_version');
    }
}
