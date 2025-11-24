        function generateOtherInfoStep(applicant) {
            const oi = applicant.otherInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">List of Languages Spoken</label>
                        <textarea name="oi_spoken_language_list" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                rows="3"
                                onchange="updateApplicantData('otherInfo', 'oi_spoken_language_list', this.value)">${oi.oi_spoken_language_list || ''}</textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you traveled to any countries in the last five years?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_travel_country_5years" value="true" 
                                    ${oi.oi_have_travel_country_5years ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('traveled-countries', true); updateApplicantData('otherInfo', 'oi_have_travel_country_5years', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_travel_country_5years" value="false" 
                                    ${!oi.oi_have_travel_country_5years ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('traveled-countries', false); updateApplicantData('otherInfo', 'oi_have_travel_country_5years', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="traveled-countries" class="conditional-block" style="display: ${oi.oi_have_travel_country_5years ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Traveled Countries</label>
                            <div id="travelled-countries-container">
                                ${generateTravelledCountryFields(oi.oi_travelled_country || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addTravelledCountryField()">
                                Add Another Country
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you belonged to any professional, social, or charitable organizations?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_you_belong_orgntion" value="true" 
                                    ${oi.oi_have_you_belong_orgntion ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('organization-info', true); updateApplicantData('otherInfo', 'oi_have_you_belong_orgntion', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_you_belong_orgntion" value="false" 
                                    ${!oi.oi_have_you_belong_orgntion ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('organization-info', false); updateApplicantData('otherInfo', 'oi_have_you_belong_orgntion', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="organization-info" class="conditional-block" style="display: ${oi.oi_have_you_belong_orgntion ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Organization Name*</label>
                            <div id="organizations-container">
                                ${generateOrganizationFields(oi.oi_organization_name || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addOrganizationField()">
                                Add Another Organization
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have any special skills or training in fire arms, explosives, or nuclear materials?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_special_skills" value="true" 
                                    ${oi.oi_have_special_skills ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('special-skills-info', true); updateApplicantData('otherInfo', 'oi_have_special_skills', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_special_skills" value="false" 
                                    ${!oi.oi_have_special_skills ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('special-skills-info', false); updateApplicantData('otherInfo', 'oi_have_special_skills', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="special-skills-info" class="conditional-block" style="display: ${oi.oi_have_special_skills ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Explain your special skills or training</label>
                            <textarea name="oi_special_skills" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    rows="4"
                                    onchange="updateApplicantData('otherInfo', 'oi_special_skills', this.value)">${oi.oi_special_skills || ''}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever served in the military?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_served_military" value="true" 
                                    ${oi.oi_have_served_military ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('military-service-history', true); updateApplicantData('otherInfo', 'oi_have_served_military', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_served_military" value="false" 
                                    ${!oi.oi_have_served_military ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('military-service-history', false); updateApplicantData('otherInfo', 'oi_have_served_military', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="military-service-history" class="conditional-block" style="display: ${oi.oi_have_served_military ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Military Service History</label>
                            <div id="military-service-container">
                                ${generateMilitaryServiceFields(oi.oi_military_service || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addMilitaryServiceField()">
                                Add Another Service Period
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Keep all your existing helper functions as they are
        function updateTravelledCountryArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }
            
            // For travelled countries, we're storing just the country code as string
            state.applicants[state.currentApplicant][scope][arrayName][index] = value;
            saveToLocalStorage();
        }

        function addTravelledCountryField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_travelled_country) {
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country = [];
            }
            
            state.applicants[state.currentApplicant].otherInfo.oi_travelled_country.push('');
            
            const container = document.getElementById('travelled-countries-container');
            if (container) {
                container.innerHTML = generateTravelledCountryFields(state.applicants[state.currentApplicant].otherInfo.oi_travelled_country);
            }
            
            saveToLocalStorage();
        }

        function removeTravelledCountryField(index) {
            if (state.applicants[state.currentApplicant].otherInfo && 
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country) {
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country.splice(index, 1);
                
                const container = document.getElementById('travelled-countries-container');
                if (container) {
                    container.innerHTML = generateTravelledCountryFields(state.applicants[state.currentApplicant].otherInfo.oi_travelled_country);
                }
                
                saveToLocalStorage();
            }
        }

        function generateTravelledCountryFields(selectedCountries) {
            if (!selectedCountries || !Array.isArray(selectedCountries)) {
                selectedCountries = [''];
            }
            
            return selectedCountries.map((countryCode, index) => `
                <div class="flex items-center space-x-2 mb-2">
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateTravelledCountryArray('otherInfo', 'oi_travelled_country', ${index}, 'country', this.value)">
                        <option value="">Select Country</option>
                        ${countries.map(country => `
                            <option value="${country.code}" ${countryCode === country.code ? 'selected' : ''}>${country.name}</option>
                        `).join('')}
                    </select>
                    <button type="button" 
                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeTravelledCountryField(${index})">
                        Remove
                    </button>
                </div>
            `).join('');
        }

        function updateOrganizationArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }
            
            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();
        }

        function addOrganizationField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_organization_name) {
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name = [];
            }
            
            state.applicants[state.currentApplicant].otherInfo.oi_organization_name.push({ name: '' });
            
            const container = document.getElementById('organizations-container');
            if (container) {
                container.innerHTML = generateOrganizationFields(state.applicants[state.currentApplicant].otherInfo.oi_organization_name);
            }
            
            saveToLocalStorage();
        }

        function removeOrganizationField(index) {
            if (state.applicants[state.currentApplicant].otherInfo && 
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name) {
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name.splice(index, 1);
                
                const container = document.getElementById('organizations-container');
                if (container) {
                    container.innerHTML = generateOrganizationFields(state.applicants[state.currentApplicant].otherInfo.oi_organization_name);
                }
                
                saveToLocalStorage();
            }
        }

        function generateOrganizationFields(organizations) {
            if (!organizations || !Array.isArray(organizations)) {
                organizations = [{ name: '' }];
            }
            
            return organizations.map((org, index) => `
                <div class="flex items-center space-x-2 mb-2">
                    <input type="text" 
                        value="${org.name || ''}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="updateOrganizationArray('otherInfo', 'oi_organization_name', ${index}, 'name', this.value)">
                    <button type="button" 
                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeOrganizationField(${index})">
                        Remove
                    </button>
                </div>
            `).join('');
        }

        function updateMilitaryServiceArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }
            
            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();
        }

        function addMilitaryServiceField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_military_service) {
                state.applicants[state.currentApplicant].otherInfo.oi_military_service = [];
            }
            
            state.applicants[state.currentApplicant].otherInfo.oi_military_service.push({
                oi_sm_country_name: '',
                oi_sm_service_branch: '',
                oi_sm_rank: '',
                oi_militay_speciality: '',
                oi_sm_serve_from: '',
                oi_sm_serve_to: ''
            });
            
            const container = document.getElementById('military-service-container');
            if (container) {
                container.innerHTML = generateMilitaryServiceFields(state.applicants[state.currentApplicant].otherInfo.oi_military_service);
            }
            
            saveToLocalStorage();
        }

        function removeMilitaryServiceField(index) {
            if (state.applicants[state.currentApplicant].otherInfo && 
                state.applicants[state.currentApplicant].otherInfo.oi_military_service) {
                state.applicants[state.currentApplicant].otherInfo.oi_military_service.splice(index, 1);
                
                const container = document.getElementById('military-service-container');
                if (container) {
                    container.innerHTML = generateMilitaryServiceFields(state.applicants[state.currentApplicant].otherInfo.oi_military_service);
                }
                
                saveToLocalStorage();
            }
        }

        function generateMilitaryServiceFields(serviceHistory) {
            if (!serviceHistory || !Array.isArray(serviceHistory)) {
                serviceHistory = [{
                    oi_sm_country_name: '',
                    oi_sm_service_branch: '',
                    oi_sm_rank: '',
                    oi_militay_speciality: '',
                    oi_sm_serve_from: '',
                    oi_sm_serve_to: ''
                }];
            }
            
            return serviceHistory.map((service, index) => `
                <div class="border border-gray-300 p-4 rounded-lg mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Name of Country</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_country_name', this.value)">
                                <option value="">Select Country</option>
                                ${countries.map(country => `
                                    <option value="${country.code}" ${service.oi_sm_country_name === country.code ? 'selected' : ''}>${country.name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Branch of Service</label>
                            <input type="text" 
                                value="${service.oi_sm_service_branch || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_service_branch', this.value)">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Rank or Rating</label>
                            <input type="text" 
                                value="${service.oi_sm_rank || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_rank', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Military Speciality</label>
                            <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    rows="3"
                                    onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_militay_speciality', this.value)">${service.oi_militay_speciality || ''}</textarea>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Service Date From</label>
                            <input type="date" 
                                value="${service.oi_sm_serve_from || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_serve_from', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Service Date To</label>
                            <input type="date" 
                                value="${service.oi_sm_serve_to || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_serve_to', this.value)">
                        </div>
                    </div>
                    <button type="button" 
                            class="mt-4 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeMilitaryServiceField(${index})">
                        Remove Service Record
                    </button>
                </div>
            `).join('');
        }     