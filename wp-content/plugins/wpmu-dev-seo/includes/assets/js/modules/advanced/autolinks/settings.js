import React from 'react';
import { __ } from '@wordpress/i18n';
import Toggle from '../../../components/toggle';
import ConfigValues from '../../../es6/config-values';
import SettingsRow from '../../../components/settings-row';
import TextInputField from '../../../components/form-fields/text-input-field';

export default class Settings extends React.Component {
	render() {
		const optName = ConfigValues.get('option_name', 'autolinks'),
			settings = ConfigValues.get('additional', 'autolinks');

		return (
			<>
				<SettingsRow
					label={__('Min lengths', 'wds')}
					description={__(
						'Define the shortest title and taxonomy length to autolink. Smaller titles will be ignored.',
						'wds'
					)}
				>
					<div className="sui-row sui-no-margin-bottom">
						<div className="sui-col-auto">
							<TextInputField
								id="cpt_char_limit"
								name={`${optName}[cpt_char_limit]`}
								label={__('Posts & pages', 'wds')}
								className="sui-input-sm"
								value={ConfigValues.get(
									'cpt_char_limit',
									'autolinks'
								)}
							></TextInputField>
						</div>
						<div className="sui-col-auto">
							<TextInputField
								id="tax_char_limit"
								name={`${optName}[tax_char_limit]`}
								label={__(
									'Archives & taxonomies',
									'wds'
								)}
								className="sui-input-sm"
								value={ConfigValues.get(
									'tax_char_limit',
									'autolinks'
								)}
							></TextInputField>
						</div>
					</div>
					<p className="sui-description">
						{__(
							'We recommend a minimum of 10 chars for each type.',
							'wds'
						)}
					</p>
				</SettingsRow>
				<SettingsRow
					label={__('Max limits', 'wds')}
					description={__(
						'Set the max amount of links you want to appear per post.',
						'wds'
					)}
				>
					<div className="sui-row sui-no-margin-bottom">
						<div className="sui-col-auto">
							<TextInputField
								id="link_limit"
								name={`${optName}[link_limit]`}
								label={__('Per post total', 'wds')}
								className="sui-input-sm"
								value={ConfigValues.get(
									'link_limit',
									'autolinks'
								)}
							></TextInputField>
						</div>
						<div className="sui-col-auto">
							<TextInputField
								id="single_link_limit"
								name={`${optName}[single_link_limit]`}
								label={__(
									'Per keyword group',
									'wds'
								)}
								className="sui-input-sm"
								value={ConfigValues.get(
									'single_link_limit',
									'autolinks'
								)}
							></TextInputField>
						</div>
					</div>
					<p className="sui-description">
						{__(
							'Use 0 to allow unlimited automatic links.',
							'wds'
						)}
					</p>
				</SettingsRow>
				<SettingsRow
					label={__('Optional Settings', 'wds')}
					description={__(
						'Configure extra settings for absolute control over autolinking.',
						'wds'
					)}
				>
					{Object.keys(settings).map((key, index) => {
						const setting = settings[key];
						return (
							<div className="sui-row" key={index}>
								<div className="sui-col-2">
									<Toggle
										name={`${optName}[${key}]`}
										label={setting.label}
										description={setting.description}
										checked={setting.value}
									/>
								</div>
							</div>
						);
					})}
				</SettingsRow>
			</>
		);
	}
}
