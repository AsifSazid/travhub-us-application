        // Travel Companion Information Step (Based on Excel TCI section)
        function generateTravelCompanionStep(applicant) {
            const tci = applicant.travelInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Are you traveling with anyone?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="1" 
                                    ${tci.tci_have_anyone ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('travel-companion', true); updateApplicantData('travelInfo', 'tci_have_anyone', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="0" 
                                    ${!tci.tci_have_anyone ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('travel-companion', false); updateApplicantData('travelInfo', 'tci_have_anyone', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="travel-companion" class="conditional-block" style="display: ${tci.tci_have_anyone ? 'block' : 'none'};">
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-800">Travel Companion Details</h4>
                            
                            <!-- Companion Details (Multiple) -->
                            <div class="dynamic-field-group">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Surname</label>
                                        <input type="text" name="tci_surname" 
                                            value="${tci.tci_surname || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_surname', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Given name</label>
                                        <input type="text" name="tci_given_name" 
                                            value="${tci.tci_given_name || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_given_name', this.value)">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2">Relationship to You</label>
                                        <input type="text" name="tci_relationship" 
                                            value="${tci.tci_relationship || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_relationship', this.value)">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 mb-2">Are you traveling as part of a group?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tci_have_group" value="1" 
                                            ${tci.tci_have_group ? 'checked' : ''}
                                            onchange="toggleConditionalBlock('group-travel', true); updateApplicantData('travelInfo', 'tci_have_group', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tci_have_group" value="0" 
                                            ${!tci.tci_have_group ? 'checked' : ''}
                                            onchange="toggleConditionalBlock('group-travel', false); updateApplicantData('travelInfo', 'tci_have_group', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div id="group-travel" class="conditional-block" style="display: ${tci.tci_have_group ? 'block' : 'none'};">
                                <div>
                                    <label class="block text-gray-700 mb-2">Group Name</label>
                                    <input type="text" name="tci_group_name" 
                                        value="${tci.tci_group_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'tci_group_name', this.value)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }