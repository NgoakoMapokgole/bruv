const navButtons = document.querySelectorAll('.profile-nav button');
const sections = document.querySelectorAll('.profile-section');

navButtons.forEach(button => {
    if (button.id !== 'writeReview') {
        button.addEventListener('click', () => {
            navButtons.forEach(btn => btn.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));

            button.classList.add('active');
            const sectionId = button.getAttribute('data-section');
            const targetSection = document.getElementById(sectionId);
            if (targetSection) targetSection.classList.add('active');
        });
    }
});
