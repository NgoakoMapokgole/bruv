// ---------- EMAIL VALIDATION ----------
let emailMsg = document.createElement("span");
let breaker = document.createElement("br");
let emailInput = document.getElementById("login-email");

emailInput.parentNode.insertBefore(breaker, emailInput.nextSibling);
breaker.parentNode.insertBefore(emailMsg, breaker.nextSibling);

emailInput.addEventListener("change", () => {
    let emailValue = emailInput.value.trim();
    let pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!pattern.test(emailValue)) {
        emailMsg.innerHTML = "Incorrect Format for email";
        emailMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        emailMsg.innerHTML = "Correct Format for email";
        emailMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});

// ---------- LOGIN PASSWORD VALIDATION ----------
let passwordMsg = document.createElement("span");
let breakers = document.createElement("br");
let passwordInput = document.getElementById("login-password");

passwordInput.parentNode.insertBefore(breakers, passwordInput.nextSibling);
breakers.parentNode.insertBefore(passwordMsg, breakers.nextSibling);

passwordInput.addEventListener("change", () => {
    let pValue = passwordInput.value.trim();
    let pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;

    if (!pattern.test(pValue)) {
        passwordMsg.innerHTML = "Incorrect Password format<br><small>Password must be at least 8 characters, include upper and lower case letters, a number, and a special character.</small>";
        passwordMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        passwordMsg.innerHTML = "Correct Password format";
        passwordMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});


// ---------- EMAIL VALIDATION ----------
let emailMsg1 = document.createElement("span");
let breaker1 = document.createElement("br");
let emailInput1 = document.getElementById("signup-email");

emailInput1.parentNode.insertBefore(breaker1, emailInput1.nextSibling);
breaker1.parentNode.insertBefore(emailMsg1, breaker1.nextSibling);

emailInput1.addEventListener("change", () => {
    let emailValue1 = emailInput1.value.trim();
    let pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!pattern.test(emailValue1)) {
        emailMsg1.innerHTML = "Incorrect Format for email";
        emailMsg1.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        emailMsg1.innerHTML = "Correct Format for email";
        emailMsg1.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});

let fullNameMsg = document.createElement("span");
let brFullName = document.createElement("br");
let fullNameInput = document.getElementById("signup-Aname");

// Insert the message span after the full name input
fullNameInput.parentNode.insertBefore(brFullName, fullNameInput.nextSibling);
brFullName.parentNode.insertBefore(fullNameMsg, brFullName.nextSibling);

fullNameInput.addEventListener("change", () => {
    let nameValue = fullNameInput.value.trim();

    // At least two words, only letters, spaces, hyphens, and apostrophes
    let pattern = /^[a-zA-Z]+([ '-][a-zA-Z]+)+$/;

    if (!pattern.test(nameValue)) {
        fullNameMsg.innerHTML = "Please enter your full name (first and last).";
        fullNameMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        fullNameMsg.innerHTML = "Valid full name.";
        fullNameMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});


const input = document.getElementById("myDate");
const dobMsg = document.createElement("span");
const dobBr = document.createElement("br");

// Insert message span after the input
input.parentNode.insertBefore(dobBr, input.nextSibling);
dobBr.parentNode.insertBefore(dobMsg, dobBr.nextSibling);

// Set max date to today to prevent future selection
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
input.max = `${yyyy}-${mm}-${dd}`;

// Minimum age check: 16 years
input.addEventListener("change", () => {
    const selectedDate = new Date(input.value);
    const today = new Date();

    const age = today.getFullYear() - selectedDate.getFullYear();
    const m = today.getMonth() - selectedDate.getMonth();
    const dayDiff = today.getDate() - selectedDate.getDate();

    const actualAge = (m < 0 || (m === 0 && dayDiff < 0)) ? age - 1 : age;

    if (actualAge < 16) {
        dobMsg.innerHTML = "You must be at least 16 years old.";
        dobMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        dobMsg.innerHTML = "Date of birth is valid.";
        dobMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});
// ---------- SIGNUP USERNAME VALIDATION ----------
let nameMsg = document.createElement("span");
let brName = document.createElement("br");
let nameInput = document.getElementById("signup-name");

nameInput.parentNode.insertBefore(brName, nameInput.nextSibling);
brName.parentNode.insertBefore(nameMsg, brName.nextSibling);

nameInput.addEventListener("change", () => {
    let nameValue = nameInput.value.trim();
    if (nameValue.length < 3) {
        nameMsg.innerHTML = "Username must be at least 3 characters.";
        nameMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
     
    } else {
        nameMsg.innerHTML = "Valid username.";
        nameMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});

// ---------- SIGNUP PHONE VALIDATION ----------
let phoneMsg = document.createElement("span");
let brPhone = document.createElement("br");
let phoneInput = document.getElementById("signup-phone");

phoneInput.parentNode.insertBefore(brPhone, phoneInput.nextSibling);
brPhone.parentNode.insertBefore(phoneMsg, brPhone.nextSibling);

// Change event — validate format
phoneInput.addEventListener("change", () => {
    let phoneValue = phoneInput.value.trim();
    let phonePattern = /^\d{3}-\d{3}-\d{4}$/;

    if (!phonePattern.test(phoneValue)) {
        phoneMsg.innerHTML = "Phone number must be in the format 123-456-7890.";
        phoneMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        phoneMsg.innerHTML = "Valid phone number.";
        phoneMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});

// Blur event — auto-format raw digits
phoneInput.addEventListener("blur", () => {
    let raw = phoneInput.value.trim().replace(/\D/g, ''); // Remove non-digits

    if (raw.length === 10) {
        // Format to 123-456-7890
        let formatted = `${raw.substring(0, 3)}-${raw.substring(3, 6)}-${raw.substring(6)}`;
        phoneInput.value = formatted;

        // Optional: Trigger validation again
        let event = new Event('change');
        phoneInput.dispatchEvent(event);
    } else if (raw.length > 0 && raw.length !== 10) {
        phoneMsg.innerHTML = "Phone number must have exactly 10 digits.";
        phoneMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    }
});
 

// ---------- SIGNUP PASSWORD VALIDATION ----------
let signupPasswordMsg = document.createElement("span");
let brSignupPassword = document.createElement("br");
let signupPasswordInput = document.getElementById("signup-password");

signupPasswordInput.parentNode.insertBefore(brSignupPassword, signupPasswordInput.nextSibling);
brSignupPassword.parentNode.insertBefore(signupPasswordMsg, brSignupPassword.nextSibling);

signupPasswordInput.addEventListener("change", () => {
    let value = signupPasswordInput.value.trim();
    let pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;

    if (!pattern.test(value)) {
        signupPasswordMsg.innerHTML = "Weak password. Must include upper, lower, digit, special char, 8+ characters.";
        signupPasswordMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        signupPasswordMsg.innerHTML = "Strong password.";
        signupPasswordMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});

// ---------- CONFIRM PASSWORD MATCH ----------
let confirmMsg = document.createElement("span");
let brConfirm = document.createElement("br");
let confirmInput = document.getElementById("confirm-password");

confirmInput.parentNode.insertBefore(brConfirm, confirmInput.nextSibling);
brConfirm.parentNode.insertBefore(confirmMsg, brConfirm.nextSibling);

confirmInput.addEventListener("change", () => {
    let original = document.getElementById("signup-password").value.trim();
    let confirm = confirmInput.value.trim();

    if (original !== confirm) {
        confirmMsg.innerHTML = "Passwords do not match.";
        confirmMsg.style.color = "red";
        document.getElementsByTagName("button").disabled=true;
    } else {
        confirmMsg.innerHTML = "Passwords match.";
        confirmMsg.style.color = "green";
        document.getElementsByTagName("button").disabled=false;
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const loginSection = document.getElementById('loginSection');
    const signupSection = document.getElementById('signupSection');
    const showSignup = document.getElementById('showSignup');
    const showLogin = document.getElementById('showLogin');

    // Switch to signup
    if (showSignup) {
        showSignup.addEventListener('click', function() {
            loginSection.style.display = 'none';
            signupSection.style.display = 'block';
        });
    }

    // Switch back to login
    if (showLogin) {
        showLogin.addEventListener('click', function() {
            signupSection.style.display = 'none';
            loginSection.style.display = 'block';
        });
    }
});
