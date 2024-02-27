import React from 'react';
import RedirectTable from './redirects/redirect-table';
import RedirectSettings from './redirects/redirect-settings';
import RedirectDeactivated from './redirects/redirect-deactivated';
import ConfigValues from '../../es6/config-values';
import { getDefaultType } from '../../utils/redirect-utils';
import { createStore } from 'redux';
import reducer from './redirects/reducers/redirect-reducer';
import { Provider } from 'react-redux';

const active = ConfigValues.get('active', 'redirects');

export default class Redirects extends React.Component {
	toggleMaxmindActivation(maxmindKey) {
		this.setState({ maxmindKey });
	}

	handleDefaultTypeChange(defaultType) {
		this.setState({ defaultType });
	}

	render() {
		if (!active) {
			return <RedirectDeactivated />;
		}

		const defaultType =
			ConfigValues.get('default_type', 'redirects') || getDefaultType();

		const store = createStore(reducer, {
			id: '',
			source: '',
			destination: '',
			dstDisabled: false,
			rules: [],
			ruleKeys: [],
			title: '',
			options: [],
			type: defaultType,
			valid: false,
			loading: false,
			maxmindKey: ConfigValues.get('maxmind_license', 'redirects'),
			defaultType,
			deletingRule: false,
		});

		return (
			<Provider store={store}>
				<RedirectTable />
				<RedirectSettings />
			</Provider>
		);
	}
}
