import React from 'react';
import classnames from 'classnames';

export default class TextareaInput extends React.Component {
	static defaultProps = {
		id: '',
		name: '',
		value: '',
		placeholder: '',
		disabled: false,
		rows: 0,
		className: '',
	};

	constructor(props) {
		super(props);

		this.state = { value: this.props.value };
	}

	// eslint-disable-next-line no-unused-vars
	componentDidUpdate(prevProps, prevState, snapshot) {
		if (prevProps.value !== this.props.value) {
			this.setState({ value: this.props.value });
		}
	}

	handleChange(e) {
		if (this.props.onChange) {
			this.props.onChange(e.target.value);
		}

		this.setState({ value: e.target.value });
	}

	render() {
		const { id, name, placeholder, disabled, rows, className } = this.props;
		const { value } = this.state;

		const props = { name, placeholder, disabled, value };

		if (id) {
			props.id = id;
		}

		if (rows) {
			props.rows = rows;
		}

		return (
			<textarea
				{...props}
				className={classnames('sui-form-control', className)}
				onChange={(e) => this.handleChange(e)}
			/>
		);
	}
}
