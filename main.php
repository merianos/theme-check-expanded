<?php
function should_excluded( $theme = '', $file = '' ) {
	$root    = $theme . '/';
	$exclude = [];
	if ( isset( $_COOKIE['exclude_paths'] ) ) {
		$exclude = explode( "\n", base64_decode( $_COOKIE['exclude_paths'] ) );
		$exclude = array_map(
			function ( $item = '' ) use ( $root ) {
				return trim( $root ) . trim( $item );
			},
			$exclude
		);
	}

	$should_excluded = false;

	foreach ( $exclude as $path ) {
		if ( false !== strpos( $file, $path ) ) {
			$should_excluded = true;
			break;
		}
	}

	return $should_excluded;
}

function check_main( $theme ) {
	global $themechecks, $data, $themename;
	$themename = $theme;
	$theme     = get_theme_root( $theme ) . "/$theme";
	$files     = listdir( $theme );
	$data      = tc_get_theme_data( $theme . '/style.css' );
	if ( $data['Template'] ) {
		// This is a child theme, so we need to pull files from the parent, which HAS to be installed.
		$parent = get_theme_root( $data['Template'] ) . '/' . $data['Template'];
		if ( ! tc_get_theme_data( $parent . '/style.css' ) ) { // This should never happen but we will check while were here!
			echo '<h2>' . sprintf( __( 'Parent theme %1$s not found! You have to have parent AND child-theme installed!', 'theme-check-extended' ), '<strong>' . $data['Template'] . '</strong>' ) . '</h2>';

			return;
		}
		$parent_data = tc_get_theme_data( $parent . '/style.css' );
		$themename   = basename( $parent );
		$files       = array_merge( listdir( $parent ), $files );
	}

	if ( $files ) {
		foreach ( $files as $key => $filename ) {
			if ( should_excluded( $theme, $filename ) ) {
				continue;
			}

			if ( substr( $filename, - 4 ) == '.php' && ! is_dir( $filename ) ) {
				$php[ $filename ] = file_get_contents( $filename );
				$php[ $filename ] = tc_strip_comments( $php[ $filename ] );
			} else if ( substr( $filename, - 4 ) == '.css' && ! is_dir( $filename ) ) {
				$css[ $filename ] = file_get_contents( $filename );
			} else {
				$other[ $filename ] = ( ! is_dir( $filename ) ) ? file_get_contents( $filename ) : '';
			}
		}

		// run the checks
		$success = run_themechecks( $php, $css, $other );

		global $checkcount;

		// second loop, to display the errors
		echo '<h2>' . __( 'Theme Info', 'theme-check-extended' ) . ': </h2>';
		echo '<div class="theme-info">';
		if ( file_exists( trailingslashit( WP_CONTENT_DIR . '/themes' ) . trailingslashit( basename( $theme ) ) . 'screenshot.png' ) ) {
			$image = getimagesize( $theme . '/screenshot.png' );
			echo '<div style="float:right" class="theme-info"><img style="max-height:180px;" src="' . trailingslashit( WP_CONTENT_URL . '/themes' ) . trailingslashit( basename( $theme ) ) . 'screenshot.png" />';
			echo '<br /><div style="text-align:center">' . $image[0] . 'x' . $image[1] . ' ' . round( filesize( $theme . '/screenshot.png' ) / 1024 ) . 'k</div></div>';
		}

		echo ( ! empty( $data['Title'] ) ) ? '<p><label>' . __( 'Title', 'theme-check-extended' ) . '</label><span class="info">' . $data['Title'] . '</span></p>' : '';
		echo ( ! empty( $data['Version'] ) ) ? '<p><label>' . __( 'Version', 'theme-check-extended' ) . '</label><span class="info">' . $data['Version'] . '</span></p>' : '';
		echo ( ! empty( $data['AuthorName'] ) ) ? '<p><label>' . __( 'Author', 'theme-check-extended' ) . '</label><span class="info">' . $data['AuthorName'] . '</span></p>' : '';
		echo ( ! empty( $data['AuthorURI'] ) ) ? '<p><label>' . __( 'Author URI', 'theme-check-extended' ) . '</label><span class="info"><a href="' . $data['AuthorURI'] . '">' . $data['AuthorURI'] . '</a>' . '</span></p>' : '';
		echo ( ! empty( $data['URI'] ) ) ? '<p><label>' . __( 'Theme URI', 'theme-check-extended' ) . '</label><span class="info"><a href="' . $data['URI'] . '">' . $data['URI'] . '</a>' . '</span></p>' : '';
		echo ( ! empty( $data['License'] ) ) ? '<p><label>' . __( 'License', 'theme-check-extended' ) . '</label><span class="info">' . $data['License'] . '</span></p>' : '';
		echo ( ! empty( $data['License URI'] ) ) ? '<p><label>' . __( 'License URI', 'theme-check-extended' ) . '</label><span class="info">' . $data['License URI'] . '</span></p>' : '';
		echo ( ! empty( $data['Tags'] ) ) ? '<p><label>' . __( 'Tags', 'theme-check-extended' ) . '</label><span class="info">' . implode( $data['Tags'], ', ' ) . '</span></p>' : '';
		echo ( ! empty( $data['Description'] ) ) ? '<p><label>' . __( 'Description', 'theme-check-extended' ) . '</label><span class="info">' . $data['Description'] . '</span></p>' : '';

		if ( $data['Template'] ) {
			if ( $data['Template Version'] > $parent_data['Version'] ) {
				echo '<p>' . sprintf(
						__( 'This child theme requires at least version %1$s of theme %2$s to be installed. You only have %3$s please update the parent theme.', 'theme-check-extended' ),
						'<strong>' . $data['Template Version'] . '</strong>',
						'<strong>' . $parent_data['Title'] . '</strong>',
						'<strong>' . $parent_data['Version'] . '</strong>'
					) . '</p>';
			}
			echo '<p>' . sprintf(
					__( 'This is a child theme. The parent theme is: %s. These files have been included automatically!', 'theme-check-extended' ),
					'<strong>' . $data['Template'] . '</strong>'
				) . '</p>';
			if ( empty( $data['Template Version'] ) ) {
				echo '<p>' . __( 'Child theme does not have the <strong>Template Version</strong> tag in style.css.', 'theme-check-extended' ) . '</p>';
			} else {
				echo ( $data['Template Version'] < $parent_data['Version'] ) ? '<p>' . sprintf( __( 'Child theme is only tested up to version %1$s of %2$s breakage may occur! %3$s installed version is %4$s', 'theme-check-extended' ), $data['Template Version'], $parent_data['Title'], $parent_data['Title'], $parent_data['Version'] ) . '</p>' : '';
			}
		}
		echo '</div><!-- .theme-info-->';

		$plugins = get_plugins( '/theme-check' );
		$version = explode( '.', $plugins['theme-check.php']['Version'] );
		echo '<p>' . sprintf(
				__( ' Running %1$s tests against %2$s using Guidelines Version: %3$s Plugin revision: %4$s', 'theme-check-extended' ),
				'<strong>' . $checkcount . '</strong>',
				'<strong>' . $data['Title'] . '</strong>',
				'<strong>' . $version[0] . '</strong>',
				'<strong>' . $version[1] . '</strong>'
			) . '</p>';
		$results = display_themechecks();
		if ( ! $success ) {
			echo '<h2>' . sprintf( __( 'One or more errors were found for %1$s.', 'theme-check-extended' ), $data['Title'] ) . '</h2>';
		} else {
			echo '<h2>' . sprintf( __( '%1$s passed the tests', 'theme-check-extended' ), $data['Title'] ) . '</h2>';
			tc_success();
		}
		if ( ! defined( 'WP_DEBUG' ) || WP_DEBUG == false ) {
			echo '<div class="updated"><span class="tc-fail">' . __( 'WARNING', 'theme-check-extended' ) . '</span> ' . __( '<strong>WP_DEBUG is not enabled!</strong> Please test your theme with <a href="https://codex.wordpress.org/Editing_wp-config.php">debug enabled</a> before you upload!', 'theme-check-extended' ) . '</div>';
		}
		echo '<div class="tc-box">';
		echo '<ul class="tc-result">';
		echo $results;
		echo '</ul></div>';
	}
}

