import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import VerticalTab from '../../components/vertical-tab';
import UrlUtil from '../../utils/url-util';
import Deactivate from '../../components/deactivate';
import DisabledComponent from '../../components/disabled-component';
import Button from '../../components/button';
import ConfigValues from '../../es6/config-values';
import CodeType from './breadcrumbs/code-type';
import Previews from './breadcrumbs/previews';
import Separators from './breadcrumbs/separators';
import Configs from './breadcrumbs/configs';
import LabelFormat from './breadcrumbs/label-format';

const optName = ConfigValues.get('option_name', 'breadcrumbs');
const active = ConfigValues.get('active', 'breadcrumbs');
const isCurrent =
	UrlUtil.getQueryParam('tab') &&
	UrlUtil.getQueryParam('tab') === 'tab_breadcrumb';

export default class Breadcrumbs extends React.Component {
	constructor(props) {
		super(props);

		const options = ConfigValues.get('options', 'breadcrumbs') || [],
			previews = options.map((opt) => ({
				type: opt.type,
				label: opt.label,
				snippets: opt.snippets,
				value: opt.value,
				default: opt.placeholder,
			})),
			configs = ConfigValues.get('configs', 'breadcrumbs'),
			prefix = ConfigValues.get('prefix', 'breadcrumbs'),
			separator = ConfigValues.get('separator', 'breadcrumbs'),
			custom = ConfigValues.get('custom_sep', 'breadcrumbs'),
			homeText = ConfigValues.get('home_label', 'breadcrumbs')
				? ConfigValues.get('home_label', 'breadcrumbs')
				: 'Home';
		this.state = {
			previews,
			configs,
			prefix,
			separator,
			custom,
			homeText,
		};
	}

	render() {
		return (
			<VerticalTab
				id="tab_breadcrumb"
				title={__('Breadcrumbs', 'wds')}
				buttonText={active && __('Save Settings', 'wds')}
				isActive={isCurrent}
			>
				{active ? this.renderActivation() : this.renderDeactivation()}
			</VerticalTab>
		);
	}

	renderActivation() {
		const { previews, configs, prefix, separator, custom, homeText } =
			this.state;

		const options = ConfigValues.get('options', 'breadcrumbs'),
			formats = options.map((opt) => ({
				type: opt.type,
				/* translators: %s: Breadcrumb type name */
				label: sprintf(__('%s Label Format'), opt.title || opt.label),
				value: opt.value,
				placeholder: opt.placeholder,
				variables: opt.variables,
			}));
		return (
			<>
				<CodeType />
				<Previews
					previews={previews}
					options={configs}
					homeTrail={configs?.home_trail?.value}
					prefix={prefix}
					separator={separator}
					custom={custom}
					homeText={homeText}
				/>
				<Separators
					updateSeparator={(value) => this.updateSeparator(value)}
					updateCustomSeparator={(value) =>
						this.updateCustomSeparator(value)
					}
				/>
				<Configs
					configs={configs}
					prefix={prefix}
					homeText={homeText}
					onChange={(updated) => this.handleConfigChange(updated)}
					onHandlePrefix={(value) => this.handlePrefixChange(value)}
					onHandleHomeText={(value) =>
						this.handleHomeTextChange(value)
					}
				/>
				<LabelFormat
					formats={formats}
					onChange={(index, value) =>
						this.handlePreviewChange(index, value)
					}
				/>
				<Deactivate
					description={__(
						'No longer need breadcrumbs? This will deactivate this feature.',
						'wds'
					)}
					name={`${optName}[active]`}
				></Deactivate>
			</>
		);
	}

	renderDeactivation() {
		return (
			<DisabledComponent
				message={__(
					"Breadcrumbs provide an organized trail of links showing a visitor's journey on a website, improving the user experience and aiding search engines in understanding the site's structure for enhanced SEO.",
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
		);
	}

	handleConfigChange(key) {
		this.setState({
			configs: {
				...this.state.configs,
				[key]: {
					...this.state.configs[key],
					value: !this.state.configs[key].value,
				},
			},
		});
	}
	handlePreviewChange(i, v) {
		const originalPreview = this.state.previews;
		this.setState({
			previews: originalPreview.map((item, index) => {
				if (index === i) {
					return { ...item, value: v };
				}
				return item;
			}),
		});
	}
	handlePrefixChange(value) {
		this.setState({ prefix: value });
	}
	handleHomeTextChange(value) {
		this.setState({ homeText: value });
	}
	updateSeparator(value) {
		this.setState({ separator: value });
	}
	updateCustomSeparator(value) {
		this.setState({ custom: value });
	}
}
