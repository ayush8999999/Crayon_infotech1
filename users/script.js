document.addEventListener('DOMContentLoaded', () => {
    let companyIndex = 1;

    // document.querySelector('.add-company').addEventListener('click', addCompanySection);

    // function addCompanySection() {
    //     const container = document.querySelector('#companies');
    //     const companySection = document.querySelector('.company-section').cloneNode(true);
    //     companySection.querySelectorAll('input').forEach(input => {
    //         input.value = ''; // Clear the values
    //         let name = input.name;
    //         // Update the name attribute with a unique index
    //         input.name = name.replace(/\[\d+\]/, `[${companyIndex}]`);
    //     });
    //     container.appendChild(companySection);
    //     companyIndex++;
    // }

    // document.querySelector('form').addEventListener('submit', function(event) {
    //     if (!validateForm()) {
    //         event.preventDefault(); // Prevent form submission if validation fails
    //     }
    // });

    function validateForm(event) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const mobile = document.getElementById('mobile').value.trim();
        const companySections = document.querySelectorAll('.company-section');
    
        let isValid = true;
        let errorMessages = [];
    
        // Validate name
        if (name === '') {
            errorMessages.push('Name is required.');
            isValid = false;
        }
    
        // Validate email
        if (email === '' || !/\S+@\S+\.\S+/.test(email)) {
            errorMessages.push('A valid email address is required.');
            isValid = false;
        }
    
        // Validate mobile
        if (mobile === '' || !/^\d{10}$/.test(mobile)) {
            alert('A valid mobile number (10 digits) is required.');
            isValid = false;
        }
    
        // Validate experience details
        companySections.forEach((section, index) => {
            const years = section.querySelector(`input[name="companies[${index}][years]"]`).value.trim();
            const months = section.querySelector(`input[name="companies[${index}][months]"]`).value.trim();
    
            if (years === '' || months === '') {
                errorMessages.push(`Please fill out all experience details for company ${index + 1}.`);
                isValid = false;
            }
        });
    
        // Show all error messages in one alert
        if (errorMessages.length > 0) {
            alert(errorMessages.join('\n'));
            event.preventDefault(); // Prevent form submission if validation fails
        }
    
        return isValid;
    }
    

    function displayError(inputId, message) {
        const input = document.getElementById(inputId);
        let errorMsg = input.nextElementSibling;
        if (!errorMsg || !errorMsg.classList.contains('error-message')) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            input.parentNode.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
    }
});
