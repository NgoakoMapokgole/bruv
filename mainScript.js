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



// ========================== Responsive navbar ========================== //
const menuToggle = document.getElementById("menu-toggle");
const navLinks = document.querySelector(".nav-links");
const closeMenuBtn = document.getElementById("close-menu");

menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("active");
});

// Optional: close button inside nav (if you have one)
if (closeMenuBtn) {
    closeMenuBtn.addEventListener("click", () => {
        navLinks.classList.remove("active");
    });
}

// ========================== Mobile search toggle ========================== //
const search = document.getElementById("search-toggle");
const mobileSearch = document.querySelector(".nav-search-mobile");

if (search && mobileSearch) {
    search.addEventListener("click", () => {
        mobileSearch.classList.toggle("active");
    });
}

// ========================== Hide/show header on scroll ========================== //
let prevScrollPos = window.pageYOffset;
const header = document.querySelector("header");

function handleScroll() {
    let currentScrollPos = window.pageYOffset;

    if (document.body.scrollHeight > window.innerHeight) {
        if (currentScrollPos <= 0) {
            header.style.top = "0"; // always show at top
        } else if (prevScrollPos > currentScrollPos) {
            header.style.top = "0"; // scrolling up → show
        } else {
            header.style.top = "-80px"; // scrolling down → hide
        }
    } else {
        header.style.top = "0"; // page too short → always show
    }

    prevScrollPos = currentScrollPos;
}

// Show navbar immediately on load
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


document.addEventListener("DOMContentLoaded", () => {
  console.log("JS loaded");

  // ===== MODAL HANDLING =====
  const modal = document.getElementById("reviewModal");
  const openBtn = document.getElementById("openReviewBtn");
  const closeBtn = modal.querySelector(".close");

  let isLoggedIn = false;
  let userId = null;

  fetch('checkLogin.php')
      .then(res => res.json())
      .then(data => {
          isLoggedIn = data.isLoggedIn;
          userId = data.userId;
      });

  if (openBtn && modal) {
    openBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (!isLoggedIn) {
        window.location.href = "Login.php";
      } else {
        modal.showModal();
      }
    });
  }

  if (closeBtn && modal) {
    closeBtn.addEventListener("click", () => modal.close());

    // Close when clicking outside the dialog content
    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.close();
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

  // Populate tag suggestions based on category
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

  // Add tag on Enter
  tagInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && tagInput.value.trim() !== "") {
      e.preventDefault();
      addTag(tagInput.value.trim());
      tagInput.value = "";
    }
  });

  function addTag(text) {
    if (tags.includes(text)) return;
    tags.push(text);

    const tagEl = document.createElement("span");
    tagEl.className = "tag";
    tagEl.textContent = text;

    const removeBtn = document.createElement("button");
    removeBtn.textContent = "×";
    removeBtn.type = "button";
    removeBtn.onclick = () => {
      tags = tags.filter(t => t !== text);
      tagEl.remove();
      updateHiddenField();
    };

    tagEl.appendChild(removeBtn);
    tagContainer.appendChild(tagEl);
    updateHiddenField();
  }

  function updateHiddenField() {
    tagsField.value = tags.join(",");
  }

  // ===== MEDIA PREVIEW =====
  const mediaInput = document.getElementById("media");
  const previewArea = document.getElementById("previewArea");

  if (mediaInput) {
    mediaInput.addEventListener("change", function () {
      if (!previewArea) return;
      previewArea.innerHTML = "";

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

  function showFlashMessage(message, isSuccess = true, duration = 3000) {
      const flash = document.getElementById("flashMessage");
      flash.textContent = message;
      flash.className = "flash-message"; // reset classes
      if (!isSuccess) flash.classList.add("error");
      flash.style.display = "block";
      flash.style.opacity = 1;

      // Fade out after duration
      setTimeout(() => {
          flash.style.opacity = 0;
          setTimeout(() => {
              flash.style.display = "none";
          }, 500);
      }, duration);
  }

  // Show flash if reviewStatus exists
  if (reviewStatus) {
      showFlashMessage(reviewStatus.message, reviewStatus.success);
  }

});

document.addEventListener('DOMContentLoaded', function() {
    // Like buttons
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', handleLikeClick);
    });

    // Dislike buttons
    document.querySelectorAll('.dislike-btn').forEach(button => {
        button.addEventListener('click', handleDislikeClick);
    });
});

function handleLikeClick(event) {
    const likeBtn = event.currentTarget;
    const postId = likeBtn.dataset.postid;
    const dislikeBtn = document.querySelector(`.dislike-btn[data-postid="${postId}"]`);

    fetch('ajaxActions.php', { // <-- replace with your PHP path
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `postID=${postId}&action=like`
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) return console.error(data.error);

        // Update counts
        likeBtn.querySelector('.like-count').textContent = data.likes;
        dislikeBtn.querySelector('.dislike-count').textContent = data.dislikes;

        // Update active classes based on PHP response
        if (data.likes > 0) {
            likeBtn.classList.add('active');
        } else {
            likeBtn.classList.remove('active');
        }

        if (data.dislikes > 0) {
            dislikeBtn.classList.add('active');
        } else {
            dislikeBtn.classList.remove('active');
        }
    })
    .catch(err => console.error(err));
}

function handleDislikeClick(event) {
    const dislikeBtn = event.currentTarget;
    const postId = dislikeBtn.dataset.postid;
    const likeBtn = document.querySelector(`.like-btn[data-postid="${postId}"]`);

    fetch('ajaxActions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `postID=${postId}&action=dislike`
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) return console.error(data.error);

        // Update counts
        dislikeBtn.querySelector('.dislike-count').textContent = data.dislikes;
        likeBtn.querySelector('.like-count').textContent = data.likes;

        // Update active classes based on PHP response
        if (data.dislikes > 0) {
            dislikeBtn.classList.add('active');
        } else {
            dislikeBtn.classList.remove('active');
        }

        if (data.likes > 0) {
            likeBtn.classList.add('active');
        } else {
            likeBtn.classList.remove('active');
        }
    })
    .catch(err => console.error(err));
}
