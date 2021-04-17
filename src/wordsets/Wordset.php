<?php


namespace MoneroIntegrations\MoneroPhp\wordsets;

interface Wordset
{

    /* Returns name of wordset in the wordset's native language.
     * This is a human-readable string, and should be capitalized
     * if the language supports it.
     */
    public static function name() : string;

    /* Returns name of wordset in english.
     * This is a human-readable string, and should be capitalized
     */
    public static function english_name() : string;

    /* Returns integer indicating length of unique prefix,
     * such that each prefix of this length is unique across
     * the entire set of words.
     *
     * A value of 0 indicates that there is no unique prefix
     * and the entire word must be used instead.
     */
    public static function prefix_length() : int;

    /* Returns an array of all words in the wordset.
     */
    public static function words() : array;
}