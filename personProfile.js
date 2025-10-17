// Tab navigation functionality
const navButtons = document.querySelectorAll('.profile-nav button');
const sections = document.querySelectorAll('.profile-section, .reviews-grid'); // include reviews-grid

navButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        navButtons.forEach(btn => btn.classList.remove('active'));

        // Hide all sections
        sections.forEach(section => section.style.display = 'none');

        // Activate clicked button
        button.classList.add('active');

        // Show target section
        const sectionId = button.getAttribute('data-section');
        const targetSection = document.getElementById(sectionId) || document.querySelector(`.${sectionId}`);
        if (targetSection) targetSection.style.display = 'block';
    });
});

// Initialize: show the first section by default
document.addEventListener('DOMContentLoaded', () => {
    navButtons[0].click();
});

// Filter functionality
const mediaTypeFilter = document.getElementById('media-type');
const sortByFilter = document.getElementById('sort-by');

if (mediaTypeFilter && sortByFilter) {
    mediaTypeFilter.addEventListener('change', applyFilters);
    sortByFilter.addEventListener('change', applyFilters);
}

function applyFilters() {
    // This would filter and sort reviews in a real implementation
    console.log('Filters applied:', {
        mediaType: mediaTypeFilter.value,
        sortBy: sortByFilter.value
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const followBtn = document.getElementById('btn-follow');
    if (!followBtn) return;

    followBtn.addEventListener('click', () => {
        const userID = followBtn.dataset.userid;

        fetch('followAction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `userID=${userID}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                followBtn.textContent = data.action === 'follow' ? 'Unfollow' : 'Follow';
                // Optionally update follower count
                const followersStat = document.querySelector('.profile-stats .stat:nth-child(3) .stat-number');
                if(followersStat) followersStat.textContent = data.followersCount;
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    });
});
