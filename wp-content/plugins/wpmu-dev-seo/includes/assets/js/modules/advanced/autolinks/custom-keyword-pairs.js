import React from 'react';
import Button from '../../../components/button';
import Dropdown from '../../../components/dropdown';
import DropdownButton from '../../../components/dropdown-button';
import Pagination from '../../../components/navigations/pagination';
import PaginationUtil from '../../../utils/pagination-util';
import { __ } from '@wordpress/i18n';
import CustomKeywordModal from './custom-keyword-modal';
import update from 'immutability-helper';
import SettingsRow from '../../../components/settings-row';
import ConfigValues from '../../../es6/config-values';

const optName = ConfigValues.get('option_name', 'autolinks');

export default class CustomKeywordPairs extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			pairs: this.textToPairs(
				ConfigValues.get('customkey', 'autolinks') || ''
			),
			addingPair: false,
			editingPair: false,
			currentPageNumber: 1,
		};

		this.pairsPerPage = 10;
	}

	render() {
		const pairs = this.state.pairs;
		const pairsCount = this.objectLength(pairs);
		const pairsExist = pairsCount > 0;
		const page = this.getPairsPage();

		return (
			<SettingsRow
				label={__('Custom Links', 'wds')}
				description={__(
					'Choose additional custom keywords you want to target, and where to link them to.',
					'wds'
				)}
				direction="column"
			>
				<React.Fragment>
					{pairsExist && (
						<table className="wds-keyword-pairs sui-table">
							<tbody>
								<tr>
									<th>{__('Keyword', 'wds')}</th>
									<th colSpan="2">
										{__(
											'Auto-Linked URL',
											'wds'
										)}
									</th>
								</tr>

								{Object.keys(page).map((key) => {
									const pair = page[key];
									return (
										<tr key={key}>
											<td>{pair.keyword}</td>
											<td>
												<a
													href={this.getAbsoluteUrl(
														pair.url
													)}
													title={pair.url}
												>
													{pair.url}
												</a>
											</td>
											<td>
												<Dropdown
													buttons={[
														<DropdownButton
															key={0}
															onClick={() =>
																this.startEditingPair(
																	key
																)
															}
															icon="sui-icon-pencil"
															text={__(
																'Edit',
																'wds'
															)}
														/>,
														<DropdownButton
															key={1}
															onClick={() =>
																this.deletePair(
																	key
																)
															}
															icon="sui-icon-trash"
															text={__(
																'Delete',
																'wds'
															)}
															red={true}
														/>,
													]}
												/>

												{this.state.editingPair ===
													key && (
													<CustomKeywordModal
														keyword={pair.keyword}
														url={pair.url}
														editMode={true}
														onClose={() =>
															this.stopEditingPair()
														}
														onSave={(
															keyword,
															url
														) =>
															this.editPair(
																key,
																keyword,
																url
															)
														}
													/>
												)}
											</td>
										</tr>
									);
								})}
							</tbody>
						</table>
					)}

					<div className="wds-keyword-pairs-actions">
						<div className="wds-keyword-pair-new">
							<Button
								id="wds-keyword-pair-new-button"
								icon="sui-icon-plus"
								onClick={() => this.startAddingPair()}
								text={__('Add Link', 'wds')}
							/>
						</div>

						<React.Fragment>
							{pairsCount > this.pairsPerPage && (
								<Pagination
									count={pairsCount}
									currentPage={this.state.currentPageNumber}
									perPage={this.pairsPerPage}
									onClick={(pageNumber) =>
										this.changePage(pageNumber)
									}
								/>
							)}
						</React.Fragment>
					</div>

					<textarea
						name={`${optName}[customkey]`}
						style={{ display: 'none' }}
						value={this.pairsToText()}
						readOnly={true}
					/>

					{this.state.addingPair && (
						<CustomKeywordModal
							onClose={() => this.stopAddingPair()}
							onSave={(keyword, url) =>
								this.addPair(keyword, url)
							}
						/>
					)}
				</React.Fragment>
			</SettingsRow>
		);
	}

	objectLength(collectionObject) {
		return Object.keys(collectionObject).length;
	}

	changePage(pageNumber) {
		this.setState({ currentPageNumber: pageNumber });
	}

	getPairsPage() {
		return PaginationUtil.getPage(
			this.state.pairs,
			this.state.currentPageNumber,
			this.pairsPerPage
		);
	}

	getAbsoluteUrl(url) {
		if (url.indexOf('://') > 0 || url.indexOf('//') === 0) {
			return url;
		}
		const homeUrl = ConfigValues.get('home_url', 'autolinks');
		// Remove leading slash and append to home url.
		return homeUrl + url.replace(/^\/|\/$/g, '');
	}

	textToPairs(text) {
		const lines = text.split(/\n/);
		const pairs = [];
		lines.forEach((line) => {
			if (!line.includes(',')) {
				return;
			}
			const parts = line.split(',').map((part) => part.trim());
			pairs.push({
				keyword: parts.slice(0, -1).join(','),
				url: parts.slice(-1).pop(),
			});
		});

		return pairs;
	}

	pairsToText() {
		const lines = [];
		this.state.pairs.forEach((pair) => {
			const keyword = pair.keyword?.trim();
			const url = pair.url?.trim();

			if (keyword && url) {
				lines.push(keyword + ',' + url);
			}
		});

		return lines.join('\n');
	}

	startEditingPair(index) {
		this.setState({
			editingPair: index,
		});
	}

	editPair(index, keyword, url) {
		if (!keyword.trim() || !url.trim()) {
			return;
		}

		const pairs = this.state.pairs.slice();

		pairs[index] = {
			keyword,
			url,
		};

		this.setState({
			pairs,
			editingPair: false,
		});
	}

	stopEditingPair() {
		this.setState({
			editingPair: false,
		});
	}

	startAddingPair() {
		this.setState({
			addingPair: true,
		});
	}

	addPair(keyword, url) {
		if (!keyword.trim() || !url.trim()) {
			return;
		}

		const pairs = this.state.pairs.slice();

		pairs.splice(0, 0, {
			keyword,
			url,
		});

		this.setState({
			pairs: update(this.state.pairs, { $set: pairs }),
			addingPair: false,
			currentPageNumber: 1,
		});
	}

	stopAddingPair() {
		this.setState({
			addingPair: false,
		});
	}

	deletePair(index) {
		const pairs = this.state.pairs.filter(
			(pair, idx) => idx !== parseInt(index)
		);
		this.setState({
			pairs,
		});
	}
}
