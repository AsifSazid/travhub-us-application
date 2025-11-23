

        // তারিখ validation ফাংশন
        function isValidDate(dateString) {
            // DD/MM/YYYY format validate
            const pattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            if (!pattern.test(dateString)) return false;

            const [_, day, month, year] = pattern.exec(dateString);
            const date = new Date(year, month - 1, day);

            return date.getDate() == day &&
                date.getMonth() == month - 1 &&
                date.getFullYear() == year;
        }

        // DD/MM/YYYY থেকে YYYY-MM-DD তে convert
        function convertToISO(dateString) {
            if (!isValidDate(dateString)) return '';

            const [day, month, year] = dateString.split('/');
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }

        // YYYY-MM-DD থেকে DD/MM/YYYY তে convert
        function convertToDisplay(isoDate) {
            if (!isoDate) return '';

            const [year, month, day] = isoDate.split('-');
            return `${day}/${month}/${year}`;
        }


                            <input type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                value="${applicant.travelInfo.leaveDate ? convertToDisplay(applicant.travelInfo.leaveDate) : ''}" 
                                onchange="handleDateChange('travelInfo', 'leaveDate', this.value)"
                                placeholder="DD/MM/YYYY"
                                required>



        function handleDateChange(category, field, value) {
            if (isValidDate(value)) {
                const isoDate = convertToISO(value);
                updateApplicantData(category, field, isoDate);
            } else {
                alert('Please enter date in DD/MM/YYYY format');
                // ফোকাস ফিরিয়ে দিন এবং ভ্যালু ক্লিয়ার করুন
                event.target.focus();
                event.target.value = '';
            }
        }

        // আপনার existing code এর নিচে এই ফাংশনগুলো যোগ করুন

        // তারিখ validation
        function isValidDate(dateString) {
            const pattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            if (!pattern.test(dateString)) return false;

            const day = parseInt(dateString.split('/')[0]);
            const month = parseInt(dateString.split('/')[1]);
            const year = parseInt(dateString.split('/')[2]);

            // সহজ validation
            if (day < 1 || day > 31) return false;
            if (month < 1 || month > 12) return false;
            if (year < 1900 || year > 2100) return false;

            return true;
        }

        // DD/MM/YYYY থেকে YYYY-MM-DD
        function convertToISO(dateString) {
            if (!isValidDate(dateString)) return '';
            const parts = dateString.split('/');
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }

        // YYYY-MM-DD থেকে DD/MM/YYYY
        function convertToDisplay(isoDate) {
            if (!isoDate) return '';
            const parts = isoDate.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        // তারিখ change handler
        function handleDateChange(category, field, value) {
            if (value === '') {
                updateApplicantData(category, field, '');
                return;
            }

            if (isValidDate(value)) {
                const isoDate = convertToISO(value);
                updateApplicantData(category, field, isoDate);
            } else {
                alert('Invalid date format. Please use DD/MM/YYYY');
                event.target.value = '';
                updateApplicantData(category, field, '');
            }
        }