import React from 'react';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import VerticalTab from '../../components/vertical-tab';
import UrlUtil from '../../utils/url-util';
import Notice from '../../components/notices/notice';
import AutolinkTypes from './autolinks/autolink-types';
import CustomKeywordPairs from './autolinks/custom-keyword-pairs';
import ExcludedPosts from './autolinks/excluded-posts';
import Settings from './autolinks/settings';
import Deactivate from '../../components/deactivate';
import DisabledComponent from '../../components/disabled-component';
import Button from '../../components/button';
import ConfigValues from '../../es6/config-values';
import Tabs from '../../components/tabs';
import SettingsRow from '../../components/settings-row';

const optName = ConfigValues.get('option_name', 'autolinks');
const active = ConfigValues.get('active', 'autolinks');

export default class Autolinks extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			selectedTab: !!UrlUtil.getQueryParam('sub')
				? UrlUtil.getQueryParam('sub')
				: 'post_types',
		};
	}

	render() {
		const isActive =
			!UrlUtil.getQueryParam('tab') ||
			UrlUtil.getQueryParam('tab') === 'tab_automatic_linking';

		return (
			<VerticalTab
				id="tab_automatic_linking"
				title={__('Automatic Linking', 'wds')}
				actionsLeft={this.renderTag()}
				children={active ? this.getSettings() : this.getDeactivated()}
				buttonText={active && __('Save Settings', 'wds')}
				isActive={isActive}
			></VerticalTab>
		);
	}

	renderTag() {
		const isMember = ConfigValues.get('is_member', 'admin') === '1';

		if (isMember) {
			return '';
		}

		return (
			<a
				target="_blank"
				href="https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_autolinking_pro_tag"
				rel="noreferrer"
			>
				<span
					className="sui-tag sui-tag-pro sui-tooltip"
					data-tooltip={__(
						'Upgrade to SmartCrawl Pro',
						'wds'
					)}
				>
					{__('Pro', 'wds')}
				</span>
			</a>
		);
	}

	getSettings() {
		return [
			<p key={0}>
				{__(
					'SmartCrawl will look for keywords that match posts/pages around your website and automatically link them. Specify what post types you want to include in this tool, and what post types you want those to automatically link to.',
					'wds'
				)}
			</p>,

			<Notice
				key={1}
				type=""
				message={createInterpolateElement(
					__(
						'Certain page builders and themes can interfere with the auto linking feature causing issues on your site. Enable the "<strong>Prevent caching on auto-linked content</strong>" option in the Settings tab section to fix the issues.',
						'wds'
					),
					{
						strong: <strong />,
					}
				)}
			></Notice>,
			<SettingsRow key={2} direction="column">
				<Tabs
					tabs={{
						post_types: {
							label: __('Post Types', 'wds'),
							component: <AutolinkTypes />,
						},
						custom_links: {
							label: __('Custom Links', 'wds'),
							component: <CustomKeywordPairs />,
						},
						exclusions: {
							label: __('Exclusions', 'wds'),
							component: <ExcludedPosts />,
						},
						settings: {
							label: __('Settings', 'wds'),
							component: <Settings />,
						},
					}}
					value={this.state.selectedTab}
					onChange={(tab) => this.handleTabChange(tab)}
				></Tabs>
			</SettingsRow>,
			<Deactivate
				key={3}
				description={__(
					'No longer need keyword linking? This will deactivate this feature but won’t remove existing links.',
					'wds'
				)}
				name={`${optName}[active]`}
			></Deactivate>,
		];
	}

	handleTabChange(tab) {
		const urlParts = location.href.split('&sub=');

		history.replaceState({}, '', urlParts[0] + '&sub=' + tab);

		event.preventDefault();
		event.stopPropagation();

		this.setState({
			selectedTab: tab,
		});
	}

	getDeactivated() {
		return (
			<>
				<DisabledComponent
					message={__(
						'Configure SmartCrawl to automatically link certain key words to a page on your blog or even a whole new site all together. Internal linking can help boost SEO by giving search engines ample ways to index your site.',
						'wds'
					)}
					premium={true}
					upgradeTag="smartcrawl_autolinking_upgrade_button"
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
			</>
		);
	}
}
