//==============search bar =================================//
window.addEventListener("scroll", function () {
    const navbar = document.querySelector(".navbar");
    if (window.scrollY > 100) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  });

//added by anesipho
const searchToggle = document.getElementById('search-toggle');
const navSearch = document.querySelector('.nav-search');

searchToggle.addEventListener('click', () => {
    navSearch.classList.toggle('active');
});



//========================== Responsive nav bar ==================//
const toggle = document.getElementById("menu-toggle");
const navLinks = document.querySelector(".nav-links");

toggle.addEventListener("click", () => {
  navLinks.classList.toggle("active");
});

let prevScrollpos = window.pageYOffset;
const header = document.querySelector("header");

function handleScroll() {
  let currentScrollPos = window.pageYOffset;

  // Only apply scroll logic if page is tall enough to scroll
  if (document.body.scrollHeight > window.innerHeight) {
    if (currentScrollPos <= 0) {
      header.style.top = "0"; // always show at top
    } else if (prevScrollpos > currentScrollPos) {
      header.style.top = "0"; // scrolling up → show
    } else {
      header.style.top = "-80px"; // scrolling down → hide
    }
  } else {
    // Not scrollable → always show
    header.style.top = "0";
  }

  prevScrollpos = currentScrollPos;
}

// Make sure navbar is shown immediately on load
window.addEventListener("load", () => {
  header.style.top = "0";
});

window.addEventListener("scroll", handleScroll);

//=================the comments and reviews all that ===========//
// Like system
document.querySelectorAll(".like-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const count = btn.querySelector(".like-count");
    count.textContent = parseInt(count.textContent) + 1;
  });
});

// Comment system
document.querySelectorAll(".comment-submit").forEach(btn => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".review-card");
    const input = card.querySelector(".comment-input");
    const list = card.querySelector(".comment-list");
    if(input.value.trim() !== "") {
      const li = document.createElement("li");
      li.innerHTML = `<strong>Anonymous:</strong> ${input.value}`;
      list.appendChild(li);
      input.value = "";
    }
  });
});


//===================Review Form ===============================//
document.addEventListener("DOMContentLoaded", () => {
  console.log("JS loaded"); // should appear in browser console
  // ===== MODAL HANDLING =====
  const modal = document.getElementById("reviewModal");
  const openBtn = document.getElementById("openReviewBtn");
  const closeBtn = document.querySelector("#reviewModal .close");

  if (openBtn && modal) {
    openBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (!isLoggedIn) {
        window.location.href = "Login.php";
      } else {
        modal.showModal();
      }
    });
    console.log({ modal, openBtn, closeBtn }); // check if elements are found
  }

  if (closeBtn && modal) {
    closeBtn.addEventListener("click", () => modal.close());

    //close when clicking outside
    modal.addEventListener("click", (e) => {
      const rect = modal.getBoundingClientRect();
      const isInDialog =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;

      if (!isInDialog) modal.close();
    });
  }

  // ===== TAGS & CATEGORIES =====
  const categories = {
    Place: [
      "City", "Province", "Country", "University", "Campus", "Landmark",
      "Neighborhood", "Park", "Shopping Mall", "Historical Site",
      "Tourist Attraction", "Beach", "Mountain", "Lake", "Transport Hub"
    ],
    Food: [
      "Cuisine", "Restaurant", "Café", "Street Food", "Vegetarian", "Vegan",
      "Halal", "Kosher", "Dessert", "Snacks", "Drinks", "Cheap", "Mid-range",
      "Expensive", "Service", "Atmosphere", "Portion Size", "Delivery",
      "Takeaway", "Local", "Traditional", "Fusion"
    ],
    Media: [
      "Movie", "Series", "Documentary", "Action", "Drama", "Comedy", "Horror",
      "Romance", "Sci-Fi", "Fantasy", "Actor", "Director", "Author", "Artist",
      "Netflix", "YouTube", "Spotify", "Music", "Album", "Song", "Game",
      "Podcast", "Book", "Manga", "Comic", "English", "Japanese", "Spanish"
    ],
    Concept: [
      "Happiness", "Sadness", "Anger", "Calm", "Friendship", "Love", "Family",
      "Loneliness", "Creativity", "Productivity", "Inspiration", "Freedom",
      "Restriction", "Tradition", "Modernity", "Nature", "Technology",
      "Dreams", "Sleep", "Memories", "Rain", "Sunshine", "Winter", "Summer"
    ],
    EverythingElse: [
      "Funny", "Random", "Weird", "Trending", "Meme", "Viral", "Challenge",
      "Dare", "Hidden Gem", "Unexpected Find", "DIY", "Life Hack", "Event",
      "Festival", "Experience", "Adventure", "Pets", "Animals", "Mystery",
      "Strange Encounter"
    ]
  };

  const categorySelect = document.getElementById("category");
  const tagInput = document.getElementById("tagInput");
  const tagSuggestions = document.getElementById("tagSuggestions");
  const tagContainer = document.getElementById("tagContainer");
  const tagsField = document.getElementById("tagsField");

  let tags = [];

  if (categorySelect && tagSuggestions) {
    categorySelect.addEventListener("change", () => {
      const selected = categorySelect.value;
      tagSuggestions.innerHTML = "";

      if (categories[selected]) {
        categories[selected].forEach(tag => {
          const option = document.createElement("option");
          option.value = tag;
          tagSuggestions.appendChild(option);
        });
      }
    });
  }

  if (tagInput) {
    tagInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && tagInput.value.trim() !== "") {
        e.preventDefault();
        addTag(tagInput.value.trim());
        tagInput.value = "";
      }
    });
  }

  function addTag(text) {
    if (tags.includes(text)) return;
    tags.push(text);

    const tag = document.createElement("span");
    tag.textContent = text;
    tag.className = "tag";

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "×";
    removeBtn.onclick = () => {
      tags = tags.filter(t => t !== text);
      tag.remove();
      updateHiddenField();
    };

    tag.appendChild(removeBtn);
    tagContainer.appendChild(tag);

    updateHiddenField();
  }

  function updateHiddenField() {
    if (tagsField) tagsField.value = tags.join(",");
  }

  // ===== MEDIA PREVIEW =====
  const mediaInput = document.getElementById("media");
  if (mediaInput) {
    mediaInput.addEventListener("change", function () {
      const previewArea = document.getElementById("previewArea");
      if (!previewArea) return;

      previewArea.innerHTML = ""; // clear previous previews

      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          let element;
          if (file.type.startsWith("image/")) {
            element = document.createElement("img");
            element.src = e.target.result;
            element.style.maxWidth = "120px";
            element.style.margin = "5px";
          } else if (file.type.startsWith("video/")) {
            element = document.createElement("video");
            element.src = e.target.result;
            element.controls = true;
            element.style.maxWidth = "200px";
            element.style.margin = "5px";
          } else if (file.type.startsWith("audio/")) {
            element = document.createElement("audio");
            element.src = e.target.result;
            element.controls = true;
            element.style.display = "block";
            element.style.margin = "5px 0";
          }
          previewArea.appendChild(element);
        };
        reader.readAsDataURL(file);
      });
    });
  }
});
