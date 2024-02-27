import React from 'react';
import { __ } from '@wordpress/i18n';
import update from 'immutability-helper';
import ConfigValues from '../../../es6/config-values';
import SettingsRow from '../../../components/settings-row';
import TextareaInputField from '../../../components/form-fields/textarea-input-field';
import RequestUtil from '../../../utils/request-util';
import NoticeUtil from '../../../utils/notice-util';
import List from '../../../components/autolinks/list/list';
import ExclusionModal from './exclusion-modal';
import FloatingNoticePlaceholder from '../../../components/floating-notice-placeholder';

const nonce = ConfigValues.get('nonce', 'admin');

export default class ExcludedPosts extends React.Component {
	constructor(props) {
		super(props);

		const exclusions = ConfigValues.get('ignorepost', 'autolinks')
			.split(',')
			.filter((excl) => !!excl)
			.map((excl) => excl.trim());

		this.state = {
			loading: false,
			openDialog: false,
			exclusions,
			excludedPosts: [],
			excludedUrls: ConfigValues.get('excluded_urls', 'autolinks'),
		};
	}

	componentDidMount() {
		this.loadPosts();
	}

	render() {
		const { exclusions, excludedPosts, loading, openDialog } = this.state;

		const optName = ConfigValues.get('option_name', 'autolinks'),
			postTypes = ConfigValues.get('post_types', 'admin');

		return (
			<SettingsRow
				label={__('Exclusions', 'wds')}
				description={__(
					'Provide a comma-separated list of keywords that you would like to exclude. You can also select individual posts/pages/URLs for exclusion.',
					'wds'
				)}
				direction="column"
			>
				<FloatingNoticePlaceholder id="wds-postlist-notice" />

				<TextareaInputField
					id="ignore"
					label={__('Excluded Keywords', 'wds')}
					name={`${optName}[ignore]`}
					value={ConfigValues.get('ignore', 'autolinks')}
					placeholder={__('eg: SEO', 'wds')}
				></TextareaInputField>

				<label className="sui-label">
					{__('Excluded Posts/Pages/URLs', 'wds-texdomain')}
				</label>

				<List
					items={exclusions}
					posts={excludedPosts}
					loading={loading}
					types={postTypes}
					onRemove={(value, type) => this.handleRemove(value, type)}
					onAdd={() => this.setState({ openDialog: true })}
				/>

				<input
					name={optName + '[ignorepost]'}
					type="hidden"
					value={exclusions.join(',')}
				/>

				{openDialog && (
					<ExclusionModal
						id="wds-postlist-selector"
						postTypes={postTypes}
						onPostsUpdate={(posts) => this.handlePostsUpdate(posts)}
						onSubmit={(values, type) =>
							this.handleItemsAdd(values, type)
						}
						onClose={() => this.toggleModal()}
					/>
				)}
			</SettingsRow>
		);
	}

	handleRemove(item) {
		const index = this.state.exclusions.indexOf(item);
		this.setState({
			exclusions: update(this.state.exclusions, {
				$splice: [[index, 1]],
			}),
		});
	}

	loadPosts() {
		const { exclusions } = this.state;

		this.setState({ loading: true });

		const ids = exclusions.filter((item) => !isNaN(item));
		RequestUtil.post('smartcrawl_get_posts_by_ids', nonce, {
			posts: ids,
		})
			.then((data) => {
				this.setState({
					excludedPosts: data.posts,
					loading: false,
				});
			})
			.catch((error) => {
				NoticeUtil.showErrorNotice(
					'wds-postlist-notice',
					error ||
						__(
							'An error occurred. Please try again.',
							'wds'
						),
					false
				);

				this.setState({ loading: false });
			});
	}

	handleItemsAdd(values, type) {
		let { exclusions } = this.state;

		values.forEach((value) => {
			const val = 'url' === type ? value : parseInt(value);
			if (exclusions.indexOf(val) === -1) {
				exclusions = update(exclusions, { $push: [val] });
			}
		});

		this.setState({
			exclusions,
			openDialog: false,
		});
	}

	handlePostsUpdate(updatablePosts) {
		let { excludedPosts } = this.state;

		if (!excludedPosts) {
			excludedPosts = {};
		}

		const ids = excludedPosts ? Object.keys(excludedPosts) : [];

		updatablePosts.forEach((post) => {
			if (ids.indexOf(post.id) === -1) {
				excludedPosts[post.id] = post;
			}
		});

		this.setState({ excludedPosts });
	}

	toggleModal() {
		this.setState({ openDialog: !this.state.openDialog });
	}
}
