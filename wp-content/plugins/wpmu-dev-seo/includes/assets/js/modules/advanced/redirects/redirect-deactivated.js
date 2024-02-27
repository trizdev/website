import React from 'react';
import DisabledComponent from '../../../components/disabled-component';
import Button from '../../../components/button';
import { __ } from '@wordpress/i18n';
import VerticalTab from '../../../components/vertical-tab';
import UrlUtil from '../../../utils/url-util';
import ConfigValues from '../../../es6/config-values';

const isActive =
	UrlUtil.getQueryParam('tab') &&
	UrlUtil.getQueryParam('tab') === 'tab_url_redirection';

const optName = ConfigValues.get('option_name', 'redirects');

export default class RedirectDeactivated extends React.Component {
	render() {
		return (
			<VerticalTab
				title={__('URL Redirection', 'wds')}
				isActive={isActive}
			>
				<DisabledComponent
					message={__(
						'Configure SmartCrawl to automatically redirect traffic from one URL to another. Use this tool if you have changed a page’s URL and wish to keep traffic flowing to the new page.',
						'wds'
					)}
					nonceFields={false}
					button={
						<Button
							type="submit"
							name={`${optName}[active]`}
							value="1"
							color="blue"
							text={__('Activate', 'wds')}
						/>
					}
					inner
				/>
			</VerticalTab>
		);
	}
}
