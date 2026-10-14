export default {
	getMitarbeiterByUid(mitarbeiter_uid) {
		return {
			method: 'get',
			url: 'extensions/FHC-Core-DasZeit/api/frontend/v1/MitarbeiterSync/getMitarbeiterByUid',
			params: { mitarbeiter_uid }
		};
	},
	getDasZeitMitarbeiterByUid(mitarbeiter_uid) {
		return {
			method: 'get',
			url: 'extensions/FHC-Core-DasZeit/api/frontend/v1/MitarbeiterSync/getDasZeitMitarbeiterByUid',
			params: { mitarbeiter_uid }
		};
	},
	getAllEntities() {
		return {
			method: 'get',
			url: 'extensions/FHC-Core-DasZeit/api/frontend/v1/MitarbeiterSync/getAllEntities'
		};
	}
};
