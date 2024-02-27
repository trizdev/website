import React from 'react';
import UrlUtil from '../../../utils/url-util';
import VerticalTab from '../../../components/vertical-tab';
import { __ } from '@wordpress/i18n';
import SettingsRow from '../../../components/settings-row';
import ConfigValues from '../../../es6/config-values';
import Select from '../../../components/input-fields/select';
import Toggle from '../../../components/toggle';
import Checkbox from '../../../components/checkbox';
import RequestUtil from '../../../utils/request-util';
import $ from 'jQuery';
import { getRedirectTypes } from '../../../utils/redirect-utils';
import Deactivate from '../../../components/deactivate';
import MaxmindConfigDeactivation from './maxmind-config-deactivation';
import MaxmindConfigActivation from './maxmind-config-activation';
import { connect } from 'react-redux';

const isActive =
	UrlUtil.getQueryParam('tab') &&
	UrlUtil.getQueryParam('tab') === 'tab_url_redirection';
const isMember = ConfigValues.get('is_member', 'admin') === '1';
const optName = ConfigValues.get('option_name', 'redirects');

class RedirectSettings extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			isNewFeature:
				ConfigValues.get('new_feature_badge', 'admin') !== '1',
		};

		this.newFeature = React.createRef();
	}

	componentDidMount() {
		if (!this.state.isNewFeature) {
			return;
		}

		$(window).on('scroll', () => {
			if (this.state.isNewFeature && this.isElementInViewport()) {
				RequestUtil.post(
					'wds_update_new_feature_badge',
					ConfigValues.get('nonce', 'admin')
				);
			}
		});
	}

	isElementInViewport() {
		const $target = $(this.newFeature.current);
		const elementTop = $target.offset().top;
		const elementBottom = elementTop + $target.height();
		const viewportTop = $(window).scrollTop();
		const viewportBottom = viewportTop + $(window).height();

		return elementBottom > viewportTop && elementTop < viewportBottom;
	}

	render() {
		const { defaultType, maxmindKey, updateDefaultType } = this.props;

		return (
			<VerticalTab
				title={__('Settings', 'wds')}
				isActive={isActive}
				buttonText={__('Save Settings', 'wds')}
			>
				<SettingsRow
					label={__('Redirect attachments', 'wds')}
					description={__(
						'Redirect attachments to their respective file, preventing them from appearing in the SERPs.',
						'wds'
					)}
				>
					<Toggle
						name={`${optName}[attachments]`}
						label={__('Redirect attachments', 'wds')}
						checked={ConfigValues.get('attachments', 'redirects')}
					>
						<Checkbox
							name={`${optName}[images_only]`}
							label={__(
								'Redirect image attachments only',
								'wds'
							)}
							defaultChecked={ConfigValues.get(
								'images_only',
								'redirects'
							)}
						></Checkbox>
						<p
							className="sui-description"
							style={{ marginLeft: '25px' }}
						>
							{__(
								'Select this option if you only want to redirect attachments that are an image.',
								'wds'
							)}
						</p>
					</Toggle>
				</SettingsRow>

				<SettingsRow
					label={__('Default Redirection Type', 'wds')}
					description={__(
						'Select the redirection type that you would like to be used as default.',
						'wds'
					)}
				>
					<Select
						name={`${optName}[default_type]`}
						minimumResultsForSearch="-1"
						options={getRedirectTypes()}
						selectedValue={defaultType}
						onSelect={(type) => updateDefaultType(type)}
					></Select>
				</SettingsRow>

				<SettingsRow
					label={
						<>
							{__('Location-based Rules', 'wds')}
							{!isMember && (
								<span
									className="sui-tag sui-tag-pro sui-tooltip"
									data-tooltip={__(
										'Unlock with SmartCrawl Pro to gain access to location-based redirections rules.',
										'wds'
									)}
								>
									{__('Pro', 'wds')}
								</span>
							)}
							{!!isMember && this.state.isNewFeature && (
								<span
									className="sui-tag sui-tag-green sui-tag-sm"
									ref={this.newFeature}
								>
									{__('New', 'wds')}
								</span>
							)}
						</>
					}
					description={__(
						'Add location-based redirect rules to ensure users see the most relevant content based on their locations.',
						'wds'
					)}
				>
					{maxmindKey ? (
						<MaxmindConfigDeactivation />
					) : (
						<MaxmindConfigActivation />
					)}
				</SettingsRow>

				<Deactivate
					description={__(
						'No longer need URL Redirection? This will deactivate this feature and disable all redirects. Your redirects will not be deleted.',
						'wds'
					)}
					name={`${optName}[active]`}
				/>
			</VerticalTab>
		);
	}
}

const mapStateToProps = (state) => ({ ...state });

const mapDispatchToProps = {
	updateDefaultType: (defaultType) => ({
		type: 'UPDATE_DEFAULT_TYPE',
		payload: { defaultType },
	}),
};

export default connect(mapStateToProps, mapDispatchToProps)(RedirectSettings);
