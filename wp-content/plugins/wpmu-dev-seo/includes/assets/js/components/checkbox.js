import React from 'react';

export default class Checkbox extends React.Component {
	render() {
		const validProps = [
			'id',
			'name',
			'checked',
			'defaultChecked',
			'disabled',
			'readOnly',
			'className',
		];

		const props = Object.keys(this.props)
			.filter((propName) => validProps.includes(propName))
			.reduce((obj, propName) => {
				obj[propName] = this.props[propName];
				return obj;
			}, {});

		if (this.props.onChange) {
			props.onChange = (e) => this.props.onChange(e.target.checked);
		}

		return (
			<label className="sui-checkbox">
				<input type="checkbox" {...props} />
				<span aria-hidden="true" />
				{this.props.label && <span>{this.props.label}</span>}
			</label>
		);
	}
}
