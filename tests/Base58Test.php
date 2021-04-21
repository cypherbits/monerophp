<?php declare(strict_types=1);
/**
 * PHP Test for testing Tuupola\Base58
 * @author Cypherbits <info@avanix.es>
 * PHP8 porting and refactor
 */

use PHPUnit\Framework\TestCase;
use Tuupola\Base58;

class Base58Test extends TestCase
{
    private static string $textClean = "hello";
    private static string $textEncoded;


    public function testEncodeAndDecode(): void
    {
        $base58 = new Base58(["characters" => Base58::BITCOIN]);
        self::$textEncoded = $base58->encode(self::$textClean);

        $decoded = $base58->decode(self::$textEncoded);

        self::assertSame($decoded, self::$textClean);
    }
}
