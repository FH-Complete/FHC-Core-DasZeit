/**
 * Copyright (C) 2023 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

//import {CoreFilterCmpt} from '../../../../../js/components/filter/Filter.js';
import FhcLoader from '../../../../js/components/Loader.js';
import ApiMitarbeiterSync from '../api/factory/Mitarbeitersync.js';
import searchbar from "../../../../js/components/searchbar/searchbar.js";
//import {searchbaroptions} from "../apps/common.js";
import ApiSearchbar from  '../../../../js/api/factory/searchbar.js';
//import studiensemester from './studiensemester/Studiensemester.js';

export const Mitarbeitersync = {
	components: {
		FhcLoader,
		searchbar,
		//searchbaroptions,
		ApiSearchbar
		//CoreFilterCmpt,
		//studiensemester,
	},
	props: {
		modelValue: Object,
		default: null
	},
	data: function() {
		return {
			protocol_host: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router,
			searchMitarbeiterUid: null,
			mitarbeiter: {},
			dasZeitMitarbeiter: {},
			dasZeitMessage: null,
			searchbaroptions: {
				//cssclass: "position-relative",
				calcheightonly: true,
				origin: 'dasZeit',
				types: [
					"person",
					"mitarbeiter"
				],
				actions: {
					person: {
						defaultaction: {
							type: "link",
							action: function(data) { 
								return data.profil;
							}
						},
						childactions: [
						]
					},
					employee: {
						defaultaction: {
							type: "function",
							action: (data) => {
								this.searchMitarbeiterUid = data.uid;
								this.getMitarbeiterData();
							}
							//~ type: "link",
							//~ action: (data) => {
								//~ this.searchMitarbeiterUid = data.uid;
								//~ //return this.protocol_host + `/extensions/FHC-Core-Personalverwaltung/Employees/${data.person_id}/${data.uid}/summary`;
							//~ }
						},
						childactions: [
						]
					}
				}
			}
		};
	},
	methods: {
		getMitarbeiterData() {
			this.getMitarbeiterByUid();
			this.getDasZeitMitarbeiterByUid();
		},
		getMitarbeiterByUid() {
			let mitarbeiter_uid = this.modelValue != null && this.modelValue.personUID ? this.modelValue.personUID : this.searchMitarbeiterUid;

			if (mitarbeiter_uid != null)
			{
				this.$refs.loader.show();
				this.$api
				.call(ApiMitarbeiterSync.getMitarbeiterByUid(mitarbeiter_uid))
				.then((response) => {
					this.mitarbeiter = response.data;

					// hide loading
					this.$refs.loader.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
			}
		},
		getDasZeitMitarbeiterByUid() {
			let mitarbeiter_uid = this.modelValue != null && this.modelValue.personUID ? this.modelValue.personUID : this.searchMitarbeiterUid;

			if (mitarbeiter_uid != null)
			{
				this.$refs.loader.show();
				this.$api
				.call(ApiMitarbeiterSync.getDasZeitMitarbeiterByUid(mitarbeiter_uid))
				.then((response) => {
					if (typeof response.data === 'string' || response.data instanceof String)
					{
						this.dasZeitMessage = response.data
						this.dasZeitMitarbeiter = {};
					}
					else if (typeof response.data === 'object')
					{
						this.dasZeitMitarbeiter = response.data;
						this.dasZeitMessage = null;
					}

					// hide loading
					this.$refs.loader.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
			}
		},
		searchfunction(params, config) {
			return this.$api.call(ApiSearchbar.search(params), config);
		}
	},
	mounted: function() {
		this.getMitarbeiterData();
	},
	template: `
		<div>
			<header>
				<h1 class="h2 fhc-hr">{{$p.t('dasZeit', 'mitarbeiterSync')}}</h1>
			</header>
			<!-- input fields -->
			<div class="row" v-if="modelValue == null || !modelValue.personUID">
				<div class="col-12">
					<!-- employee search -->
					<searchbar
						ref="searchbar"
						:searchoptions="searchbaroptions"
						:searchfunction="searchfunction"
						class="searchbar position-relative w-100"
						show-btn-submit
					></searchbar>
				</div>
			</div>
			<br />
			<!-- Sync component -->
			<div class="row">
				<div class="col">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th colspan="2" class="text-center">FHComplete</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(value, key) in mitarbeiter">
								<td>{{key}}</td>
								<td>{{value}}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th colspan="2" class="text-center">DasZeit</th>
							</tr>
						</thead>
						<tbody>
							<tr v-if="dasZeitMessage != null">
								<td class="text-center">{{dasZeitMessage}}</td>
							</tr>
							<tr v-for="(value, key) in dasZeitMitarbeiter">
								<td>{{key}}</td>
								<td>{{value}}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col text-center">
					<button class="btn btn-primary">{{$p.t('dasZeit', 'synchronisieren')}} <i class="bi bi-arrow-repeat"></i></button>
				</div>
			</div>
			<fhc-loader ref="loader"></fhc-loader>
		</div>`
};

export default Mitarbeitersync;

