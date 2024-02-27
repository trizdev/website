(function ($) {
	$(function () {
		var __WDS_TITLE_COUNT = l10nWdsCounters.title_max,
			__WDS_META_COUNT = l10nWdsCounters.metadesc_max;
		function _replace(what, current) {
			return l10nWdsCounters[what]
				.replace(/\{MAX_COUNT\}/, __WDS_TITLE_COUNT)
				.replace(/\{CURRENT_COUNT\}/, current)
				.replace(/\{TOTAL_LEFT\}/, __WDS_TITLE_COUNT - current);
		}

		function checkTitleLength() {
			var txt = $('#wds_title').val(),
				res = txt ? txt.length : false;
			$('#wds_title_counter_result').html(
				res > __WDS_TITLE_COUNT
					? '<span style="color:red">' +
							_replace('title_longer', res) +
							'</span>'
					: _replace('title_length', res)
			);
		}
		function checkMainTitleLength() {
			var txt = $('#title').val(),
				res = txt ? txt.length : false;
			$('#wds_main_title_counter_result').html(
				res > __WDS_TITLE_COUNT
					? '<span style="color:red">' +
							_replace('main_title_longer', res) +
							'</span>'
					: _replace('title_length', res)
			);
		}

		function setUpCounters() {
			if (l10nWdsCounters.main_title_warning) {
				var $main_title = $('#title'),
					$main_title_root = $('#titlewrap');
				$main_title_root.append(
					'<p id="wds_main_title_counter_result">' +
						__WDS_TITLE_COUNT +
						' characters left</p>'
				);
				$main_title.on('keyup', checkMainTitleLength);
				$main_title.on('change', checkMainTitleLength);
				checkMainTitleLength();
			}
		}

		setUpCounters();

		// Set overflow for SEO metabox
		$('#wds-wds-meta-box .inside').css('overflow-x', 'scroll');
	});
})(jQuery);
