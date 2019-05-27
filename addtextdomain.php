<?php
/**
 * Console application, which adds textdomain argument
 * to all i18n function calls
 *
 * @package wordpress-i18n
 */
error_reporting( E_ALL );

class AddTextdomain {

	var $modified_contents = '';
	var $funcs;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$makepot       = array(
			'_'                    => array( 'string' ),
			'__'                   => array( 'string' ),
			'_e'                   => array( 'string' ),
			'_c'                   => array( 'string' ),
			'_n'                   => array( 'singular', 'plural' ),
			'_n_noop'              => array( 'singular', 'plural' ),
			'_nc'                  => array( 'singular', 'plural' ),
			'__ngettext'           => array( 'singular', 'plural' ),
			'__ngettext_noop'      => array( 'singular', 'plural' ),
			'_x'                   => array( 'string', 'context' ),
			'_ex'                  => array( 'string', 'context' ),
			'_nx'                  => array( 'singular', 'plural', null, 'context' ),
			'_nx_noop'             => array( 'singular', 'plural', 'context' ),
			'_n_js'                => array( 'singular', 'plural' ),
			'_nx_js'               => array( 'singular', 'plural', 'context' ),
			'esc_attr__'           => array( 'string' ),
			'esc_html__'           => array( 'string' ),
			'esc_attr_e'           => array( 'string' ),
			'esc_html_e'           => array( 'string' ),
			'esc_attr_x'           => array( 'string', 'context' ),
			'esc_html_x'           => array( 'string', 'context' ),
			'comments_number_link' => array( 'string', 'singular', 'plural' ),
		);
		$this->funcs   = array_keys( $makepot );
		$this->funcs[] = 'translate_nooped_plural';
	}

	/**
	 * Prints CLI usage.
	 */
	public function usage() {
		$usage = "Usage: php add-textdomain.php [-i] <domain> <file>\n\nAdds the string <domain> as a last argument to all i18n function calls in <file>\nand prints the modified php file on standard output.\n\nOptions:\n    -i    Modifies the PHP file in place, instead of printing it to standard output.\n";
		fwrite( STDERR, $usage );
		exit( 1 );
	}

	/**
	 * Adds textdomain to a single file.
	 *
	 * @param string $domain Text domain.
	 * @param string $source_filename Filename with optional path.
	 * @param bool   $inplace True to modifies the PHP file in place. False to print to standard output.
	 *
	 * @see AddTextdomain::process_string()
	 *
	 */
	public function process_file( $domain, $source_filename, $inplace ) {
		$new_source = $this->process_string( $domain, file_get_contents( $source_filename ) );

		if ( $inplace ) {
			$f = fopen( $source_filename, 'w' );
			fwrite( $f, $new_source );
			fclose( $f );
		} else {
			echo $new_source;
		}
	}

	/**
	 * Adds textdomain to a string of PHP.
	 *
	 * Functions calls should be wrapped in opening and closing PHP delimiters as usual.
	 *
	 * @param string $domain Text domain.
	 * @param string $string PHP code to parse.
	 *
	 * @return string Modified source.
	 * @see AddTextdomain::process_tokens()
	 *
	 */
	public function process_string( $domain, $string ) {
		$tokens = token_get_all( $string );
		return $this->process_tokens( $domain, $tokens );
	}

	/**
	 * Adds textdomain to a set of PHP tokens.
	 *
	 * @param string $domain Text domain.
	 * @param array  $tokens PHP tokens. An array of token identifiers. Each individual token identifier is either a
	 *                       single character (i.e.: ;, ., >, !, etc.), or a three element array containing the token
	 *                       index in element 0, the string content of the original token in element 1 and the line
	 *                       number in element 2.
	 *
	 * @return string Modified source.
	 */
	public function process_tokens( $domain, $tokens ) {
		$this->modified_contents = '';
		$domain                  = addslashes( $domain );

		$in_func        = false;
		$args_started   = false;
		$parens_balance = 0;
		$found_domain   = false;

		foreach ( $tokens as $index => $token ) {
			$string_success = false;
			if ( is_array( $token ) ) {
				list( $id, $text ) = $token;
				if ( T_STRING == $id && in_array( $text, $this->funcs ) ) {
					$in_func        = true;
					$parens_balance = 0;
					$args_started   = false;
					$found_domain   = false;
				} elseif ( T_CONSTANT_ENCAPSED_STRING == $id && ( "'$domain'" == $text || "\"$domain\"" == $text ) ) {
					if ( $in_func && $args_started ) {
						$found_domain = true;
					}
				}
				$token = $text;
			} elseif ( '(' == $token ) {
				$args_started = true;
				++$parens_balance;
			} elseif ( ')' == $token ) {
				--$parens_balance;
				if ( $in_func && 0 == $parens_balance ) {
					if ( ! $found_domain ) {
						$token = ", '$domain'";
						if ( T_WHITESPACE == $tokens[ $index - 1 ][0] ) {
							$token .= ' '; // Maintain code standards if previously present
							// Remove previous whitespace token to account for it.
							$this->modified_contents = trim( $this->modified_contents );
						}
						$token .= ')';
					}
					$in_func      = false;
					$args_started = false;
					$found_domain = false;
				}
			}
			$this->modified_contents .= $token;
		}

		return $this->modified_contents;
	}
}

// Run the CLI only if the file wasn't included.
$included_files = get_included_files();
if ( $included_files[0] == __FILE__ ) {
	$adddomain = new AddTextdomain();

	if ( ! isset( $argv[1] ) || ! isset( $argv[2] ) ) {
		$adddomain->usage();
	}

	$inplace = false;
	if ( '-i' == $argv[1] ) {
		$inplace = true;
		if ( ! isset( $argv[3] ) )
			$adddomain->usage();
		array_shift( $argv );
	}

	if ( is_dir( $argv[2] ) ) {
		$directory = new RecursiveDirectoryIterator( $argv[2], RecursiveDirectoryIterator::SKIP_DOTS );
		$files     = new RecursiveIteratorIterator( $directory );
		foreach ( $files as $file ) {
			if ( 'php' === $file->getExtension() ) {
				$adddomain->process_file( $argv[1], $file->getPathname(), $inplace );
			}
		}
	} else {
		$adddomain->process_file( $argv[1], $argv[2], $inplace );
	}
}
