import React from 'react';
import { __ } from '@wordpress/i18n';
import FieldList from '../../../components/field-list';
import Toggle from '../../../components/toggle';
import ConfigValues from '../../../es6/config-values';
import update from 'immutability-helper';
import SettingsRow from '../../../components/settings-row';
import SelectField from '../../../components/form-fields/select-field';

export default class AutolinkTypes extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			insert: ConfigValues.get('insert', 'autolinks'),
			linkTo: ConfigValues.get('link_to', 'autolinks'),
			insertOptions: ConfigValues.get('insert_options', 'autolinks'),
			linktoOptions: ConfigValues.get('link_to_options', 'autolinks'),
		};
	}

	render() {
		const optName = ConfigValues.get('option_name', 'autolinks');

		return (
			<SettingsRow
				label={__('Post Types', 'wds')}
				description={__(
					'Use the options below to select post types to insert links in, and post types/taxonomies to link to.',
					'wds'
				)}
				direction="column"
			>
				<div className="sui-border-frame">
					<SelectField
						label={__('Insert Links', 'wds')}
						description={__(
							'Select the post types to insert links in.',
							'wds'
						)}
						selectedValue={this.state.insert}
						multiple={true}
						onSelect={(values) =>
							this.handleChange('insert', values)
						}
						options={this.state.insertOptions}
						name={`${optName}[insert][]`}
					/>
					<SelectField
						label={__('Link To', 'wds')}
						description={__(
							'Select the post types & taxonomies that can be linked to.',
							'wds'
						)}
						selectedValue={this.state.linkTo}
						multiple={true}
						onSelect={(values) =>
							this.handleChange('linkTo', values)
						}
						options={this.state.linktoOptions}
						name={`${optName}[link_to][]`}
					/>
				</div>
			</SettingsRow>
		);
	}

	handleChange(key, values) {
		this.setState({
			[key]: values,
		});
	}
}
