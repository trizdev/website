<?php

namespace SmartCrawl;

class SmartCrawl_String {

	/**
	 * @var string
	 */
	private $string;

	/**
	 * @var int
	 */
	private $syllable_count;

	/**
	 * @var Syllable
	 */
	private $syllable_helper;

	/**
	 * @var string[]
	 */
	private $sentences_with_punctuation;

	/**
	 * @var string[]
	 */
	private $sentences;

	/**
	 * @var string[]
	 */
	private $words;

	/**
	 * @var string[]
	 */
	private $paragraphs;

	/**
	 * @var string[]
	 */
	private $keywords;

	/**
	 * @var string
	 */
	private $language_code;

	/**
	 * @var string[]
	 */
	private $language_stopwords;

	/**
	 * @param string $string        The string to analyse.
	 * @param string $language_code Language code.
	 */
	public function __construct( $string, $language_code = 'en' ) {
		$this->string = $string;

		$this->syllable_helper = new Syllable( $language_code );
		$this->language_code   = $language_code;
	}

	public function uppercase() {
		return String_Utils::uppercase( $this->string );
	}

	public function substr( $start = 0, $length = null ) {
		return String_Utils::substr( $this->string, $start, $length );
	}

	public function length() {
		return String_Utils::len( $this->string );
	}

	public function pos( $needle, $offset = 0 ) {
		return String_Utils::pos( $this->string, $needle, $offset );
	}

	public function get_words() {
		if ( is_null( $this->words ) ) {
			$this->words = String_Utils::words( $this->string );
		}

		return $this->words;
	}

	public function get_sentences() {
		if ( is_null( $this->sentences ) ) {
			$this->sentences = String_Utils::sentences( $this->string, false );
		}

		return $this->sentences;
	}

	public function get_sentences_with_punctuation() {
		if ( is_null( $this->sentences_with_punctuation ) ) {
			$this->sentences_with_punctuation = String_Utils::sentences( $this->string, true );
		}

		return $this->sentences_with_punctuation;
	}

	public function get_paragraphs() {
		if ( is_null( $this->paragraphs ) ) {
			$this->paragraphs = String_Utils::paragraphs( $this->string );
		}

		return $this->paragraphs;
	}

	public function lowercase() {
		return String_Utils::lowercase( $this->string );
	}

	public function starts_with( $needle ) {
		return String_Utils::starts_with( $this->string, $needle );
	}

	public function ends_with( $needle ) {
		return String_Utils::ends_with( $this->string, $needle );
	}

	public function has_stopwords() {
		$has   = false;
		$stops = $this->get_language_stopwords();
		$words = $this->get_words();

		foreach ( $words as $word ) {
			if ( ! in_array( $word, $stops, true ) ) {
				continue;
			}
			$has = true;
			break;
		}

		return $has;
	}

	public function get_language_stopwords() {
		if ( is_null( $this->language_stopwords ) ) {
			$this->language_stopwords = $this->import_language_stopwords();
		}

		return $this->language_stopwords;
	}

	private function import_language_stopwords() {
		if ( empty( $this->language_code ) ) {
			return array();
		}

		$stop_words_file = sprintf(
			SMARTCRAWL_PLUGIN_DIR . 'core/resources/stop-words/%s.php',
			$this->language_code
		);

		if ( ! file_exists( $stop_words_file ) ) {
			return array();
		}

		return include $stop_words_file;
	}

	public function get_keywords( $limit = false ) {
		if ( is_null( $this->keywords ) ) {
			$this->keywords = $this->find_keywords( $this->string );
		}

		$keywords = $this->keywords;

		return ! empty( $limit )
			? array_slice( $keywords, 0, $limit )
			: $keywords;
	}

	private function find_keywords( $string ) {
		$keywords = array();
		if ( empty( $string ) ) {
			return $keywords;
		}

		$words = String_Utils::words( $string );
		if ( empty( $words ) ) {
			return $keywords;
		}

		$stopwords = $this->get_language_stopwords();

		foreach ( $words as $word ) {
			if ( in_array( $word, $stopwords, true ) ) {
				continue;
			}
			if ( empty( $keywords[ $word ] ) ) {
				$keywords[ $word ] = 0;
			}
			++$keywords[ $word ];
		}
		arsort( $keywords );

		return $keywords;
	}

	public function get_sentence_count() {
		return count( $this->get_sentences() );
	}

	public function get_word_count() {
		return count( $this->get_words() );
	}

	public function get_syllable_count() {
		if ( is_null( $this->syllable_count ) ) {
			$this->syllable_count = $this->syllable_helper->count_syllables( $this->string );
		}

		return $this->syllable_count;
	}
}