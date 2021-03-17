<?php


namespace MoneroIntegrations\MoneroPhp;

interface Wordset
{

    /* Returns name of wordset in the wordset's native language.
     * This is a human-readable string, and should be capitalized
     * if the language supports it.
     */
    static public function name() : string;

    /* Returns name of wordset in english.
     * This is a human-readable string, and should be capitalized
     */
    static public function english_name() : string;

    /* Returns integer indicating length of unique prefix,
     * such that each prefix of this length is unique across
     * the entire set of words.
     *
     * A value of 0 indicates that there is no unique prefix
     * and the entire word must be used instead.
     */
    static public function prefix_length() : int;

    /* Returns an array of all words in the wordset.
     */
    static public function words() : array;
}