import React from 'react';
import { __ } from '@wordpress/i18n';
import ConfigValues from '../../../es6/config-values';
import SettingsRow from '../../../components/settings-row';
import Toggle from '../../../components/toggle';
import TextInputField from '../../../components/form-fields/text-input-field';

export default class Configs extends React.Component {
	static defaultProps = {
		onChange: () => false,
		configs: [],
	};

	render() {
		const { configs, onChange, prefix, homeText } = this.props;

		const optName = ConfigValues.get('option_name', 'breadcrumbs');

		return (
			<SettingsRow
				label={__('Configurations', 'wds')}
				description={__(
					'Enable and configure the additional breadcrumbs settings for your site.',
					'wds'
				)}
			>
				{Object.keys(configs).map((key, index) => {
					const config = configs[key];
					return (
						<div className="sui-row" key={index}>
							<div className="sui-col-2">
								<Toggle
									name={`${optName}[${key}]`}
									label={config.label}
									description={config.description}
									checked={config.value}
									onChange={() => onChange(key)}
								/>

								{key === 'add_prefix' && config.value && (
									<div className="sui-border-frame">
										<TextInputField
											name={`${optName}[prefix]`}
											label={__(
												'Prefix',
												'wds'
											)}
											placeholder={__(
												'Eg. Location',
												'wds'
											)}
											value={prefix || ''}
											onChange={(value) =>
												this.onChangePrefix(value)
											}
										></TextInputField>
									</div>
								)}
								{key === 'home_trail' && config.value && (
									<div className="sui-border-frame">
										<TextInputField
											name={`${optName}[home_label]`}
											placeholder={__(
												'Eg. Location',
												'wds'
											)}
											value={homeText || ''}
											onChange={(value) =>
												this.onChangeHomeText(value)
											}
										></TextInputField>
									</div>
								)}
							</div>
						</div>
					);
				})}
			</SettingsRow>
		);
	}
	onChangePrefix(value) {
		if (this.props.onHandlePrefix) {
			this.props.onHandlePrefix(value);
		}
	}
	onChangeHomeText(value) {
		if (this.props.onHandleHomeText) {
			this.props.onHandleHomeText(value);
		}
	}
}
