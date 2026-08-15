document.addEventListener('DOMContentLoaded', () => {
    const eventBlocks = document.querySelectorAll('.event-block');

    eventBlocks.forEach(block => {
        // Make each block clickable
        block.style.cursor = 'pointer';
        block.addEventListener('click', () => {
            const eventId = block.dataset.id;
            window.location.href = `volunteer_details.php?id=${eventId}`;
        });

        // Hover effect
        block.addEventListener('mouseenter', () => {
            block.style.transform = 'scale(1.05)';
            block.style.boxShadow = '0 8px 16px rgba(0,0,0,0.3)';
        });

        block.addEventListener('mouseleave', () => {
            block.style.transform = 'scale(1)';
            block.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        });
    });
});