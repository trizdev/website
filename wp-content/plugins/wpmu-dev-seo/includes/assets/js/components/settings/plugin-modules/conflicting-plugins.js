import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import Notice from '../../notices/notice';
import ConfigValues from '../../../es6/config-values';
import { createInterpolateElement } from '@wordpress/element';
import Button from '../../button';
import Modal from '../../modal';
import ConflictingPluginItem from './conflicting-plugin-item';
import update from 'immutability-helper';
import { debounce } from 'lodash-es';

export default class ConflictingPlugins extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			open: false,
			plugins: ConfigValues.get('plugins', 'conflicts') || {},
			inProgress: false,
			error: false,
			deactivatedPlugin: false,
		};
	}

	showModal() {
		this.setState({ open: true });
	}

	hideModal() {
		this.setState({ open: false });
	}

	handleDeactivate(plugin) {
		this.setState(
			{
				deactivatedPlugin: this.state.plugins[plugin].name,
				plugins: update(this.state.plugins, { $unset: [plugin] }),
				inProgress: false,
			},
			() => {
				const debounced = debounce(() => {
					this.setState({ deactivatedPlugin: false });
				}, 3000);

				debounced();
			}
		);
	}

	handleError(plugin = false, error = false) {
		if (!plugin) {
			this.setState({ error: false });
		}

		this.setState({ error });
	}

	handleProgress(val) {
		this.setState({ inProgress: val });
	}

	render() {
		const { open, plugins, inProgress, deactivatedPlugin, error } =
			this.state;

		return (
			<>
				{Object.keys(plugins).length > 0 && (
					<Notice
						type="warning"
						message={createInterpolateElement(
							__(
								'<strong>We’ve detected one or more SEO plugins on your site.</strong> To avoid SEO issues, please disable the following conflicting plugin(s) or select specific SmartCrawl modules to use alongside the other plugins below.',
								'wds'
							),
							{ strong: <strong /> }
						)}
						actions={
							<Button
								ghost={true}
								text={__(
									'View Conflicting Plugins',
									'wds'
								)}
								onClick={() => this.showModal()}
							/>
						}
					></Notice>
				)}

				{open && (
					<Modal
						id="wds-confl-plugs"
						small={true}
						title={__('Conflicting Plugins', 'wds')}
						description={sprintf(
							// translators: %d: number of plugins conflicting.
							__(
								'We detected %d plugins conflicting with SmartCrawl on your site. For best SEO performance, please deactivate the plugins listed below or activate specific SmartCrawl modules to use alongside these listed plugins.',
								'wds'
							),
							Object.keys(plugins).length
						)}
						onClose={() => this.hideModal()}
						footer={
							<>
								<Button
									text={__('Close', 'wds')}
									ghost={true}
									onClick={() => this.hideModal()}
									disabled={inProgress}
								/>
								<Button
									text={__(
										'Go to Plugins page',
										'wds'
									)}
									href={ConfigValues.get(
										'plugins_url',
										'admin'
									)}
									icon="sui-icon-arrow-right"
									iconRight={true}
									disabled={inProgress}
								/>
							</>
						}
					>
						<>
							{!!deactivatedPlugin && (
								<Notice
									type="success"
									message={sprintf(
										// translators: %s: plugin name.
										__(
											'%s has been deactivated successfully.',
											'wds'
										),
										deactivatedPlugin
									)}
								/>
							)}

							{!!error && (
								<Notice
									type="error"
									message={createInterpolateElement(error, {
										a: (
											<a
												href={ConfigValues.get(
													'plugins_url',
													'admin'
												)}
												target="_blank"
												rel="noreferrer"
											/>
										),
									})}
								/>
							)}

							{Object.keys(plugins).length > 0 && (
								<div className="wds-confl-plug-list">
									{Object.keys(plugins).map((plugin) => (
										<ConflictingPluginItem
											key={plugin}
											plugin={plugin}
											{...plugins[plugin]}
											onDeactivate={(plg) =>
												this.handleDeactivate(plg)
											}
											onError={(plg, err) =>
												this.handleError(plg, err)
											}
											onProgress={(val) =>
												this.handleProgress(val)
											}
										></ConflictingPluginItem>
									))}
								</div>
							)}
						</>
					</Modal>
				)}
			</>
		);
	}
}
