<?php
/**
 * Handle common Scan notification and reporting template.
 *
 * @since 3.8.0
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

trait Scan_Email_Template {
	/**
	 * Get email template.
	 *
	 * @return array
	 */
	public function get_email_template(): array {
		return [
			'found' => [
				'subject' => __( 'Malware Scan of {SITE_URL} is complete. {ISSUES_COUNT} issue(s) found.', 'wpdef' ),
				'body' => __(
					'Hi {USER_NAME},

Malware Scan identified {ISSUES_COUNT} issue(s) on {SITE_URL}. The identified issue(s) is/are listed below.

{ISSUES_LIST}', 'wpdef'
				),
			],
			'not_found' => [
				'subject' => __( 'Scan of {SITE_URL} complete. {ISSUES_COUNT} issues found.', 'wpdef' ),
				'body' => __(
					'Hi {USER_NAME},

No vulnerabilities have been found for {SITE_URL}.', 'wpdef'
				),
			],
			'error' => [
				'subject' => __( 'Couldn’t scan {SITE_URL} for vulnerabilities. ', 'wpdef' ),
				'body' => __(
					'Hi {USER_NAME},

We couldn’t scan {SITE_URL} for vulnerabilities. Please visit your site and run a manual scan.', 'wpdef'
				),
			],
		];
	}
}