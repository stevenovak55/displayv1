/**
 * JavaScript for the Single Property Details Page.
 * v7.0.0
 * - FEAT: Added robust full-width gallery logic for mobile.
 * - REFACTOR: Implemented a highly reliable, JS-driven sticky sidebar using a placeholder method.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    const gallery = document.querySelector('.mld-gallery');

    // --- Full-width Gallery on Mobile ---
    function handleGalleryWidth() {
        const container = document.querySelector('#mld-single-property-page .mld-container');
        if (window.innerWidth <= 1140) {
            if (container) container.style.maxWidth = '100vw';
        } else {
            if (container) container.style.maxWidth = '1200px';
        }
    }

    // --- Swipeable Gallery Script ---
    if (gallery) {
        const slider = gallery.querySelector('.mld-gallery-slider');
        const slides = gallery.querySelectorAll('.mld-gallery-slide');
        const prevButton = gallery.querySelector('.mld-slider-nav.prev');
        const nextButton = gallery.querySelector('.mld-slider-nav.next');
        const thumbnails = gallery.querySelectorAll('.mld-thumb');

        if (slider && slides.length > 1) {
            let currentIndex = 0;
            let isDragging = false;
            let startPos = 0;
            let currentTranslate = 0;
            let prevTranslate = 0;
            let animationID;

            const goToSlide = (index) => {
                if (index < 0 || index >= slides.length) return;
                const slideWidth = slides[0].offsetWidth;
                slider.style.transition = 'transform 0.3s ease-out';
                currentTranslate = index * -slideWidth;
                slider.style.transform = `translateX(${currentTranslate}px)`;
                prevTranslate = currentTranslate;
                currentIndex = index;
                updateUI();
            };

            const updateUI = () => {
                thumbnails.forEach((thumb, index) => {
                    thumb.classList.toggle('active', index === currentIndex);
                    if(index === currentIndex) {
                        thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                });
                prevButton.style.display = currentIndex === 0 ? 'none' : 'flex';
                nextButton.style.display = currentIndex === slides.length - 1 ? 'none' : 'flex';
            };

            const getPositionX = (event) => event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;

            const dragStart = (event) => {
                isDragging = true;
                startPos = getPositionX(event);
                slider.style.transition = 'none';
                animationID = requestAnimationFrame(animation);
                gallery.querySelector('.mld-gallery-main-image').classList.add('is-dragging');
            };

            const drag = (event) => {
                if (isDragging) {
                    const currentPosition = getPositionX(event);
                    currentTranslate = prevTranslate + currentPosition - startPos;
                }
            };

            const dragEnd = () => {
                if (!isDragging) return;
                isDragging = false;
                cancelAnimationFrame(animationID);
                const movedBy = currentTranslate - prevTranslate;
                if (movedBy < -100 && currentIndex < slides.length - 1) currentIndex++;
                if (movedBy > 100 && currentIndex > 0) currentIndex--;
                goToSlide(currentIndex);
                gallery.querySelector('.mld-gallery-main-image').classList.remove('is-dragging');
            };

            const animation = () => {
                slider.style.transform = `translateX(${currentTranslate}px)`;
                if (isDragging) requestAnimationFrame(animation);
            };

            slider.addEventListener('mousedown', dragStart);
            slider.addEventListener('touchstart', dragStart, { passive: true });
            slider.addEventListener('mouseup', dragEnd);
            slider.addEventListener('mouseleave', dragEnd);
            slider.addEventListener('touchend', dragEnd);
            slider.addEventListener('mousemove', drag);
            slider.addEventListener('touchmove', drag, { passive: true });
            prevButton.addEventListener('click', () => goToSlide(currentIndex - 1));
            nextButton.addEventListener('click', () => goToSlide(currentIndex + 1));
            thumbnails.forEach((thumb, index) => thumb.addEventListener('click', () => goToSlide(index)));
            
            window.addEventListener('resize', () => {
                handleGalleryWidth();
                goToSlide(currentIndex);
            });

            handleGalleryWidth(); // Initial check
            updateUI();
        }
    }

    // --- Robust Sticky Sidebar Script ---
    const sidebar = document.querySelector('.mld-sidebar');
    if (sidebar && window.matchMedia('(min-width: 992px)').matches) {
        const sidebarContent = document.querySelector('.mld-sidebar-sticky-content');
        const mainContent = document.querySelector('.mld-listing-details');
        const topSpacing = 20;
        let placeholder = null;

        let topOffset = topSpacing;

        const calculatePositions = () => {
            let totalHeaderHeight = 0;
            const fixedElements = document.querySelectorAll('*');
            fixedElements.forEach(el => {
                const style = window.getComputedStyle(el);
                if ((style.position === 'fixed' || style.position === 'sticky') && el.offsetHeight > 0) {
                    const rect = el.getBoundingClientRect();
                    if (rect.top >= 0 && rect.top < 50 && !el.classList.contains('mld-sidebar')) {
                        totalHeaderHeight = Math.max(totalHeaderHeight, rect.bottom);
                    }
                }
            });
            topOffset = totalHeaderHeight + topSpacing;
        };

        const createPlaceholder = () => {
            if (!placeholder) {
                placeholder = document.createElement('div');
                placeholder.className = 'mld-sidebar-placeholder';
                sidebar.parentNode.insertBefore(placeholder, sidebar);
            }
            placeholder.style.height = sidebar.offsetHeight + 'px';
            placeholder.style.width = sidebar.offsetWidth + 'px';
        };

        const handleScroll = () => {
            if (!mainContent) return;
            const scrollY = window.scrollY;
            const parentRect = sidebar.parentElement.getBoundingClientRect();
            const mainContentBottom = mainContent.offsetTop + mainContent.offsetHeight;

            if (!sidebar.classList.contains('is-sticky') && (parentRect.top < topOffset)) {
                createPlaceholder();
                sidebar.classList.add('is-sticky');
                sidebar.style.left = `${parentRect.left}px`;
                sidebar.style.width = `${parentRect.width}px`;
                sidebar.style.top = `${topOffset}px`;
            } else if (sidebar.classList.contains('is-sticky') && (placeholder.getBoundingClientRect().top >= topOffset)) {
                sidebar.classList.remove('is-sticky');
                sidebar.style.cssText = '';
                if (placeholder) {
                    placeholder.remove();
                    placeholder = null;
                }
            }
            
            if (sidebar.classList.contains('is-sticky')) {
                 const sidebarBottom = scrollY + sidebar.offsetHeight + topOffset;
                 if (sidebarBottom > mainContentBottom) {
                     const stopPosition = mainContentBottom - scrollY - sidebar.offsetHeight;
                     sidebar.style.top = `${stopPosition}px`;
                 } else {
                     sidebar.style.top = `${topOffset}px`;
                 }
            }
        };

        setTimeout(() => {
            calculatePositions();
            window.addEventListener('scroll', handleScroll);
            window.addEventListener('resize', calculatePositions);
        }, 500);
    }
});
