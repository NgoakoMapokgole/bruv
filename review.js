document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#reviewModal form");
  const category = document.getElementById("category");
  const ratingInputs = document.getElementsByName("rating");
  const tagsField = document.getElementById("tagsField");
  const tagInput = document.getElementById("tagInput");
  const tagContainer = document.getElementById("tagContainer");

  // === Tag addition logic ===
  const tags = [];
  tagInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const value = tagInput.value.trim();
      if (value && !tags.includes(value)) {
        tags.push(value);
        updateTagsField();
        renderTags();
        tagInput.value = "";
      }
    }
  });

  function updateTagsField() {
    tagsField.value = tags.join(","); // store tags as CSV for submission
  }

  function renderTags() {
    tagContainer.innerHTML = "";
    tags.forEach((tag, index) => {
      const tagEl = document.createElement("span");
      tagEl.className = "tag";
      tagEl.textContent = tag + " ×";
      tagEl.addEventListener("click", () => {
        tags.splice(index, 1);
        updateTagsField();
        renderTags();
      });
      tagContainer.appendChild(tagEl);
    });
  }

  // === Form submission validation ===
  form.addEventListener("submit", (e) => {
    let errors = [];

    // Category
    if (category.value === "") errors.push("Please select a category.");

    // Title & Content
    const title = form.querySelector('input[name="title"]').value.trim();
    const content = form.querySelector('textarea[name="content"]').value.trim(); // updated for textarea
    if (!title) errors.push("Please enter a title.");
    if (!content) errors.push("Please enter content.");

    // Rating
    let ratingSelected = Array.from(ratingInputs).some(r => r.checked);
    if (!ratingSelected) errors.push("Please select a rating.");

    // Tags
    if (!tagsField.value || tagsField.value.trim() === "") {
      errors.push("Please add at least one tag.");
    }

    if (errors.length > 0) {
      e.preventDefault();
      alert(errors.join("\n"));
    }
  });
});
