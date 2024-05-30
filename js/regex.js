function isValidNumberVerify(inputElement) {
    let errorMessage;
    let input = inputElement.value;

    if (input === '') {
        errorMessage = document.getElementById(inputElement.id + '-error');
        if (errorMessage) {
            errorMessage.textContent = '';
            inputElement.parentNode.removeChild(errorMessage);
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^\d{6}$/;
    let isValid = regex.test(input);
    errorMessage = document.getElementById(inputElement.id + '-error');

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = inputElement.id + '-error';
            errorMessage.className = 'text-danger';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a number in the format 000000.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = inputElement.id + '-error';
            errorMessage.className = 'text-success';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidNumber(inputElement) {
    let errorMessageDiv;
    let input = inputElement.value;
    let errorMessageDivId = inputElement.id + '-error';
    errorMessageDiv = document.getElementById(errorMessageDivId);

    let inputGroup = inputElement.closest('.input-group');

    if (input === '') {
        if (errorMessageDiv) {
            errorMessageDiv.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^\d{6}$/;
    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessageDiv) {
            errorMessageDiv = document.createElement('div');
            errorMessageDiv.id = errorMessageDivId;
            errorMessageDiv.className = 'text-danger error-message';
            if (inputGroup.nextSibling) {
                inputGroup.parentNode.insertBefore(errorMessageDiv, inputGroup.nextSibling);
            } else {
                inputGroup.parentNode.appendChild(errorMessageDiv);
            }
        }
        errorMessageDiv.textContent = 'Invalid input. Please enter a number in the format 000000.';
        errorMessageDiv.classList.remove('text-success');
        errorMessageDiv.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessageDiv) {
            errorMessageDiv = document.createElement('div');
            errorMessageDiv.id = errorMessageDivId;
            errorMessageDiv.className = 'text-success error-message';
            if (inputGroup.nextSibling) {
                inputGroup.parentNode.insertBefore(errorMessageDiv, inputGroup.nextSibling);
            } else {
                inputGroup.parentNode.appendChild(errorMessageDiv);
            }
        }
        errorMessageDiv.textContent = '';
        errorMessageDiv.classList.remove('text-danger');
        errorMessageDiv.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}


function isValidPassword(inputElement) {
    let errorMessage;
    let input = inputElement.value;
    if (input === '') {
        errorMessage = document.getElementById(inputElement.id + '-error');
        if (errorMessage) {
            errorMessage.textContent = '';
            inputElement.parentNode.removeChild(errorMessage);
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }
    let regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
    let isValid = regex.test(input);
    errorMessage = document.getElementById(inputElement.id + '-error');
    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = inputElement.id + '-error';
            errorMessage.className = 'text-danger';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Password must contain at least one uppercase letter, one lowercase letter and one number.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = inputElement.id + '-error';
            errorMessage.className = 'text-success';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidName(inputElement) {
    let errorMessage;
    let input = inputElement.value.trim();

    let errorMessageDivId = inputElement.id + '-error';
    errorMessage = document.getElementById(errorMessageDivId);

    if (input === '') {
        if (errorMessage) {
            errorMessage.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^\p{Lu}[\p{Ll}\p{L}]*$/u;

    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-danger error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a valid name.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-success error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidEmail(inputElement) {
    let errorMessage;
    let input = inputElement.value.trim();

    let errorMessageDivId = inputElement.id + '-error';
    errorMessage = document.getElementById(errorMessageDivId);

    if (input === '') {
        if (errorMessage) {
            errorMessage.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-zA-Z]{2,}$/;
    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-danger error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a valid email.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-success error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidTelephone(inputElement) {
    let errorMessage;
    let input = inputElement.value.trim();

    let errorMessageDivId = inputElement.id + '-error';
    errorMessage = document.getElementById(errorMessageDivId);

    if (input === '') {
        if (errorMessage) {
            errorMessage.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^\+421\d{9}$/;
    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-danger error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a valid telephone number.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-success error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidInput(inputElement) {
    let errorMessage;
    let input = inputElement.value.trim();

    let errorMessageDivId = inputElement.id + '-error';
    errorMessage = document.getElementById(errorMessageDivId);

    if (input === '') {
        if (errorMessage) {
            errorMessage.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^[\p{L}\d\s,']+$/u;
    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-danger error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a valid string.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-success error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function isValidText(inputElement) {
    let errorMessage;
    let input = inputElement.value.trim();

    let errorMessageDivId = inputElement.id + '-error';
    errorMessage = document.getElementById(errorMessageDivId);

    if (input === '') {
        if (errorMessage) {
            errorMessage.remove();
        }
        inputElement.classList.remove('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    }

    let regex = /^[^\x00-\x1F\x7F-\x9F]*$/u;
    let isValid = regex.test(input);

    if (!isValid) {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-danger error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = 'Invalid input. Please enter a valid text.';
        errorMessage.classList.remove('text-success');
        errorMessage.classList.add('text-danger');
        inputElement.classList.add('is-invalid');
        inputElement.classList.remove('is-valid');
        return false;
    } else {
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = errorMessageDivId;
            errorMessage.className = 'text-success error-message';
            inputElement.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = '';
        errorMessage.classList.remove('text-danger');
        errorMessage.classList.add('text-success');
        inputElement.classList.remove('is-invalid');
        inputElement.classList.add('is-valid');
        return true;
    }
}

function checkForm(e) {
    e.preventDefault();

    let form = e.target;
    let inputs = form.querySelectorAll('input');
    let isValid = true;

    inputs.forEach(input => {
        if (input.classList.contains('is-invalid')) {
            isValid = false;
        }
    });
    if (isValid) {
        form.submit();
    } else {
        toastr.error('Please fill in the form correctly.');
    }
}