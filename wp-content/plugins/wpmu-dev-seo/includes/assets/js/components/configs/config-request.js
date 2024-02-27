import ConfigValues from '../../es6/config-values';
import RequestUtil from '../../utils/request-util';

export default class ConfigRequest {
	static sync() {
		return this.post('wds_sync_hub_configs');
	}

	static applyConfig(configId) {
		return this.post('wds_apply_config', { config_id: configId });
	}

	static deleteConfig(configId) {
		return this.post('wds_delete_config', { config_id: configId });
	}

	static updateConfig(configId, configName, configDescription) {
		return this.post('wds_update_config', {
			config_id: configId,
			name: configName,
			description: configDescription,
		});
	}

	static createConfig(configName, configDescription) {
		return this.post('wds_create_new_config', {
			name: configName,
			description: configDescription,
		});
	}

	static uploadConfig(file) {
		return RequestUtil.uploadFile(
			'wds_upload_config',
			ConfigValues.get('nonce', 'config'),
			file
		);
	}

	static post(action, data) {
		const nonce = ConfigValues.get('nonce', 'config');
		return RequestUtil.post(action, nonce, data);
	}
}
