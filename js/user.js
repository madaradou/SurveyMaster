document.addEventListener("DOMContentLoaded", function() {
    // Register form validation
    var registerForm = document.querySelector('form[action="signup.php"]');
    if (registerForm) {
        registerForm.addEventListener("submit", function(e) {
            var firstName = registerForm.elements["first_name"].value.trim();
            var lastName = registerForm.elements["last_name"].value.trim();
            var email = registerForm.elements["email"].value.trim();
            var password = registerForm.elements["password"].value;
            var errorMessages = [];

            if (firstName.length === 0) {
                errorMessages.push("First name is required.");
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(firstName)) {
                errorMessages.push("First name contains invalid characters.");
            } else if (firstName.length < 2) {
                errorMessages.push("First name must be at least 2 characters.");
            }

            if (lastName.length === 0) {
                errorMessages.push("Last name is required.");
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(lastName)) {
                errorMessages.push("Last name contains invalid characters.");
            } else if (lastName.length < 2) {
                errorMessages.push("Last name must be at least 2 characters.");
            }

            if (email.length === 0) {
                errorMessages.push("Email is required.");
            } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                errorMessages.push("Invalid email format.");
            }

            if (password.length === 0) {
                errorMessages.push("Password is required.");
            } else if (password.length < 6) {
                errorMessages.push("Password must be at least 6 characters long.");
            }

            var prevError = document.querySelector('.js-error');
            if (prevError) prevError.remove();

            if (errorMessages.length > 0) {
                e.preventDefault();
                var errorDiv = document.createElement('div');
                errorDiv.className = 'error js-error';
                errorDiv.innerHTML = errorMessages.join('<br>');
                registerForm.parentNode.insertBefore(errorDiv, registerForm);
            }
        });
    }

    // Profile form validation
    var updateForm = document.getElementById('updateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            var firstName = updateForm.elements['first_name'].value.trim();
            var lastName = updateForm.elements['last_name'].value.trim();
            var email = updateForm.elements['email'].value.trim();
            var errorMessages = [];

            if (firstName.length === 0) {
                errorMessages.push('First name is required.');
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(firstName)) {
                errorMessages.push('First name contains invalid characters.');
            } else if (firstName.length < 2) {
                errorMessages.push('First name must be at least 2 characters.');
            }

            if (lastName.length === 0) {
                errorMessages.push('Last name is required.');
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(lastName)) {
                errorMessages.push('Last name contains invalid characters.');
            } else if (lastName.length < 2) {
                errorMessages.push('Last name must be at least 2 characters.');
            }

            var emailPattern = /^\S+@\S+\.\S+$/;
            if (email.length === 0) {
                errorMessages.push('Email is required.');
            } else if (!emailPattern.test(email)) {
                errorMessages.push('Invalid email format.');
            }

            var errorDiv = document.getElementById('update-error');
            if (errorMessages.length > 0) {
                e.preventDefault();
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'update-error';
                    errorDiv.className = 'error-message';
                    updateForm.parentNode.insertBefore(errorDiv, updateForm);
                }
                errorDiv.innerHTML = errorMessages.join('<br>');
                errorDiv.style.display = 'block';
            } else {
                if (errorDiv) errorDiv.style.display = 'none';
            }
        });
    }
});

    // Login form validation (email/password only; keep reCAPTCHA intact)
    var loginForm = document.querySelector('form[action=""]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            var email = loginForm.elements['email'] ? loginForm.elements['email'].value.trim() : '';
            var password = loginForm.elements['password'] ? loginForm.elements['password'].value : '';
            var errorMessages = [];

            if (email.length === 0) {
                errorMessages.push('Email is required.');
            } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                errorMessages.push('Invalid email format.');
            }

            if (password.length === 0) {
                errorMessages.push('Password is required.');
            }

            var prevError = document.querySelector('.login-js-error');
            if (prevError) prevError.remove();

            if (errorMessages.length > 0) {
                e.preventDefault();
                var errorDiv = document.createElement('div');
                errorDiv.className = 'error login-js-error';
                errorDiv.innerHTML = errorMessages.join('<br>');
                loginForm.parentNode.insertBefore(errorDiv, loginForm);
            }
        });
    }
