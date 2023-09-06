<?php
if (! function_exists('str_begins_with')) {
	/**
	 * Report whether a string (`$str`) begins with another (`$prefix`).
	 *
	 * @param string $str String to check.
	 * @param string $prefix
	 * @param boolean (optional) $insensitive if TRUE, comparison is case insensitive.
	 *
	 * @returns boolean
	 */
	function str_begins_with($str, $prefix, $insensitive=FALSE) {
		if ($str) {
			$len = strlen($prefix);
			return 0 === substr_compare($str, $prefix, 0, $len, $insensitive);
		} else {
			# TRUE iff both strings are empty
			return ! $prefix;
		}
	}
}

if (! function_exists('get_file_attachment_id')) {
	/* Should this function throw exceptions to distinguish "no record of attachment file" from "file doesn't exist" and "file can't be an attachment"? It would be useful when this function is being used as `attachment_exists()`. */
	/**
	 * If a given file is an attachment, return its post ID.
	 *
	 * @param string $pathname Path to file, absolute or relative (to uploads dir, content dir, home, ...)
	 *
	 * @returns int|NULL
	 */
	function get_file_attachment_id($pathname) {
		global $wpdb;
		$uploads = wp_get_upload_dir();
		$uploads = rtrim($uploads['basedir'], DIRECTORY_SEPARATOR) .  DIRECTORY_SEPARATOR;

		// first, determine full path
		if (file_exists($pathname)) {
			$fullpath = $pathname;
		} else {
			$base = $uploads;
			// assume $pathname is relative; try each directory in uploads path as a path prefix
			while (strlen($base) > 2) {
				if (file_exists($base . $pathname)) {
					$fullpath = $base . $pathname;
					break;
				}
				$base = dirname($base) . DIRECTORY_SEPARATOR;
			}
			if (empty($fullpath)) {
				// file doesn't exist
				return;
			}
		}

		// second, ensure file lives in uploads & get path relative to uploads
		$fullpath = realpath($fullpath);
		if (str_begins_with($fullpath, $uploads)) {
			$subpath = substr($fullpath, strlen($uploads));
		} else {
			// $pathname isn't an upload
			return;
		}

		// third, look up file in database
		$stmt = <<<EOS
			SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				  AND meta_value = %s
EOS;
		$query = $wpdb->prepare($stmt, $subpath);
		$id = $wpdb->get_var($query);
		return $id;
	}

}
