// Travel Information Step (Based on Excel TI section)
function generateTravelInfoStep(applicant) {
    const ti = applicant.travelInfo || {};
    const locations = ti.locations || [{
        address_line_1: '',
        address_line_2: '',
        city: '',
        state: '',
        zip_code: ''
    }];

    return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Purpose of Travel *</label>
                        <input type="text" name="ti_travel_purpose" 
                            value="${ti.ti_travel_purpose || ''}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateApplicantData('travelInfo', 'ti_travel_purpose', this.value)" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you made travel plans?</label>
                        <select name="ti_have_travel_plan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="handleTravelPlanChange(this.value); updateApplicantData('travelInfo', 'ti_have_travel_plan', this.value)">
                            <option value="">Select</option>
                            <option value="yes" ${(ti.ti_have_travel_plan === 'yes') ? 'selected' : ''}>Yes</option>
                            <option value="no" ${(ti.ti_have_travel_plan === 'no') ? 'selected' : ''}>No</option>
                        </select>
                    </div>

                    <!-- No Travel Plan Fields -->
                    <div id="no-travel-plan" class="conditional-block" style="display: ${ti.ti_have_travel_plan === 'no' ? 'block' : 'none'};">
                        <!-- ... no travel plan fields content ... -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Intended date of arrival</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_intended_arrival_date ? convertToDisplay(ti.ti_intended_arrival_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_intended_arrival_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Length of stay</label>
                                <input type="text" name="ti_stay_length" 
                                    value="${ti.ti_stay_length || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_stay_length', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Number + Select Option (Day, Month, Year)</label>
                                <select name="ti_length_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_length_type', this.value)">
                                    <option value="">Select</option>
                                    <option value="Days" ${(ti.ti_length_type === 'Days') ? 'selected' : ''}>Days</option>
                                    <option value="Months" ${(ti.ti_length_type === 'Months') ? 'selected' : ''}>Months</option>
                                    <option value="Years" ${(ti.ti_length_type === 'Years') ? 'selected' : ''}>Years</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Yes Travel Plan Fields -->
                    <div id="yes-travel-plan" class="conditional-block" style="display: ${ti.ti_have_travel_plan === 'yes' ? 'block' : 'none'};">
                        <!-- ... yes travel plan fields content ... -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Date of Arrival in the USA</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_arrival_date ? convertToDisplay(ti.ti_arrival_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_arrival_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Arrival Flight Number</label>
                                <input type="text" name="ti_arrival_flight_no" 
                                    value="${ti.ti_arrival_flight_no || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_arrival_flight_no', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Arrival City</label>
                                <input type="text" name="ti_arrival_city" 
                                    value="${ti.ti_arrival_city || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_arrival_city', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Date of Departure</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_departure_date ? convertToDisplay(ti.ti_departure_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_departure_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Departure Flight Number</label>
                                <input type="text" name="ti_departure_flight_no" 
                                    value="${ti.ti_departure_flight_no || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_departure_flight_no', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Departure City</label>
                                <input type="text" name="ti_departure_city" 
                                    value="${ti.ti_departure_city || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_departure_city', this.value)">
                            </div>
                        </div>
                    </div>

                    <!-- Locations to Visit -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Locations You Plan to Visit</h3>
                        <div id="location-fields">
                            ${generateLocationFields(locations)}
                        </div>
                        <button type="button" onclick="addLocationField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Another Location
                        </button>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Who is paying for your trip?</label>
                        <select name="trip_payment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="handlePaymentChange(this.value); updateApplicantData('travelInfo', 'trip_payment', this.value)">
                            <option value="">Select</option>
                            <option value="Self" ${(ti.trip_payment === 'Self') ? 'selected' : ''}>Self</option>
                            <option value="Other person" ${(ti.trip_payment === 'Other person') ? 'selected' : ''}>Other person</option>
                            <option value="Present employer" ${(ti.trip_payment === 'Present employer') ? 'selected' : ''}>Present employer</option>
                            <option value="Employer in the USA" ${(ti.trip_payment === 'Employer in the USA') ? 'selected' : ''}>Employer in the USA</option>
                            <option value="Other Company" ${(ti.trip_payment === 'Other Company') ? 'selected' : ''}>Other Company</option>
                        </select>
                    </div>

                    <!-- Payment Fields for Other Person / Others- -->
                    <div id="other-person-payment" class="conditional-block" style="display: ${(ti.trip_payment === 'Other person') ? 'block' : 'none'};">
                        <!-- ... other person payment fields content ... -->
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Paying Person Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Surname of Paying Person</label>
                                <input type="text" name="trip_paying_person_surname" 
                                    value="${ti.trip_paying_person_surname || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'trip_paying_person_surname', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Given Name of Paying Person</label>
                                <input type="text" name="ti_trip_paying_person_given_name" 
                                    value="${ti.ti_trip_paying_person_given_name || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_given_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Telephone of Paying Person</label>
                                <input type="tel" name="ti_trip_paying_person_telephone" 
                                    value="${ti.ti_trip_paying_person_telephone || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_telephone', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Email of Paying Person</label>
                                <input type="email" name="ti_trip_paying_person_email" 
                                    value="${ti.ti_trip_paying_person_email || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_email', this.value)">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 mb-2">Relationship to Paying Person</label>
                                <input type="text" name="_trip_paying_person_relationship" 
                                    value="${ti._trip_paying_person_relationship || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', '_trip_paying_person_relationship', this.value)">
                            </div>
                        </div>


                        <!-- Address Toggle for Paying Person -->
                        <div class="mt-4">
                            <label class="block text-gray-700 mb-2">Is the address of paying person same as yours?</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trip_paying_person_have_same_address" value="1" ${ti.trip_paying_person_have_same_address ? 'checked' : ''} onchange="toggleConditionalBlock('paying-person-address', false); updateApplicantData('travelInfo', 'trip_paying_person_have_same_address', true)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trip_paying_person_have_same_address" value="0" ${!ti.trip_paying_person_have_same_address ? 'checked' : ''} onchange="toggleConditionalBlock('paying-person-address', true); updateApplicantData('travelInfo', 'trip_paying_person_have_same_address', false)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Address Block for Paying Person -->
                        <div id="paying-person-address" class="conditional-block mt-4" style="display: ${!ti.trip_paying_person_have_same_address ? 'block' : 'none'};">
                            <!-- ... paying person address fields ... -->
                            <div class="border-t pt-6">
                                <!-- Conditional Address Block for Paying Person -->
                                <div id="paying-person-address" class="conditional-block mt-4" style="display: ${!ti.trip_paying_person_have_same_address ? 'block' : 'none'};">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 1</label>
                                            <input type="text" name="ti_trip_paying_person_address_line_1" 
                                                value="${ti.ti_trip_paying_person_address_line_1 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_line_1', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 2</label>
                                            <input type="text" name="ti_trip_paying_person_address_line_2" 
                                                value="${ti.ti_trip_paying_person_address_line_2 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_line_2', this.value)">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-gray-700 mb-2">City</label>
                                                <input type="text" name="ti_trip_paying_person_address_city" 
                                                    value="${ti.ti_trip_paying_person_address_city || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_city', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">State</label>
                                                <input type="text" name="ti_trip_paying_person_address_state" 
                                                    value="${ti.ti_trip_paying_person_address_state || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_state', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">Zip Code</label>
                                                <input type="text" name="ti_trip_paying_person_address_zip_code" 
                                                    value="${ti.ti_trip_paying_person_address_zip_code || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_zip_code', this.value)">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Country</label>
                                            <select name="trip_paying_person_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'trip_paying_person_address_country', this.value)">
                                                <option value="">Select Country</option>
                                                ${countries.map(country =>
        `<option value="${country.code}" ${(ti.trip_paying_person_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
    ).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Fields for Other Company -->
                    <div id="other-company-payment" class="conditional-block" style="display: ${ti.trip_payment === 'Other Company' ? 'block' : 'none'};">
                        <!-- ... other company payment fields content ... -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Other Company Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Name</label>
                                    <input type="text" name="ti_trip_paying_company_name" 
                                        value="${ti.ti_trip_paying_company_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Telephone</label>
                                    <input type="tel" name="ti_trip_paying_company_telephone" 
                                        value="${ti.ti_trip_paying_company_telephone || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Email</label>
                                    <input type="email" name="ti_trip_paying_company_email" 
                                        value="${ti.ti_trip_paying_company_email || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Relationship to Company</label>
                                    <input type="text" name="ti_trip_paying_company_relationship" 
                                        value="${ti.ti_trip_paying_company_relationship || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_relationship', this.value)">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 mt-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 1</label>
                                    <input type="text" name="ti_trip_paying_company_address_line_1" 
                                        value="${ti.ti_trip_paying_company_address_line_1 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_line_1', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 2</label>
                                    <input type="text" name="ti_trip_paying_company_address_line_2" 
                                        value="${ti.ti_trip_paying_company_address_line_2 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_line_2', this.value)">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">City</label>
                                        <input type="text" name="ti_trip_paying_company_address_city" 
                                            value="${ti.ti_trip_paying_company_address_city || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_city', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">State</label>
                                        <input type="text" name="ti_trip_paying_company_address_state" 
                                            value="${ti.ti_trip_paying_company_address_state || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_state', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Zip Code</label>
                                        <input type="text" name="ti_trip_paying_company_address_zip_code" 
                                            value="${ti.ti_trip_paying_company_address_zip_code || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_zip_code', this.value)">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Country</label>
                                    <select name="ti_trip_paying_company_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_country', this.value)">
                                        <option value="">Select Country</option>
                                        ${countries.map(country =>
        `<option value="${country.code}" ${(ti.ti_trip_paying_company_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
    ).join('')}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
}

// Simplified Helper Functions using toggleConditionalBlock
function handleTravelPlanChange(value) {
    toggleConditionalBlock('no-travel-plan', value === 'no');
    toggleConditionalBlock('yes-travel-plan', value === 'yes');
}

function handlePaymentChange(value) {
    const showOtherPerson = (value === 'Other person' || value === 'Others-');
    const showOtherCompany = (value === 'Other Company');

    toggleConditionalBlock('other-person-payment', showOtherPerson);
    toggleConditionalBlock('other-company-payment', showOtherCompany);
}

// Initialize function to set initial states
function initializeTravelStep() {
    const travelPlanSelect = document.querySelector('select[name="ti_have_travel_plan"]');
    const paymentSelect = document.querySelector('select[name="trip_payment"]');

    if (travelPlanSelect) {
        handleTravelPlanChange(travelPlanSelect.value);
    }
    if (paymentSelect) {
        handlePaymentChange(paymentSelect.value);
    }
}

// Call initialize when the step is loaded
// You can call this function after generating the travel info step