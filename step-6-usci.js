        function generateUSContactStep(applicant) {
            const usci = applicant.usContactInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Contact Type</label>
                        <select name="usci_contact_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="toggleContactTypeFields(this.value); updateApplicantData('usContactInfo', 'usci_contact_type', this.value)">
                            <option value="">Select Type</option>
                            <option value="Person" ${(usci.usci_contact_type === 'Person') ? 'selected' : ''}>Person</option>
                            <option value="Company" ${(usci.usci_contact_type === 'Company') ? 'selected' : ''}>Company</option>
                            <option value="Hotel" ${(usci.usci_contact_type === 'Hotel') ? 'selected' : ''}>Hotel</option>
                        </select>
                    </div>

                    <!-- Person Contact -->
                    <div id="person-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Person' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Person Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Surname</label>
                                    <input type="text" name="usci_contact_person_surname" 
                                        value="${usci.usci_contact_person_surname || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_person_surname', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Given Name</label>
                                    <input type="text" name="usci_contact_person_given_name" 
                                        value="${usci.usci_contact_person_given_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_person_given_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Telephone</label>
                                    <input type="tel" name="usci contact person telephone" 
                                        value="${usci['usci contact person telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Email</label>
                                    <input type="email" name="usci contact person email" 
                                        value="${usci['usci contact person email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Contact Person Relationship</label>
                                    <input type="text" name="usci contact person relationship" 
                                        value="${usci['usci contact person relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Person Address Block -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Contact Person Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact person address line 1" 
                                            value="${usci['usci contact person address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact person address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact person address line 2" 
                                            value="${usci['usci contact person address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact person address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact person address city" 
                                                value="${usci['usci contact person address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact person address state" 
                                                value="${usci['usci contact person address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact person address zip code" 
                                                value="${usci['usci contact person address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Contact -->
                    <div id="company-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Company' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Company Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Surname</label>
                                    <input type="text" name="usci_contact_company_name" 
                                        value="${usci.usci_contact_company_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_company_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Telephone</label>
                                    <input type="tel" name="usci contact company telephone" 
                                        value="${usci['usci contact company telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Email</label>
                                    <input type="email" name="usci contact company email" 
                                        value="${usci['usci contact company email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Company Relationship</label>
                                    <input type="text" name="usci contact company relationship" 
                                        value="${usci['usci contact company relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Company Address Block -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Company Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact company address line 1" 
                                            value="${usci['usci contact company address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact company address line 2" 
                                            value="${usci['usci contact company address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact company address city" 
                                                value="${usci['usci contact company address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact company address state" 
                                                value="${usci['usci contact company address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact company address zip code" 
                                                value="${usci['usci contact company address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hotel Contact -->
                    <div id="hotel-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Hotel' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Hotel Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Surname</label>
                                    <input type="text" name="usci_contact_hotel_name" 
                                        value="${usci.usci_contact_hotel_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_hotel_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Telephone</label>
                                    <input type="tel" name="usci contact hotel telephone" 
                                        value="${usci['usci contact hotel telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Email</label>
                                    <input type="email" name="usci contact hotel email" 
                                        value="${usci['usci contact hotel email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Hotel Relationship</label>
                                    <input type="text" name="usci contact hotel relationship" 
                                        value="${usci['usci contact hotel relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Hotel Address Block (using same input names as Company) -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Hotel Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact company address line 1" 
                                            value="${usci['usci contact company address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact company address line 2" 
                                            value="${usci['usci contact company address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact company address city" 
                                                value="${usci['usci contact company address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact company address state" 
                                                value="${usci['usci contact company address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact company address zip code" 
                                                value="${usci['usci contact company address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Toggle function using your existing toggleConditionalBlock
        function toggleContactTypeFields(value) {
            toggleConditionalBlock('person-contact', value === 'Person');
            toggleConditionalBlock('company-contact', value === 'Company');
            toggleConditionalBlock('hotel-contact', value === 'Hotel');
        }

        // Initialize function for US Contact step
        function initializeUSContactStep() {
            const contactTypeSelect = document.querySelector('select[name="usci_contact_type"]');
            if (contactTypeSelect) {
                toggleContactTypeFields(contactTypeSelect.value);
            }
        }