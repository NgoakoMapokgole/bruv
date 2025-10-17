document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll(".toggle-comments").forEach(button => {
        button.addEventListener("click", function() {
            const commentSection = this.nextElementSibling; // The section to show/hide
            const isVisible = commentSection.style.display === "block";

            if (isVisible) {
                commentSection.style.display = "none";
                this.textContent = this.textContent.replace("Hide", "Show");
            } else {
                commentSection.style.display = "block";
                this.textContent = this.textContent.replace("Show", "Hide");
            }
        });
    });

    // Read More functionality
    const readMoreButtons = document.querySelectorAll('.read-more-btn');
    
    readMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reviewCard = this.closest('.review-card');
            const content = reviewCard.querySelector('.review-content');
            const isExpanded = content.classList.contains('expanded');
            
            if (isExpanded) {
                content.classList.remove('expanded');
                this.classList.remove('expanded');
                reviewCard.classList.remove('expanded');
            } else {
                content.classList.add('expanded');
                this.classList.add('expanded');
                reviewCard.classList.add('expanded');
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    // Like button
    document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const postID = btn.dataset.postid;
            const action = btn.classList.contains('like-btn') ? 'like' : 'dislike';
            
            fetch('ajaxActions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `postID=${postID}&action=${action}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.error) {
                    const span = btn.querySelector('span');
                    span.textContent = action === 'like' ? data.likes : data.dislikes;

                    // small animation
                    btn.classList.add('clicked');
                    setTimeout(() => btn.classList.remove('clicked'), 300);
                }
            });
        });
    });

    // Comment form
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const postID = form.dataset.postid;
            const input = form.querySelector('input[name="new_comment"]');
            const content = input.value.trim();
            if (!content) return;

            fetch('ajaxActions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `postID=${postID}&action=comment&content=${encodeURIComponent(content)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.error) {
                    // Append comment to list
                    const commentList = form.closest('.comments').querySelector('.comment-list');
                    const comment = document.createElement('article');
                    comment.classList.add('comment');
                    comment.innerHTML = `<strong>${data.userName}:</strong> <span>${data.content}</span> <time class="comment-date">${data.date}</time>`;
                    commentList.appendChild(comment);

                    // Animate new comment
                    comment.style.opacity = 0;
                    setTimeout(() => comment.style.opacity = 1, 10);

                    input.value = '';
                }
            });
        });
    });
});
