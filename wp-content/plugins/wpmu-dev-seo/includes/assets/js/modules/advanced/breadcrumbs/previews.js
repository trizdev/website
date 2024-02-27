import React, { createRef } from 'react';
import { __ } from '@wordpress/i18n';
import SettingsRow from '../../../components/settings-row';
import { createInterpolateElement } from '@wordpress/element';
import ConfigValues from '../../../es6/config-values';
import $ from 'jQuery';

export default class Previews extends React.Component {
	static defaultProps = {
		previews: [],
		homeTrail: 'off',
		separator: 'caret-right',
		prefix: '',
		custom: '',
		options: [],
		homeText: 'Home',
	};

	constructor(props) {
		super(props);

		this.state = {
			expandPreview: false,
		};

		this.refPreviews = createRef();
	}

	render() {
		const { previews } = this.props;
		const { expandPreview } = this.state;
		return (
			<SettingsRow
				key={1}
				label={__('Preview', 'wds')}
				description={createInterpolateElement(
					__(
						'See how breadcrumbs will appear on your web page. Click “<strong>Show more</strong>” to reveal the preview of breadcrumbs on all page types.',
						'wds'
					),
					{ strong: <strong /> }
				)}
			>
				<div className="sui-border-frame">
					<div
						className="wds-breadcrumb-previews"
						ref={this.refPreviews}
					>
						{this.renderPreviews()}

						{previews.length > 1 && (
							<a
								href="#"
								className="wds-breadcrumb-preview-expander"
								onClick={(e) => this.handleExpand(e)}
							>
								{!!expandPreview && (
									<React.Fragment>
										<span className="wds-breadcrumb-preview-expander-text">
											{__('Hide more', 'wds')}
										</span>
										<span className="sui-icon sui-icon-chevron-up"></span>
									</React.Fragment>
								)}
								{!expandPreview && (
									<React.Fragment>
										<span className="wds-breadcrumb-preview-expander-text">
											{__('Show more', 'wds')}
										</span>
										<span className="sui-icon sui-icon-chevron-down"></span>
									</React.Fragment>
								)}
							</a>
						)}
					</div>
				</div>
			</SettingsRow>
		);
	}

	renderPreviews() {
		const { previews } = this.props;

		return (
			<React.Fragment>
				{this.renderPreview(previews[0])}
				<div
					className="wds-breadcrumb-previews-extra"
					style={{ display: 'none' }}
				>
					{previews.slice(1).map((preview, index) => (
						<React.Fragment key={index}>
							{this.renderPreview(preview)}
						</React.Fragment>
					))}
				</div>
			</React.Fragment>
		);
	}

	renderPreview(preview) {
		const { homeTrail, separator, custom, prefix, options, homeText } =
			this.props;
		const homeSettings = ConfigValues.get('home_page', 'breadcrumbs');
		return (
			<div className="wds-breadcrumb-preview">
				<div className="wds-breadcrumb-preview-label">
					<strong>{preview.label}:</strong>
				</div>
				<div className="wds-breadcrumb-preview-snippets">
					{prefix !== '' && options?.add_prefix?.value !== false && (
						<React.Fragment>
							<span className="prefix">{prefix}</span>
						</React.Fragment>
					)}
					{homeTrail !== false && (
						<React.Fragment>
							<strong>
								<a
									href={homeSettings.url}
									target="_blank"
									rel="noreferrer"
								>
									{homeText}
								</a>
							</strong>
							<span className="sui-icon">
								{custom === ''
									? this.defaultSeperaterCss[separator]
									: custom}
							</span>
						</React.Fragment>
					)}
					{preview.snippets.map((snippet, ind) => (
						<React.Fragment key={ind}>
							<strong>{snippet}</strong>
							{ind !== preview.snippets.length - 1 && (
								<span className="sui-icon">
									{custom === ''
										? this.defaultSeperaterCss[separator]
										: custom}
								</span>
							)}
							{ind === preview.snippets.length - 1 &&
								options?.hide_post_title?.value === false && (
									<span className="sui-icon">
										{custom === ''
											? this.defaultSeperaterCss[
													separator
											  ]
											: custom}
									</span>
								)}
						</React.Fragment>
					))}
					{options?.hide_post_title?.value === false && (
						<React.Fragment>
							<span>
								{preview.value === ''
									? preview.default
									: preview.value}
							</span>
						</React.Fragment>
					)}
					{options?.hide_post_title?.value !== false &&
						preview.type !== 'post' &&
						preview.type !== 'page' && (
							<React.Fragment>
								<span>
									{preview.value === ''
										? preview.default
										: preview.value}
								</span>
							</React.Fragment>
						)}
				</div>
			</div>
		);
	}

	handleExpand(e) {
		e.preventDefault();

		this.setState({
			expandPreview: !this.state.expandPreview,
		});

		$(this.refPreviews.current)
			.find('.wds-breadcrumb-previews-extra')
			.slideToggle();
	}
	defaultSeperaterCss = {
		dot: '·',
		'dot-l': '•',
		dash: '-',
		'dash-l': '—',
		pipe: '|',
		'forward-slash': '/',
		'back-slash': '\\',
		tilde: '~',
		'greater-than': '>',
		'less-than': '<',
		'caret-right': '›',
		'caret-left': '‹',
		'arrow-right': '→',
		'arrow-left': '←',
	};
}
