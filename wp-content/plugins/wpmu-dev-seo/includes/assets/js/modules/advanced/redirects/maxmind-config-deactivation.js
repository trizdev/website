import React from 'react';
import { __ } from '@wordpress/i18n';
import Notice from '../../../components/notices/notice';
import Button from '../../../components/button';
import ConfigValues from '../../../es6/config-values';
import RequestUtil from '../../../utils/request-util';
import TextInputField from '../../../components/form-fields/text-input-field';
import { connect } from 'react-redux';

class MaxmindConfigDeactivation extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			errMsg: '',
			loading: false,
		};
	}

	handleDisconnect(e) {
		e.preventDefault();

		this.setState({ loading: true }, () => {
			RequestUtil.post(
				'wds_reset_geodb',
				ConfigValues.get('nonce', 'redirects')
			)
				.then(() => {
					this.props.updateMaxmind('');
				})
				.catch((err) => {
					this.setState({ errMsg: err.message });
				})
				.finally(() => {
					this.setState({ loading: false });
				});
		});

		return false;
	}

	render() {
		const { maxmindKey } = this.props;
		const { errMsg, loading } = this.state;

		return (
			<>
				<TextInputField
					label={
						<>
							{__('Maxmind License Key', 'wds')}
							<span className="sui-tag sui-tag-green sui-tag-sm">
								{__('Connected', 'wds')}
							</span>
						</>
					}
					description={__(
						'Your site is connected to above Maxmind license key. SmartCrawl automatically downloads latest GeoLite2 data weekly. You can use the disconnect button above to change the license key.',
						'wds'
					)}
					prefix={
						<span className="sui-icon-key" aria-hidden="true" />
					}
					suffix={
						<>
							<Button
								icon="sui-icon-plug-disconnected"
								text={__('Disconnect', 'wds')}
								onClick={(e) => this.handleDisconnect(e)}
								loading={loading}
							></Button>
						</>
					}
					value={maxmindKey}
					readOnly={true}
					loading={loading}
				></TextInputField>

				{!!errMsg && <Notice type="error" message={errMsg} />}
			</>
		);
	}
}

const mapStateToProps = (state) => ({ maxmindKey: state.maxmindKey });

const mapDispatchToProps = {
	updateMaxmind: (maxmindKey) => ({
		type: 'UPDATE_MAXMIND_KEY',
		payload: { maxmindKey },
	}),
};

export default connect(
	mapStateToProps,
	mapDispatchToProps
)(MaxmindConfigDeactivation);
