
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form#profileForm");

    form.addEventListener("submit", function(e) {
        let errors = [];

        const userName = form.userName.value.trim();
        const fullName = form.fullName.value.trim();
        const email = form.userEmail.value.trim();
        const phone = form.phone.value.trim();
        const age = parseInt(form.age.value.trim());
        const bio = form.bio.value.trim();
        const profPic = form.profPic.files[0];

        if (userName === "" || userName.length > 50) errors.push("Username is required and max 50 chars.");
        if (fullName === "" || fullName.length > 100) errors.push("Full name is required and max 100 chars.");
        if (email === "" || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) errors.push("Valid email is required.");
        if (phone && !phone.match(/^\+?[0-9]{7,15}$/)) errors.push("Phone must be 7-15 digits and can start with +.");
        if (isNaN(age) || age < 1 || age > 120) errors.push("Age must be between 1 and 120.");
        if (bio.length > 500) errors.push("Bio max length is 500 chars.");
        if (profPic) {
            const allowedTypes = ["image/jpeg","image/png","image/gif"];
            if (!allowedTypes.includes(profPic.type)) errors.push("Profile picture must be JPG, PNG, or GIF.");
        }

        if (errors.length > 0) {
            alert("Please fix the following errors:\n" + errors.join("\n"));
            e.preventDefault();
        }
    });
});

