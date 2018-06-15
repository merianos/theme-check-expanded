<?php
class File_Checks implements themecheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {

		$ret = true;

		$filenames = array();

		foreach ( $php_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		foreach ( $other_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		foreach ( $css_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		$blacklist = array(
				'thumbs.db'				=> __( 'Windows thumbnail store', 'theme-check-extended' ),
				'desktop.ini'			=> __( 'windows system file', 'theme-check-extended' ),
				'project.properties'	=> __( 'NetBeans Project File', 'theme-check-extended' ),
				'project.xml'			=> __( 'NetBeans Project File', 'theme-check-extended' ),
				'\.kpf'					=> __( 'Komodo Project File', 'theme-check-extended' ),
				'^\.+[a-zA-Z0-9]'		=> __( 'Hidden Files or Folders', 'theme-check-extended' ),
				'php.ini'				=> __( 'PHP server settings file', 'theme-check-extended' ),
				'dwsync.xml'			=> __( 'Dreamweaver project file', 'theme-check-extended' ),
				'error_log'				=> __( 'PHP error log', 'theme-check-extended' ),
				'web.config'			=> __( 'Server settings file', 'theme-check-extended' ),
				'\.sql'					=> __( 'SQL dump file', 'theme-check-extended' ),
				'__MACOSX'				=> __( 'OSX system file', 'theme-check-extended' ),
				'\.lubith'				=> __( 'Lubith theme generator file', 'theme-check-extended' ),
				);

		$musthave = array( 'index.php', 'style.css' );
		$rechave = array( 'readme.txt' => __( 'Please see <a href="https://codex.wordpress.org/Theme_Review#Theme_Documentation">Theme_Documentation</a> for more information.', 'theme-check-extended' ) );

		checkcount();

		foreach( $blacklist as $file => $reason ) {
			if ( $filename = preg_grep( '/' . $file . '/', $filenames ) ) {
				$error = implode( array_unique( $filename ), ' ' );
				$this->error[] = sprintf('<span class="tc-lead tc-warning">'.__('WARNING','theme-check-extended').'</span>: '.__('%1$s %2$s found.', 'theme-check-extended'), '<strong>' . $error . '</strong>', $reason) ;
				$ret = false;
			}
		}

		foreach( $musthave as $file ) {
			if ( !in_array( $file, $filenames ) ) {
				$this->error[] = sprintf('<span class="tc-lead tc-warning">'.__('WARNING','theme-check-extended').'</span>: '.__('Could not find the file %s in the theme.', 'theme-check-extended'), '<strong>' . $file . '</strong>' );
				$ret = false;
			}
		}

		foreach( $rechave as $file => $reason ) {
			if ( !in_array( $file, $filenames ) ) {
				$this->error[] = sprintf('<span class="tc-lead tc-recommended">'.__('RECOMMENDED','theme-check-extended').'</span>: '.__('Could not find the file %1$s in the theme. %2$s', 'theme-check-extended'), '<strong>' . $file . '</strong>', $reason );
			}
		}

		return $ret;
	}

	function getError() { return $this->error; }
}
$themechecks[] = new File_Checks;
