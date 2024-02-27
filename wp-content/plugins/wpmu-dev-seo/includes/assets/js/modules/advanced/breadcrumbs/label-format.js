import React from 'react';
import { __ } from '@wordpress/i18n';
import ConfigValues from '../../../es6/config-values';
import SettingsRow from '../../../components/settings-row';
import InsertVariablesField from '../../../components/form-fields/insert-variables-field';

export default class LabelFormat extends React.Component {
	static defaultProps = {
		formats: [],
	};

	constructor(props) {
		super(props);

		this.state = {
			formats: this.props.formats,
		};
	}

	render() {
		const { formats } = this.state;

		const optName = ConfigValues.get('option_name', 'breadcrumbs');

		return (
			<SettingsRow
				label={__('Breadcrumbs Label Format', 'wds')}
				description={__(
					'Customize your breadcrumbs label formats across your site. ',
					'wds'
				)}
			>
				<div className="sui-border-frame">
					{formats.map((format, index) => {
						return (
							<div className="sui-row" key={index}>
								<div className="sui-col-2">
									<InsertVariablesField
										name={`${optName}[labels][${format.type}]`}
										label={format.label}
										value={format.value}
										variables={format.variables}
										placeholder={format.placeholder}
										onChange={(value) =>
											this.onChangePreview(index, value)
										}
									/>
								</div>
							</div>
						);
					})}
				</div>
			</SettingsRow>
		);
	}

	onChangePreview(index, value) {
		if (this.props.onChange) {
			this.props.onChange(index, value);
		}
	}
}
