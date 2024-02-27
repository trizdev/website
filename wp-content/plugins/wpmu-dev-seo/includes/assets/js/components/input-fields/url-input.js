import React from 'react';
import $ from 'jQuery';
import ConfigValues from '../../es6/config-values';
import classnames from 'classnames';
import { __ } from '@wordpress/i18n';
import { isUrlValid } from '../../utils/validators';

const restURL = ConfigValues.get('rest_url', 'admin');
const postTypes = ConfigValues.get('post_types', 'admin');

export default class UrlInput extends React.Component {
	static defaultProps = {
		onSelect: () => false,
	};

	constructor(props) {
		super(props);

		this.state = {
			value: '',
			results: [],
			selected: this.props.value,
			loading: false,
			showResults: false,
		};

		this.componentRef = React.createRef();
	}

	componentDidMount() {
		document.addEventListener('click', this.handleClickOutside);
	}

	componentWillUnmount() {
		document.removeEventListener('click', this.handleClickOutside);
	}

	handleClickOutside = (event) => {
		if (!this.componentRef.current) {
			return;
		}

		if (!this.componentRef.current.contains(event.target)) {
			this.setState({ showResults: false });
		}
	};

	handleChange(value) {
		this.setState({ value });

		if (value.length < 2) {
			return;
		}

		this.setState({ loading: true, results: [] });

		if (this.xhr) {
			this.xhr.abort();
		}

		this.xhr = $.ajax({
			url: restURL + 'wp/v2/search',
			type: 'GET',
			data: {
				search: value,
				per_page: 5,
				type: 'post',
				_locale: 'user',
			},
			beforeSend: (xhr) => {
				xhr.setRequestHeader('Accept', 'application/json,*.*;q=0.1');
			},
			success: (data) => {
				const results = data.map((d) => {
					return {
						id: d.id,
						type: postTypes[d.subtype],
						_type: d.subtype,
						title: d.title,
						url: d.url,
					};
				});

				if (!results.length && isUrlValid(value)) {
					results.push({
						url: value,
						type: __('URL', 'wds'),
					});
				}

				this.setState({ results, showResults: true, loading: false });
			},
		});
	}

	handleClickInput() {
		this.setState({ showResults: true });
	}

	setSelected(selected) {
		this.setState({ selected, value: '' });
		this.props.onSelect(selected);
	}

	unsetSelected() {
		this.setState({ selected: false });
		this.props.onSelect('');
	}

	render() {
		return (
			<div className="wds-url-input-wrapper" ref={this.componentRef}>
				{this.renderInner()}
			</div>
		);
	}

	renderInner() {
		const { selected } = this.state;

		if (selected) {
			return (
				<>
					<div className="wds-url-input-selected sui-form-control">
						<span>
							{selected.title ? selected.title : selected.url}
						</span>

						<button
							className="sui-button-icon"
							onClick={() => this.unsetSelected()}
						>
							<span
								className="sui-icon-close"
								aria-hidden="true"
							/>
						</button>
					</div>
				</>
			);
		}

		const validProps = [
			'id',
			'name',
			'type',
			'placeholder',
			'disabled',
			'readOnly',
			'className',
		];
		const inputProps = Object.keys(this.props)
			.filter((propName) => validProps.includes(propName))
			.reduce((obj, propName) => {
				obj[propName] = this.props[propName];
				return obj;
			}, {});

		inputProps.value = this.state.value;

		return (
			<>
				<input
					{...inputProps}
					className={classnames(
						'wds-url-input',
						'sui-form-control',
						this.props.className
					)}
					onChange={(e) => this.handleChange(e.target.value)}
					onClick={() => this.handleClickInput()}
				/>
				{this.renderSearchResults()}
			</>
		);
	}

	renderSearchResults() {
		const { loading } = this.state;

		if (loading) {
			return (
				<div className="wds-url-search-results">
					<p className="wds-url-search-loading">
						<span
							className="sui-icon-loader sui-loading"
							aria-hidden="true"
						/>{' '}
						{__('Searching…', 'wds')}
					</p>
				</div>
			);
		}

		const { value, showResults } = this.state;

		if (value.length < 2 || !showResults) {
			return '';
		}

		const { results } = this.state;

		if (results.length) {
			return (
				<div className="wds-url-search-results">
					{results.map((item, index) =>
						this.renderSearchItem(item, index)
					)}
				</div>
			);
		}

		if (value[0] === '/') {
			return '';
		}

		return (
			<div className="wds-url-search-results">
				<p className="wds-url-search-no-result">
					{__('No results found', 'wds')}
				</p>
			</div>
		);
	}

	renderSearchItem(item, index) {
		return (
			<div
				key={index}
				className="wds-url-search-item"
				onClick={() => this.setSelected(item)}
			>
				<div className="wds-url-search-item-info">
					<div className="wds-url-search-item-title">
						{item.title || item.url}
					</div>
					{!!item.url && (
						<div className="wds-url-search-item-url">
							{item.url}
						</div>
					)}
				</div>
				<span className="wds-url-search-item-shortcut">
					{item.type}
				</span>
			</div>
		);
	}
}