// strip comments from a PHP file in a way that will not change the underlying structure of the file
function tc_strip_comments( $code ) {
	$strip    = array( T_COMMENT => true, T_DOC_COMMENT => true );
	$newlines = array( "\n" => true, "\r" => true );
	$tokens   = token_get_all( $code );
	reset( $tokens );
	$return = '';
	$token  = current( $tokens );
	while ( $token ) {
		if ( ! is_array( $token ) ) {
			$return .= $token;
		} elseif ( ! isset( $strip[ $token[0] ] ) ) {
			$return .= $token[1];
		} else {
			for ( $i = 0, $token_length = strlen( $token[1] ); $i < $token_length; ++ $i ) {
				if ( isset( $newlines[ $token[1][ $i ] ] ) ) {
					$return .= $token[1][ $i ];
				}
			}
		}
		$token = next( $tokens );
	}

	return $return;
}


function tc_intro() {
	?>
    <h2><?php _e( 'About', 'theme-check-extended' ); ?></h2>
    <p><?php _e( "The Theme Check plugin is an easy way to test your theme and make sure it's up to date with the latest theme review standards. With it, you can run all the same automated testing tools on your theme that WordPress.org uses for theme submissions.", 'theme-check-extended' ); ?></p>
    <h2><?php _e( 'Contact', 'theme-check-extended' ); ?></h2>
    <p><?php printf( __( 'Theme Check is maintained by %1$s and %2$s.', 'theme-check-extended' ),
			'<a href="https://profiles.wordpress.org/otto42/">Otto42</a>',
			'<a href="https://profiles.wordpress.org/pross/">Pross</a>'
		); ?></p>
    <p><?php printf( __( 'If you have found a bug or would like to make a suggestion or contribution, please leave a post on the <a href="%1$s">WordPress forums</a>, or talk about it with the theme review team on <a href="%2$s">Make WordPress Themes</a> site.', 'theme-check-extended' ), 'https://wordpress.org/tags/theme-check?forum_id=10', 'https://make.wordpress.org/themes/' ); ?></p>
    <p><?php printf( __( 'The code for Theme Check can be contributed to on <a href="%s">GitHub</a>.', 'theme-check-extended' ), 'https://github.com/Otto42/theme-check' ); ?></p>
    <h3><?php _e( 'Testers', 'theme-check-extended' ); ?></h3>
    <p><a href="https://make.wordpress.org/themes/"><?php _e( 'The WordPress Theme Review Team', 'theme-check-extended' ); ?></a>
    </p>
	<?php
}

