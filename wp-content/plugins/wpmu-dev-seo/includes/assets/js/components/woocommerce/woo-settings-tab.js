import React from 'react';
import { __ } from '@wordpress/i18n';
import SettingsRow from '../settings-row';
import Toggle from '../toggle';
import { createInterpolateElement } from '@wordpress/element';
import DisabledComponent from '../disabled-component';
import Button from '../button';
import SelectField from '../form-fields/select-field';
import Notice from '../notices/notice';
import ConfigValues from '../../es6/config-values';

const optName = ConfigValues.get('option_name', 'woo');
const active = ConfigValues.get('active', 'woo');
const schemaAllowed = ConfigValues.get('schema_allowed', 'woo');
const schemaEnabled = ConfigValues.get('schema_enabled', 'woo');
const socialAllowed = ConfigValues.get('social_allowed', 'woo');
const ogEnabled = ConfigValues.get('og_enabled', 'woo');

export default class WooSettingsTab extends React.Component {
	render() {
		return (
			<div className="sui-box">
				<div className="sui-box-header">
					<h2 className="sui-box-title">
						{__('WooCommerce SEO', 'wds')}
					</h2>
				</div>

				<div className="sui-box-body">
					{active
						? this.renderActivation()
						: this.renderDeactivation()}
				</div>

				{active && (
					<>
						<div className="sui-box-footer">
							<Button
								name={`${optName}[active]`}
								value="0"
								text={__('Deactivate', 'wds')}
								icon="sui-icon-power-on-off"
								ghost={true}
							/>

							<Button
								type="submit"
								color="blue"
								icon="sui-icon-save"
								text={__('Save Settings', 'wds')}
							/>
						</div>
					</>
				)}
			</div>
		);
	}

	renderActivation() {
		return (
			<>
				<p>
					{__(
						'Use the WooCommerce SEO configurations below to add recommended Woo metadata and Product Schema to your WooCommerce site, helping you stand out in search results pages.',
						'wds'
					)}
				</p>

				<SettingsRow
					label={__('Improve Woo Schema', 'wds')}
					description={__(
						"Improve your site's WooCommerce Schema.",
						'wds'
					)}
				>
					{schemaAllowed && !schemaEnabled && (
						<Notice
							message={createInterpolateElement(
								__(
									'For these settings to be applied, the <a>Schema module</a> must first be enabled.',
									'wds'
								),
								{
									a: (
										<a
											target="_blank"
											href={ConfigValues.get(
												'schema_url',
												'woo'
											)}
											rel="noreferrer"
										/>
									),
								}
							)}
						/>
					)}

					<SelectField
						label={__('Brand', 'wds')}
						description={__(
							'Select the product taxonomy to use as Brand in Schema & OpenGraph markup.',
							'wds'
						)}
						name={`${optName}[brand]`}
						options={ConfigValues.get('brand_opts', 'woo')}
						selectedValue={ConfigValues.get('brand', 'woo')}
					/>

					<SelectField
						label={__('Global Identifier', 'wds')}
						description={createInterpolateElement(
							__(
								'Global Identifier key to use in the Product Schema. You can add a Global Identifier value for each product in the <strong>Inventory</strong> section of the <strong>Product Metabox</strong>',
								'wds'
							),
							{
								strong: <strong />,
							}
						)}
						options={{
							'': __('None', 'wds'),
							gtin8: 'GTIN-8',
							gtin12: 'GTIN-12',
							gtin13: 'GTIN-13',
							gtin14: 'GTIN-14',
							isbn: 'ISBN',
							mpn: 'MPN',
						}}
						name={`${optName}[global_id]`}
						selectedValue={ConfigValues.get('global_id', 'woo')}
					/>

					{schemaAllowed && (
						<Toggle
							label={__('Enable Shop Schema', 'wds')}
							description={__(
								'Add schema data on the shop page.',
								'wds'
							)}
							name={`${optName}[shop_schema]`}
							disabled={!schemaEnabled}
							checked={ConfigValues.get('shop_schema', 'woo')}
							wrapped={true}
							wrapperClass="sui-form-field"
						/>
					)}
				</SettingsRow>

				<SettingsRow
					label={__('Improve Woo Meta', 'wds')}
					description={__(
						"Improve your site's default WooCommerce Meta.",
						'wds'
					)}
				>
					{socialAllowed && !ogEnabled && (
						<Notice
							message={createInterpolateElement(
								__(
									'For these settings to be applied, OpenGraph Support must first be <a>enabled</a>.',
									'wds'
								),
								{
									a: (
										<a
											target="_blank"
											href={ConfigValues.get(
												'social_url',
												'woo'
											)}
											rel="noreferrer"
										/>
									),
								}
							)}
						/>
					)}

					{socialAllowed && (
						<Toggle
							label={__(
								'Enable Product Open Graph',
								'wds'
							)}
							description={__(
								'If enabled, WooCommerce product data will be added to Open Graph.',
								'wds'
							)}
							name={`${optName}[enable_og]`}
							disabled={!ogEnabled}
							checked={ConfigValues.get('enable_og', 'woo')}
							wrapped={true}
							wrapperClass="sui-form-field"
						/>
					)}

					<Toggle
						label={__('Remove Generator Tag', 'wds')}
						description={createInterpolateElement(
							__(
								'If enabled, the WooCommerce generator tag <strong><meta name="generator" content="WooCommerce x.x.x" /></strong> will be removed.',
								'wds'
							),
							{ strong: <strong /> }
						)}
						name={`${optName}[rm_gen_tag]`}
						checked={ConfigValues.get('rm_gen_tag', 'woo')}
						wrapped={true}
						wrapperClass="sui-form-field"
					/>
				</SettingsRow>

				<SettingsRow
					label={__('Restrict Search Engines', 'wds')}
					description={__(
						'Use these options to restrict Indexing or crawling of specific pages on the site.',
						'wds'
					)}
				>
					<Toggle
						label={__('Noindex Hidden Products', 'wds')}
						description={__(
							'Set Product Pages to noindex when WooCommerce Catalog visibility is set to hidden.',
							'wds'
						)}
						name={`${optName}[noindex_hidden_prod]`}
						checked={ConfigValues.get('noindex_hidden_prod', 'woo')}
						wrapped={true}
						wrapperClass="sui-form-field"
					/>

					<Toggle
						label={__(
							'Disallow Crawling of Cart, Checkout & My Account Pages',
							'wds'
						)}
						description={createInterpolateElement(
							__(
								'If enabled, the following will be added to your Robots.txt file:<br/><strong>Disallow: /*add-to-cart=*<br/>Disallow: /cart/<br/>Disallow: /checkout/<br/>Disallow: /my-account/</strong>',
								'wds'
							),
							{ strong: <strong />, br: <br /> }
						)}
						name={`${optName}[add_robots]`}
						checked={ConfigValues.get('add_robots', 'woo')}
						wrapped={true}
						wrapperClass="sui-form-field"
					/>
				</SettingsRow>
			</>
		);
	}

	renderDeactivation() {
		return (
			<DisabledComponent
				message={__(
					'Activate WooCommerce SEO to add the required metadata and Product Schema to your WooCommerce site, helping you stand out in search results.',
					'wds'
				)}
				nonceFields={false}
				inner
				button={
					<Button
						name={`${optName}[active]`}
						value="1"
						color="blue"
						text={__('Activate', 'wds')}
					/>
				}
			/>
		);
	}
}
