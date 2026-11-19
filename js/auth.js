
document.addEventListener('DOMContentLoaded', function () {

    // Form elements
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const form = document.getElementById('regForm');

    // Password requirement elements
    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-upper');
    const reqLower = document.getElementById('req-lower');
    const reqDigit = document.getElementById('req-digit');
    const reqSpecial = document.getElementById('req-special');

    // Feedback elements
    const confirmMsg = document.getElementById('confirmMsg');
    const reqContainer = document.getElementById('password-requirements');

    // Stop if the required registration elements are unavailable
    if (
        !passwordInput ||
        !confirmInput ||
        !form ||
        !reqLength ||
        !reqUpper ||
        !reqLower ||
        !reqDigit ||
        !reqSpecial ||
        !confirmMsg ||
        !reqContainer
    ) {
        return;
    }

    // Password validation patterns
    const upperCasePattern = /[A-Z]/;
    const lowerCasePattern = /[a-z]/;
    const digitPattern = /[0-9]/;
    const specialCharPattern = /[^A-Za-z0-9]/;

    // Check whether the password meets all requirements
    function validatePassword(password) {

        return {
            length: password.length >= 8,
            upper: upperCasePattern.test(password),
            lower: lowerCasePattern.test(password),
            digit: digitPattern.test(password),
            special: specialCharPattern.test(password)
        };
    }

    // Update the visual status of a password requirement
    function updateRequirement(element, isValid) {

        element.classList.toggle('valid', isValid);
        element.classList.toggle('invalid', !isValid);
    }

    // Check and display individual password requirements
    function checkPassword() {

        const pwdValue = passwordInput.value;
        const requirements = validatePassword(pwdValue);

        updateRequirement(reqLength, requirements.length);
        updateRequirement(reqUpper, requirements.upper);
        updateRequirement(reqLower, requirements.lower);
        updateRequirement(reqDigit, requirements.digit);
        updateRequirement(reqSpecial, requirements.special);

        return Object.values(requirements).every(Boolean);
    }

    // Check whether both password fields match
    function checkConfirmPassword() {

        const pwdValue = passwordInput.value;
        const confirm = confirmInput.value;

        if (pwdValue && confirm) {

            if (pwdValue === confirm) {

                confirmMsg.textContent = 'Passwords match!';
                confirmMsg.classList.remove('hidden', 'error');
                confirmMsg.classList.add('success');

                return true;

            } else {

                confirmMsg.textContent = 'Passwords do not match!';
                confirmMsg.classList.remove('hidden', 'success');
                confirmMsg.classList.add('error');

                return false;
            }

        } else {

            confirmMsg.textContent = '';
            confirmMsg.classList.remove('success', 'error');
            confirmMsg.classList.add('hidden');

            return false;
        }
    }

    // Show password requirements when the password field receives focus
    passwordInput.addEventListener('focus', function () {

        reqContainer.classList.remove('hidden');

    });

    // Hide requirements when the password field is empty and loses focus
    passwordInput.addEventListener('blur', function () {

        if (passwordInput.value === '') {

            reqContainer.classList.add('hidden');

        }

    });

    // Live password validation
    passwordInput.addEventListener('input', function () {

        checkPassword();
        checkConfirmPassword();

    });

    // Live password confirmation validation
    confirmInput.addEventListener('input', function () {

        checkConfirmPassword();

    });

    // Validate everything before submitting the registration form
    form.addEventListener('submit', function (event) {

        const passwordValid = checkPassword();
        const passwordsMatch =
            passwordInput.value === confirmInput.value &&
            confirmInput.value !== '';

        if (!passwordValid) {

            event.preventDefault();

            reqContainer.classList.remove('hidden');

            alert(
                'Please ensure your password meets all requirements before submitting.'
            );

            passwordInput.focus();

            return;
        }

        if (!passwordsMatch) {

            event.preventDefault();

            checkConfirmPassword();

            alert('Passwords do not match.');

            confirmInput.focus();

            return;
        }

    });

});
