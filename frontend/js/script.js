document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    function validateEmail(email) {
        const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(String(email).toLowerCase());
    }

    // Mobile number validation
    function validateMobileNumber(mobile) {
        const re = /^[0-9]{10}$/; // Assumes 10-digit mobile number
        return re.test(mobile);
    }

    // Login form validation
    const loginForm = document.querySelector('form[name="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
            }
        });
    }

    // Registration form validation
    const registerForm = document.querySelector('form[name="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const mobile = document.getElementById('mobile_number').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }

            if (!validateMobileNumber(mobile)) {
                e.preventDefault();
                alert('Please enter a valid 10-digit mobile number');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
        });
    }
});