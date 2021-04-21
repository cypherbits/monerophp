<?php declare(strict_types=1);
/**
 * PHP Test for testing the WalletRPC
 * @author Cypherbits <info@avanix.es>
 * PHP8 porting and refactor
 */

use MoneroIntegrations\MoneroPhp\WalletRPC;
use PHPUnit\Framework\TestCase;

class WalletRPCTest extends TestCase
{

    private static WalletRPC $walletRPC;

    public function testClose_wallet()
    {

    }

    public function testSweep_dust()
    {

    }

    public function testFinalize_multisig()
    {

    }

    public function testSign_multisig()
    {

    }

    public function testStop_mining()
    {

    }

    public function testCreate_wallet()
    {

    }

    public function testStore()
    {

    }

    public function testGet_tx_notes()
    {

    }

    public function testView_key()
    {

    }

    public function testGet_address()
    {

    }

    public function testEdit_address_book()
    {

    }

    public function testImport_multisig_info()
    {

    }

    public function testRescan_blockchain()
    {

    }

    public function testCheck_reserve_proof()
    {

    }

    public function test_print()
    {

    }

    public function testLabel_address()
    {

    }

    public function testStop_wallet()
    {

    }

    public function testSet_attribute()
    {

    }

    public function testGet_attribute()
    {

    }

    public function testAdd_address_book()
    {

    }

    public function testSet_tx_notes()
    {

    }

    public function testGetheight()
    {

    }

    public function testSplit_integrated_address()
    {

    }

    public function testIncoming_transfers()
    {

    }

    public function testGet_balance()
    {
        $balance = self::$walletRPC->get_balance();
        var_dump($balance);
        self::assertIsArray($balance, 'Balance is not an array');
    }

    public function testSubmit_multisig()
    {

    }

    public function testGenerate_from_keys()
    {

    }

    public function testGet_version()
    {

    }

    public function testGet_client()
    {

    }

    public function testExport_multisig_info()
    {

    }

    public function testGetbalance()
    {

    }

    public function testRescan_spent()
    {

    }

    public function testGetaddress()
    {

    }

    public function testLabel_account()
    {

    }

    public function testGet_tx_key()
    {

    }

    public function testGet_bulk_payments()
    {

    }

    public function testGet_account_tags()
    {

    }

    public function testSweep_all()
    {

    }

    public function testSweep_unmixable()
    {

    }

    public function testSpend_key()
    {

    }

    public function testGet_height()
    {

    }

    public function testMake_integrated_address()
    {

    }

    public function testMake_uri()
    {

    }

    public function testCheck_spend_proof()
    {

    }

    public function testExport_outputs()
    {

    }

    public function testTransfer()
    {

    }

    public function testGet_spend_proof()
    {

    }

    public function testVerify()
    {

    }

    public function testRelay_tx()
    {

    }

    public function testMnemonic()
    {

    }

    public function testGet_languages()
    {

    }

    public function testOpen_wallet()
    {
        $response = self::$walletRPC->open_wallet('mytestwallet', 'mytestpassword');
        self::assertIsArray($response, 'Response is not an array');
    }

    public function testRestore_deterministic_wallet()
    {

    }

    public function testValidate_address()
    {

    }

    public function testCheck_tx_key()
    {

    }

    public function testMake_multisig()
    {

    }

    public function testTransfer_split()
    {

    }

    public function testChange_wallet_password()
    {

    }

    public function testCreate_address()
    {

    }

    public function testImport_key_images()
    {

    }

    public function testCreate_account()
    {

    }

    public function testSweep_single()
    {

    }

    public function testStart_mining()
    {

    }

    public function testExport_key_images()
    {

    }

    public function testSign()
    {

    }

    public function testImport_outputs()
    {

    }

    public function testDelete_address_book()
    {

    }

    public function testGet_transfers()
    {

    }

    public function testGet_transfer_by_txid()
    {

    }

    public function testGet_tx_proof()
    {

    }

    public function testGet_address_index()
    {

    }

    public function testGet_payments()
    {

    }

    public function testPrepare_multisig()
    {

    }

    public function testDescribe_transfer()
    {

    }

    public function testGet_reserve_proof()
    {

    }

    public function testParse_uri()
    {

    }

    public function testGetClient()
    {

    }

    public function testQuery_key()
    {

    }

    public function testAuto_refresh()
    {

    }

    public function testGet_accounts()
    {

    }

    public function testIs_multisig()
    {

    }

    public function testSet_account_tag_description()
    {

    }

    public function testGet_address_book()
    {

    }

    public function testCheck_tx_proof()
    {

    }

    public function test__construct()
    {
        self::$walletRPC = new WalletRPC('127.0.0.1', 8181, false, 'admin', 'admin');
        self::assertNotNull(self::$walletRPC);
    }

    public function testRefresh()
    {

    }

    public function testUntag_accounts()
    {

    }

    public function testTag_accounts()
    {

    }
}
