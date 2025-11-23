        // Previous U.S. Travel Step (Based on Excel PUST section)
        function generatePreviousTravelStep(applicant) {
            const pust = applicant.travelHistory || {};
            const previousTravels = pust.previousTravels || [{
                arrival_date: '',
                staying_length: ''
            }];
            const driverLicenses = pust.driverLicenses || [{
                license_no: '',
                state: ''
            }];

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever issued a visa to the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="1" 
                                    ${pust.pust_have_ever_issued ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-visa', true); updateApplicantData('travelHistory', 'pust_have_ever_issued', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="0" 
                                    ${!pust.pust_have_ever_issued ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-visa', false); updateApplicantData('travelHistory', 'pust_have_ever_issued', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="previous-visa" class="conditional-block" style="display: ${pust.pust_have_ever_issued ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Date Last Issued Visa</label>
                                    <input type="text" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        value="${pust.pust_last_issued_visa_date ? convertToDisplay(pust.pust_last_issued_visa_date) : ''}" 
                                        onchange="handleDateChange('travelHistory', 'pust_last_issued_visa_date', this.value)"
                                        placeholder="DD/MM/YYYY"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Visa Number</label>
                                    <input type="text" name="pust_visa_no" 
                                        value="${pust.pust_visa_no || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelHistory', 'pust_visa_no', this.value)">
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="pust_remember_visa_no" 
                                        ${pust.pust_remember_visa_no ? 'checked' : ''}
                                        onchange="updateApplicantData('travelHistory', 'pust_remember_visa_no', this.checked)">
                                    <span class="ml-2">Do not know visa number</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Are you applying for the same visa type?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_visa" value="1" 
                                    ${pust.pust_have_applied_same_visa ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_visa', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_visa" value="0" 
                                    ${!pust.pust_have_applied_same_visa ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_visa', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Are you applying in the same country?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_country" value="1" 
                                    ${pust.pust_have_applied_same_country ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_country', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_country" value="0" 
                                    ${!pust.pust_have_applied_same_country ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_country', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Missing Toggle Fields -->
                    <div>
                        <label class="block text-gray-700 mb-2">Have you traveled to the USA before?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_travelled_before" value="1" 
                                    ${pust.pust_have_travelled_before ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-travel-details', true); updateApplicantData('travelHistory', 'pust_have_travelled_before', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_travelled_before" value="0" 
                                    ${!pust.pust_have_travelled_before ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-travel-details', false); updateApplicantData('travelHistory', 'pust_have_travelled_before', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Multi-Entry Previous Travel Details -->
                    <div id="previous-travel-details" class="conditional-block" style="display: ${pust.pust_have_travelled_before ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Previous Travel Details</h4>
                            <div id="previous-travel-fields">
                                ${generatePreviousTravelFields(previousTravels)}
                            </div>
                            <button type="button" onclick="addPreviousTravelField()" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Another Previous Travel
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have a U.S. Social Security Number?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_social_security_no" value="1" 
                                    ${pust.pust_have_social_security_no ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('social-security-field', true); updateApplicantData('travelHistory', 'pust_have_social_security_no', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_social_security_no" value="0" 
                                    ${!pust.pust_have_social_security_no ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('social-security-field', false); updateApplicantData('travelHistory', 'pust_have_social_security_no', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="social-security-field" class="conditional-block" style="display: ${pust.pust_have_social_security_no ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">U.S. Social Security Number</label>
                            <input type="text" name="pust_social_security_no" 
                                value="${pust.pust_social_security_no || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('travelHistory', 'pust_social_security_no', this.value)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have a U.S. Taxpayer Identification Number?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_tin" value="1" 
                                    ${pust.pust_have_us_tin ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('tin-field', true); updateApplicantData('travelHistory', 'pust_have_us_tin', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_tin" value="0" 
                                    ${!pust.pust_have_us_tin ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('tin-field', false); updateApplicantData('travelHistory', 'pust_have_us_tin', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="tin-field" class="conditional-block" style="display: ${pust.pust_have_us_tin ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">U.S. Taxpayer Identification Number</label>
                            <input type="text" name="pust_us_tin" 
                                value="${pust.pust_us_tin || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('travelHistory', 'pust_us_tin', this.value)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you ever hold a U.S. Driver License?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_driving_license" value="1" 
                                    ${pust.pust_have_us_driving_license ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('driver-license-section', true); updateApplicantData('travelHistory', 'pust_have_us_driving_license', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_driving_license" value="0" 
                                    ${!pust.pust_have_us_driving_license ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('driver-license-section', false); updateApplicantData('travelHistory', 'pust_have_us_driving_license', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="driver-license-section" class="conditional-block" style="display: ${pust.pust_have_us_driving_license ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">U.S. Driver License Details</h4>
                            <div id="driver-license-fields">
                                ${generateDriverLicenseFields(driverLicenses)}
                            </div>
                            <button type="button" onclick="addDriverLicenseField()" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Another Driver License
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you been ten fingerprinted?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ten_fingerprint" value="1" 
                                    ${pust.pust_have_ten_fingerprint ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_ten_fingerprint', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ten_fingerprint" value="0" 
                                    ${!pust.pust_have_ten_fingerprint ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_ten_fingerprint', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever been refused a visa to the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_refused_us_visa" value="1" 
                                    ${pust.pust_have_refused_us_visa ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('visa-refusal-explain', true); updateApplicantData('travelHistory', 'pust_have_refused_us_visa', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_refused_us_visa" value="0" 
                                    ${!pust.pust_have_refused_us_visa ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('visa-refusal-explain', false); updateApplicantData('travelHistory', 'pust_have_refused_us_visa', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="visa-refusal-explain" class="conditional-block" style="display: ${pust.pust_have_refused_us_visa ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">Explain Visa Refusal</label>
                            <textarea name="pust_visa_refusal_explain" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelHistory', 'pust_visa_refusal_explain', this.value)">${pust.pust_visa_refusal_explain || ''}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Are you or have you ever been in the USA as a legal permanent resident?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_legal_permanent_resident" value="1" 
                                    ${pust.pust_have_legal_permanent_resident ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_legal_permanent_resident', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_legal_permanent_resident" value="0" 
                                    ${!pust.pust_have_legal_permanent_resident ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_legal_permanent_resident', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Complex Conditional Logic -->
                    <div id="complex-visa-questions" class="conditional-block" style="display: ${(pust.pust_have_ever_issued && pust.pust_have_travelled_before) ? 'block' : 'none'};">
                        <div class="space-y-6 border-t pt-6">
                            <h4 class="text-lg font-medium text-gray-800">Additional Visa Information</h4>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Have your U.S. visa ever been lost or stolen?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_lost" value="1" 
                                            ${pust.pust_have_us_visa_lost ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_lost', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_lost" value="0" 
                                            ${!pust.pust_have_us_visa_lost ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_lost', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 mb-2">Have your U.S. visa ever been cancelled or revoked?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_cancelled" value="1" 
                                            ${pust.pust_have_us_visa_cancelled ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_cancelled', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_cancelled" value="0" 
                                            ${!pust.pust_have_us_visa_cancelled ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_cancelled', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Helper Functions for Multi-Entry Sections
        function generatePreviousTravelFields(travels) {
            return travels.map((travel, index) => `
                <div class="previous-travel-field border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Date of Arrival</label>
                            <input type="date" name="pust_arrival_date" 
                                value="${travel.arrival_date || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousTravelData(${index}, 'arrival_date', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Length of Stay</label>
                            <input type="text" name="pust_previous_staying_length" 
                                value="${travel.staying_length || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousTravelData(${index}, 'staying_length', this.value)">
                        </div>
                    </div>
                    ${index > 0 ? `
                        <button type="button" onclick="removePreviousTravelField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-times mr-2"></i> Remove Travel
                        </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function addPreviousTravelField() {
            const container = document.getElementById('previous-travel-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'previous-travel-field border-b pb-4 mb-4';
            newField.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Date of Arrival</label>
                        <input type="date" name="pust_arrival_date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updatePreviousTravelData(${index}, 'arrival_date', this.value)">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Length of Stay</label>
                        <input type="text" name="pust_previous_staying_length" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updatePreviousTravelData(${index}, 'staying_length', this.value)">
                    </div>
                </div>
                <button type="button" onclick="removePreviousTravelField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-times mr-2"></i> Remove Travel
                </button>
            `;
            container.appendChild(newField);
        }

        function removePreviousTravelField(index) {
            const field = document.querySelector(`.previous-travel-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                updatePreviousTravelsData();
            }
        }

        function updatePreviousTravelData(index, field, value) {
            if (!currentApplicant.travelHistory.previousTravels) {
                currentApplicant.travelHistory.previousTravels = [];
            }
            if (!currentApplicant.travelHistory.previousTravels[index]) {
                currentApplicant.travelHistory.previousTravels[index] = {};
            }
            currentApplicant.travelHistory.previousTravels[index][field] = value;
        }

        function updatePreviousTravelsData() {
            const travelFields = document.querySelectorAll('.previous-travel-field');
            currentApplicant.travelHistory.previousTravels = Array.from(travelFields).map((field, index) => {
                return {
                    arrival_date: field.querySelector('input[name="pust_arrival_date"]')?.value || '',
                    staying_length: field.querySelector('input[name="pust_previous_staying_length"]')?.value || ''
                };
            });
        }
    
        function generateDriverLicenseFields(licenses) {
            return licenses.map((license, index) => `
                <div class="driver-license-field border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">License Number</label>
                            <input type="text" name="pust_driving_license_no" 
                                value="${license.license_no || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateDriverLicenseData(${index}, 'license_no', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">State</label>
                            <input type="text" name="pust_driving_license_state" 
                                value="${license.state || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateDriverLicenseData(${index}, 'state', this.value)">
                        </div>
                    </div>
                    ${index > 0 ? `
                        <button type="button" onclick="removeDriverLicenseField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-times mr-2"></i> Remove License
                        </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function addDriverLicenseField() {
            const container = document.getElementById('driver-license-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'driver-license-field border-b pb-4 mb-4';
            newField.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">License Number</label>
                        <input type="text" name="pust_driving_license_no" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateDriverLicenseData(${index}, 'license_no', this.value)">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">State</label>
                        <input type="text" name="pust_driving_license_state" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateDriverLicenseData(${index}, 'state', this.value)">
                    </div>
                </div>
                <button type="button" onclick="removeDriverLicenseField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-times mr-2"></i> Remove License
                </button>
            `;
            container.appendChild(newField);
        }

        function removeDriverLicenseField(index) {
            const field = document.querySelector(`.driver-license-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                updateDriverLicensesData();
            }
        }

        function updateDriverLicenseData(index, field, value) {
            if (!currentApplicant.travelHistory.driverLicenses) {
                currentApplicant.travelHistory.driverLicenses = [];
            }
            if (!currentApplicant.travelHistory.driverLicenses[index]) {
                currentApplicant.travelHistory.driverLicenses[index] = {};
            }
            currentApplicant.travelHistory.driverLicenses[index][field] = value;
        }

        function updateDriverLicensesData() {
            const licenseFields = document.querySelectorAll('.driver-license-field');
            currentApplicant.travelHistory.driverLicenses = Array.from(licenseFields).map((field, index) => {
                return {
                    license_no: field.querySelector('input[name="pust_driving_license_no"]')?.value || '',
                    state: field.querySelector('input[name="pust_driving_license_state"]')?.value || ''
                };
            });
        }