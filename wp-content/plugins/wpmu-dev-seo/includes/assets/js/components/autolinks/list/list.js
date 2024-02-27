import React from 'react';
import { __ } from '@wordpress/i18n';
import ListItem from './list-item';
import Notice from '../../notices/notice';
import Button from '../../button';
import update from 'immutability-helper';

export default class List extends React.Component {
	static defaultProps = {
		items: [],
		posts: {},
		types: {},
		loading: false,
		onRemove: () => false,
		onAdd: () => false,
	};

	render() {
		const {
			items,
			posts,
			types,
			loading,
			onRemove,
			onAdd
		} = this.props;

		return (
			<div className="wds-postlist-list">
				<>
					{!!items.length && (
						<table className="wds-postlist sui-table">
							<thead>
							<tr>
								<th>{__('Posts/Pages/URLs', 'wds-texdomain')}</th>
								<th colSpan="2">
									{__('Type', 'wds-texdomain')}
								</th>
							</tr>
							</thead>
							<tbody>
							{loading ? (
								<tr>
									<td colSpan="3">
										<small>
											<i>{__('Loading posts and URLs, please hold on', 'wds-texdomain')}</i>
										</small>
									</td>
								</tr>
							) : (
								items.map((item, index) => {
									let id = parseInt(item)
									if (id && posts.hasOwnProperty(id)) {
										let post = posts[id]
										return (
											<ListItem
												key={index}
												label={post.title}
												typeLabel={types[post.type]}
												onRemove={() => onRemove(item, post.type)}
											/>
										)
									} else {
										return (
											<ListItem
												key={index}
												label={item}
												typeLabel={types['url']}
												onRemove={() => onRemove(item, 'url')}
											/>
										)
									}
								})
							)}
							</tbody>
						</table>
					)}

					{!items.length && (
						<Notice
							type=""
							message={__(
								"You haven't chosen to exclude any posts/pages.",
								'wds-texdomain'
							)}
						/>
					)}

					<Button
						id="wds-postlist-selector-open"
						icon="sui-icon-plus"
						text={__('Add Exclusion', 'wds-texdomain')}
						onClick={() => onAdd()}
					/>
				</>
			</div>
		);
	}
}