function tc_success() {
	?>
    <div class="tc-success">
        <p><?php _e( 'Now your theme has passed the basic tests you need to check it properly using the test data before you upload to the WordPress Themes Directory.', 'theme-check-extended' ); ?></p>
        <p><?php _e( 'Make sure to review the guidelines at <a href="https://codex.wordpress.org/Theme_Review">Theme Review</a> before uploading a Theme.', 'theme-check-extended' ); ?></p>
        <h3><?php _e( 'Codex Links', 'theme-check-extended' ); ?></h3>
        <ul>
            <li>
                <a href="https://codex.wordpress.org/Theme_Development"><?php _e( 'Theme Development', 'theme-check-extended' ); ?></a>
            </li>
            <li>
                <a href="https://wordpress.org/support/forum/5"><?php _e( 'Themes and Templates forum', 'theme-check-extended' ); ?></a>
            </li>
            <li>
                <a href="https://codex.wordpress.org/Theme_Unit_Test"><?php _e( 'Theme Unit Tests', 'theme-check-extended' ); ?></a>
            </li>
        </ul>
    </div>
	<?php
}

function tc_form() {
	$themes        = tc_get_themes();
	$exclude_paths = '';
	if ( isset( $_COOKIE['exclude_paths'] ) ) {
		$exclude_paths = base64_decode( $_COOKIE['exclude_paths'] );
	}

	echo '<form action="themes.php?page=themecheck" method="post">';
	echo '<select name="themename">';
	foreach ( $themes as $name => $location ) {
		echo '<option ';
		if ( isset( $_POST['themename'] ) ) {
			echo ( $location['Stylesheet'] === $_POST['themename'] ) ? 'selected="selected" ' : '';
		} else {
			echo ( basename( STYLESHEETPATH ) === $location['Stylesheet'] ) ? 'selected="selected" ' : '';
		}
		echo ( basename( STYLESHEETPATH ) === $location['Stylesheet'] ) ? 'value="' . $location['Stylesheet'] . '" style="font-weight:bold;">' . $name . '</option>' : 'value="' . $location['Stylesheet'] . '">' . $name . '</option>';
	}
	echo '</select>';
	echo '<input class="button" type="submit" value="' . __( 'Check it!', 'theme-check-extended' ) . '" />';
	if ( defined( 'TC_PRE' ) || defined( 'TC_POST' ) ) {
		echo ' <input name="trac" type="checkbox" /> ' . __( 'Output in Trac format.', 'theme-check-extended' );
	}
	echo '<input name="s_info" type="checkbox" /> ' . __( 'Suppress INFO.', 'theme-check-extended' );
	echo '<br><br><label>Exclude</label><br/><textarea name="s_exclude" class="widefat" rows="5">' . $exclude_paths . '</textarea>';
	echo '</form>';
}
