import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import Notice from '../../../components/notices/notice';
import Button from '../../../components/button';
import ConfigValues from '../../../es6/config-values';
import { createInterpolateElement } from '@wordpress/element';
import RequestUtil from '../../../utils/request-util';
import TextInputField from '../../../components/form-fields/text-input-field';
import UpsellNotice from '../../../components/notices/upsell-notice';
import { connect } from 'react-redux';

const isMember = ConfigValues.get('is_member', 'admin') === '1';

class MaxmindConfigActivation extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			licenseKey: '',
			errMsg: '',
			loading: false,
		};
	}

	handleChange(e) {
		this.setState({ licenseKey: e, errMsg: '' });
	}

	handleDownload(e) {
		e.preventDefault();

		this.setState({ loading: true }, () => {
			RequestUtil.post(
				'wds_download_geodb',
				ConfigValues.get('nonce', 'redirects'),
				{
					license_key: this.state.licenseKey,
				}
			)
				.then((resp) => {
					this.setState(
						{
							licenseKey: '',
						},
						() => {
							this.props.updateMaxmind(resp.key);
						}
					);
				})
				.catch((errMsg) => {
					this.setState({
						errMsg:
							errMsg === 'Unauthorized'
								? __(
										'Invalid license key. Please check that you have entered the correct key and try again.',
										'wds'
								  )
								: errMsg,
					});
				})
				.finally(() => {
					this.setState({
						loading: false,
					});
				});
		});

		return false;
	}

	render() {
		if (!isMember) {
			return (
				<UpsellNotice
					message={sprintf(
						// translators: 1, 2: opening and closing anchor tags.
						__(
							'%1$sUnlock with SmartCrawl Pro%2$s to unlock the Location-Based Redirects feature.',
							'wds'
						),
						'<a target="_blank" class="wds-maxmind-upsell-notice" href="https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_redirect_location_based_settings_upsell">',
						'</a>'
					)}
				/>
			);
		}

		const { errMsg, licenseKey, loading } = this.state;

		return (
			<>
				<Notice
					type="info"
					message={createInterpolateElement(
						__(
							'Location-based redirection uses Maxmind’s GeoLite2 Database. <a1>Create a free account</a1> and get the <a2>license key</a2> to download the latest Geo IP Database.',
							'wds'
						),
						{
							a1: (
								<a
									target="_blank"
									href="https://www.maxmind.com/en/geolite2/signup"
									rel="noreferrer"
								/>
							),
							a2: (
								<a
									target="_blank"
									href="https://www.maxmind.com/en/accounts/current/license-key"
									rel="noreferrer"
								/>
							),
						}
					)}
				/>

				<TextInputField
					placeholder={__('Enter license key')}
					label={__('Maxmind License Key', 'wds')}
					prefix={
						<span className="sui-icon-key" aria-hidden="true" />
					}
					suffix={
						<Button
							icon="sui-icon-download"
							text={__('Download', 'wds')}
							onClick={(e) => this.handleDownload(e)}
							disabled={!licenseKey || !!errMsg}
							loading={loading}
						></Button>
					}
					value={licenseKey}
					onChange={(e) => this.handleChange(e)}
					loading={loading}
					isValid={!errMsg}
					errorMessage={errMsg}
				></TextInputField>

				{!!licenseKey && !errMsg && (
					<Notice
						type=""
						icon="sui-icon-info"
						message={__(
							'Note that it could take up to 5 mins to activate the license.',
							'wds'
						)}
					/>
				)}
			</>
		);
	}
}

const mapDispatchToProps = {
	updateMaxmind: (maxmindKey) => ({
		type: 'UPDATE_MAXMIND_KEY',
		payload: { maxmindKey },
	}),
};

export default connect(null, mapDispatchToProps)(MaxmindConfigActivation);
