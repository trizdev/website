import React from 'react';
import { __ } from '@wordpress/i18n';
import ConfigValues from '../../../es6/config-values';
import { uniqueId } from 'lodash-es';
import TextInputField from '../../../components/form-fields/text-input-field';
import SettingsRow from '../../../components/settings-row';

export default class Separators extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			separator: ConfigValues.get('separator', 'breadcrumbs'),
			custom: ConfigValues.get('custom_sep', 'breadcrumbs'),
		};
	}

	render() {
		const { separator, custom } = this.state;

		const optName = ConfigValues.get('option_name', 'breadcrumbs');
		const separators = ConfigValues.get('separators', 'breadcrumbs');

		return (
			<SettingsRow
				label={__('Breadcrumbs Separator', 'wds')}
				description={__(
					'Select a breadcrumbs separator from the list or add a custom separator. You can also use HTML characters.',
					'wds'
				)}
			>
				<div className="wds-preset-separators">
					{Object.keys(separators).map((key) => {
						const id = uniqueId(key);

						return (
							<React.Fragment key={key}>
								<input
									type="radio"
									name={`${optName}[separator]`}
									id={id}
									value={key}
									autoComplete="off"
									checked={!custom && separator === key}
									onChange={(e) =>
										this.handleChange(e.target.value)
									}
								/>
								<label
									className="separator-selector"
									htmlFor={id}
								>
									{separators[key]}
								</label>
							</React.Fragment>
						);
					})}
				</div>
				<div className="wds-custom-separator">
					<TextInputField
						className="sui-input-md"
						label={__(
							'Enter your own custom separator',
							'wds'
						)}
						name={`${optName}[custom_sep]`}
						placeholder={__('Enter separator', 'wds')}
						value={custom}
						onChange={(e) => this.handleCustomChange(e)}
					></TextInputField>
				</div>
			</SettingsRow>
		);
	}

	handleChange(value) {
		this.setState({ separator: value, custom: '' });
		if (this.props.updateSeparator) {
			this.props.updateSeparator(value);
		}
	}

	handleCustomChange(value) {
		this.setState({ custom: value });
		if (this.props.updateCustomSeparator) {
			this.props.updateCustomSeparator(value);
		}
	}
}
