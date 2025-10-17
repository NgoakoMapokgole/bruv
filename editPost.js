document.getElementById("editPostForm").addEventListener("submit", function(event) {
    let errors = [];
    const title = document.getElementById("Title").value.trim();
    const content = document.getElementById("Content").value.trim();
    const rating = parseInt(document.getElementById("rating").value);
    const category = document.getElementById("category").value;
    const replyPermission = document.getElementById("replyPermission").value;

    // Validate Title
    if (title === "") errors.push("Title is required.");
    if (title.length > 200) errors.push("Title cannot exceed 200 characters.");

    // Validate Content
    if (content === "") errors.push("Content is required.");

    // Validate Rating
    if (isNaN(rating) || rating < 1 || rating > 5) {
        errors.push("Rating must be between 1 and 5.");
    }

    // Validate Category
    const allowedCategories = ['Media','Concept','Food','Place','EverythingElse'];
    if (!allowedCategories.includes(category)) {
        errors.push("Invalid category selected.");
    }

    // Validate Reply Permission
    const allowedReply = ['anyone','friends','author'];
    if (!allowedReply.includes(replyPermission)) {
        errors.push("Invalid reply permission selected.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n")); // Show all errors in a popup
        event.preventDefault(); // Stop form submission
    }
});