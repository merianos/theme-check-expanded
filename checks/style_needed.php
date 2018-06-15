<?php
class Style_Needed implements themecheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {

		$css = implode( ' ', $css_files );
		$ret = true;

		$checks = array(
			'[ \t\/*#]*Theme Name:' => __( '<strong>Theme name:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*Description:' => __( '<strong>Description:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*Author:' => __( '<strong>Author:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*Version' => __( '<strong>Version:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*License:' => __( '<strong>License:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*License URI:' => __( '<strong>License URI:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'[ \t\/*#]*Text Domain:' => __( '<strong>Text Domain:</strong> is missing from your style.css header.', 'theme-check-extended' ),
			'\.sticky' => __( '<strong>.sticky</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.bypostauthor' => __( '<strong>.bypostauthor</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.alignleft' => __( '<strong>.alignleft</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.alignright' => __( '<strong>.alignright</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.aligncenter' => __( '<strong>.aligncenter</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.wp-caption' => __( '<strong>.wp-caption</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.wp-caption-text' => __( '<strong>.wp-caption-text</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.gallery-caption' => __( '<strong>.gallery-caption</strong> css class is needed in your theme css.', 'theme-check-extended' ),
			'\.screen-reader-text' => __( '<strong>.screen-reader-text</strong> css class is needed in your theme css. See See: <a href="http://codex.wordpress.org/CSS#WordPress_Generated_Classes">the Codex</a> for an example implementation.', 'theme-check-extended' )
		);

		foreach ($checks as $key => $check) {
			checkcount();
			if ( !preg_match( '/' . $key . '/i', $css, $matches ) ) {
				$this->error[] = "<span class='tc-lead tc-required'>" . __('REQUIRED', 'theme-check-extended' ) . "</span>: " . $check;
				$ret = false;
			}
		}

		return $ret;
	}
	function getError() { return $this->error; }
}
$themechecks[] = new Style_Needed;
