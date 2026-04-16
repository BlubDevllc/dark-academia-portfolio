/**
 * BIBLIOTECA OBSCURA
 * Main JavaScript - Book Loading & Shelf Management
 */

let sampleBooks = [];

// ============================================
// Load books from database API
// ============================================
async function loadBooksFromDatabase() {
    try {
        console.log('🔍 Fetching books from API...');
        const pathname = window.location.pathname;
        const baseDir = pathname.substring(0, pathname.lastIndexOf('/'));
        const apiUrl = baseDir + '/api.php?action=get_books';
        console.log('📍 API URL:', apiUrl);
        
        const response = await fetch(apiUrl);
        const result = await response.json();
        console.log('📚 API Response:', result);
        
        if (result.success && result.data && result.data.length > 0) {
            console.log(`✅ Found ${result.data.length} books in database`);
            
            // Dark academia color palette for books
            const colors = ['#4A3F3F', '#5A4A4A', '#6A5555', '#7A6666', '#6D6A4A', '#5B4A5F', '#7A5A6A', '#6A5A5A', '#5A5A4A', '#8B5F4D'];
            
            sampleBooks = result.data.map((book, idx) => ({
                id: book.id,
                title: book.title || 'Unknown Title',
                author: book.author || 'Anonymous',
                description: book.description || 'No description available',
                cover_color: colors[idx % colors.length],
                image_path: book.image_path || null,
                rating: Math.floor(Math.random() * 3) + 3,
                pages: book.pages || 0,
                published_year: book.published_year || 0
            }));
        } else {
            console.warn('⚠️ No books from API, using fallback');
            sampleBooks = [
                { 
                    id: 1,
                    title: "The Midnight Codex", 
                    author: "Unknown", 
                    description: "A mysterious tome of midnight secrets",
                    cover_color: "#4A3F3F", 
                    image_path: null,
                    rating: 5,
                    pages: 234,
                    published_year: 1823
                }
            ];
        }
    } catch (error) {
        console.error('❌ Error loading books:', error);
        sampleBooks = [
            { 
                id: 1,
                title: "The Midnight Codex", 
                author: "Unknown", 
                description: "A mysterious tome",
                cover_color: "#4A3F3F", 
                image_path: null,
                rating: 5,
                pages: 234,
                published_year: 1823
            }
        ];
    }
    
    console.log('📚 Building shelves with', sampleBooks.length, 'books');
    buildBookShelves();
}

// ============================================
// Build book shelves with proper styling
// ============================================
function buildBookShelves() {
    const bookshelf = document.getElementById('bookshelf-1');
    if (!bookshelf) {
        console.error('❌ bookshelf-1 element not found!');
        return;
    }

    console.log('🏗️ Building shelf with', sampleBooks.length, 'books');
    bookshelf.innerHTML = '';
    
    if (!sampleBooks || sampleBooks.length === 0) {
        console.warn('⚠️ No books to display');
        bookshelf.innerHTML = '<p style="color: #C2A35D; padding: 20px; text-align: center; grid-column: 1/-1;">The shelves await their volumes...</p>';
        return;
    }

    sampleBooks.forEach((bookData, index) => {
        const book = document.createElement('div');
        book.className = 'book';
        
        // Store book data in attributes for modal
        book.dataset.id = bookData.id;
        book.dataset.title = bookData.title;
        book.dataset.author = bookData.author;
        book.dataset.description = bookData.description;
        book.dataset.rating = bookData.rating;
        book.dataset.pages = bookData.pages;
        book.dataset.year = bookData.published_year;
        
        // Random rotation for organic feel (between -3 and 3 degrees)
        const rotation = (Math.random() * 6) - 3;
        book.style.setProperty('--rotation', rotation);
        
        // Set book color
        book.style.setProperty('--book-color', bookData.cover_color);
        
        // If book has image, use it as background
        if (bookData.image_path) {
            const baseDir = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const imagePath = baseDir + '/' + bookData.image_path;
            book.style.backgroundImage = `url('${imagePath}')`;
            book.dataset.hasImage = 'true';
        } else {
            book.dataset.hasImage = 'false';
        }
        
        // Add title attribute for spine text
        book.setAttribute('data-title', bookData.title);
        
        bookshelf.appendChild(book);
    });
    
    console.log('✅ Shelf built with', sampleBooks.length, 'books');
}

// ============================================
// Initialize on page load
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('📖 DOM loaded, initializing...');
    loadBooksFromDatabase();
    
    // Initialize modal system
    BookModal.init();
});
        book.style.minHeight = '180px';
        book.style.zIndex = '1';
        
        // Random rotation between -3 and 3 degrees
        const rotation = (Math.random() - 0.5) * 6;
        book.style.setProperty('--rotation', rotation);
        book.dataset.title = bookData.title;
        book.dataset.author = bookData.author;
        book.dataset.index = index;
        
        // Stagger animation
        book.style.animationDelay = (index * 0.05) + 's';

        const cover = document.createElement('div');
        cover.className = 'book-cover';
        
        // FORCE cover visibility
        cover.style.display = 'block';
        cover.style.width = '100%';
        cover.style.height = '100%';
        cover.style.minHeight = '180px';
        cover.style.minWidth = '120px';
        cover.style.visibility = 'visible';
        cover.style.opacity = '1';
        cover.style.aspectRatio = '3 / 4';
        cover.style.borderRadius = '3px';
        cover.style.border = '2px solid #8B7D75';
        cover.style.boxShadow = '0 4px 8px rgba(0,0,0,0.7)';
        cover.style.overflow = 'hidden';
        cover.style.position = 'relative';
        
        // If book has image, use it; otherwise use color
        if (bookData.image_path) {
            cover.style.backgroundImage = `url('${bookData.image_path}')`;
            cover.style.backgroundSize = 'cover';
            cover.style.backgroundPosition = 'center';
        } else {
            cover.style.backgroundColor = bookData.cover;
            cover.style.backgroundImage = `linear-gradient(135deg, ${bookData.cover}, ${bookData.cover}dd)`;
        }

        const title = document.createElement('div');
        title.className = 'book-title';
        title.textContent = bookData.title;
        title.style.position = 'absolute';
        title.style.bottom = '10px';
        title.style.left = '5px';
        title.style.right = '5px';
        title.style.color = '#E2D3B7';
        title.style.fontSize = '0.8rem';
        title.style.fontWeight = 'bold';
        title.style.textAlign = 'center';
        title.style.visibility = 'visible';
        title.style.zIndex = '2';

        const spine = document.createElement('div');
        spine.className = 'book-spine';
        spine.textContent = bookData.title.substring(0, 3).toUpperCase();
        spine.style.position = 'absolute';
        spine.style.top = '50%';
        spine.style.left = '50%';
        spine.style.transform = 'translate(-50%, -50%)';
        spine.style.fontSize = '0.7rem';
        spine.style.color = 'rgba(194, 163, 93, 0.3)';
        spine.style.fontWeight = '700';
        spine.style.opacity = '0';

        cover.appendChild(spine);
        cover.appendChild(title);
        book.appendChild(cover);
        bookshelf.appendChild(book);
        
        console.log(`📖 Added book: ${bookData.title}`);
    });

    console.log('✅ Shelf complete with', sampleBooks.length, 'books');
    animateBookFloat();
}

// Updated book spine styling
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    .book-spine {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.7rem;
        color: rgba(194, 163, 93, 0.3);
        font-weight: 700;
        letter-spacing: 0.05em;
        opacity: 0;
        transition: opacity 0.3s ease;
        writing-mode: vertical-rl;
        text-orientation: mixed;
    }
    
    .book:hover .book-spine {
        opacity: 1;
    }
`;
document.head.appendChild(styleSheet);

// ============================================
// 3. BOOK FLOAT ANIMATION (ENHANCED)
// ============================================
function animateBookFloat() {
    document.querySelectorAll('.book').forEach((book, index) => {
        const randomDelay = Math.random() * 2000;
        const randomDuration = 3000 + Math.random() * 3000;
        const randomDistance = 15 + Math.random() * 20;
        const randomSway = 5 + Math.random() * 15;

        // Complex animation with multiple transforms
        const keyframes = [
            { transform: `translateY(0px) rotate(${(Math.random()-0.5)*2}deg)`, offset: 0 },
            { transform: `translateY(-${randomDistance/2}px) rotate(${(Math.random()-0.5)*3}deg)`, offset: 0.25 },
            { transform: `translateY(${randomDistance}px) rotate(${(Math.random()-0.5)*2}deg)`, offset: 0.5 },
            { transform: `translateY(-${randomDistance/3}px) rotate(${(Math.random()-0.5)*3}deg)`, offset: 0.75 },
            { transform: `translateY(0px) rotate(${(Math.random()-0.5)*2}deg)`, offset: 1 }
        ];

        book.animate(keyframes, {
            duration: randomDuration,
            delay: randomDelay,
            iterations: Infinity,
            easing: 'ease-in-out'
        });
    });
}

// ============================================
// 4. SCROLL REVEAL SYSTEM (ENHANCED)
// ============================================
function setupScrollReveal() {
    const fadeElements = document.querySelectorAll('.fade-in, .scale-in, .slide-in-left, .slide-in-right');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add small delay for staggered effect
                const delay = entry.target.dataset.revealDelay || 0;
                setTimeout(() => {
                    entry.target.classList.add('visible');
                    
                    // Trigger particles on reveal
                    if (typeof particles !== 'undefined' && Math.random() > 0.8) {
                        particles.burst(
                            window.innerWidth * 0.5,
                            entry.target.getBoundingClientRect().top + window.scrollY,
                            3
                        );
                    }
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    fadeElements.forEach((el, index) => {
        el.dataset.revealDelay = index * 30;
        observer.observe(el);
    });
}

// ============================================
// 5. MOUSE LIGHT EFFECT (ENHANCED)
// ============================================
function setupMouseLightEffect() {
    const root = document.documentElement;
    
    document.addEventListener('mousemove', e => {
        root.style.setProperty('--x', e.clientX + 'px');
        root.style.setProperty('--y', e.clientY + 'px');
    });
}

// ============================================
// 6. SEARCH FUNCTIONALITY (ENHANCED)
// ============================================
function setupSearch() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const books = document.querySelectorAll('.book');

    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const value = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        books.forEach(book => {
            const title = book.dataset.title.toLowerCase();
            const author = (book.dataset.author || '').toLowerCase();
            const isMatch = title.includes(value) || author.includes(value);

            if (isMatch || value === '') {
                book.style.display = 'block';
                book.style.opacity = isMatch ? '1' : '0.3';
                if (isMatch && value !== '') visibleCount++;
            } else {
                book.style.display = 'none';
            }
        });

        // Show results
        if (value !== '') {
            let message = `Found ${visibleCount} book(s)`;
            if (visibleCount === 0) {
                message = `No books found matching "${value}".<br><em>Perhaps it's hidden in a secret chamber...</em>`;
            }
            searchResults.innerHTML = `<p class="ink-text">${message}</p>`;
        } else {
            searchResults.innerHTML = '';
            books.forEach(b => b.style.opacity = '1');
        }
    });
}

// ============================================
// 7. SMOOTH SCROLL NAVIGATION (ENHANCED)
// ============================================
function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            e.preventDefault();
            const target = document.querySelector(href);
            if (!target) return;

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Trigger particles
            if (typeof particles !== 'undefined') {
                particles.burst(window.innerWidth / 2, window.innerHeight / 2, 8);
            }
        });
    });

    const exploreBtn = document.getElementById('explore-btn');
    const searchBtn = document.getElementById('search-btn');

    if (exploreBtn) {
        exploreBtn.addEventListener('click', () => {
            document.getElementById('shelves')?.scrollIntoView({ behavior: 'smooth' });
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('search-input')?.focus();
        });
    }
}

// ============================================
// 8. ANIMATED RATING STARS
// ============================================
function setupRatingStars() {
    const stars = document.querySelectorAll('.star');

    stars.forEach((star, index, allStars) => {
        star.addEventListener('mouseenter', () => {
            allStars.forEach((s, i) => {
                if (i <= index) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });

        star.addEventListener('click', () => {
            allStars.forEach((s, i) => {
                if (i <= index) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
            
            if (typeof particles !== 'undefined') {
                particles.burst(event.clientX, event.clientY, 10);
            }
        });
    });

    const ratingContainer = document.querySelector('.rating-container');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', () => {
            stars.forEach(s => s.classList.remove('active'));
        });
    }
}

// ============================================
// ============================================
// 9. TRACK BOOKS DISCOVERED
// ============================================
function trackBooksDiscovered() {
    document.querySelectorAll('.book').forEach(book => {
        book.addEventListener('click', () => {
            let discovered = parseInt(localStorage.getItem('books-discovered')) || 0;
            discovered++;
            localStorage.setItem('books-discovered', discovered);
        });
    });
}

// ============================================
// INITIALIZE ALL ON DOM READY
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('✦ Biblioteca Obscura awakening...');

    loadBooksFromDatabase();  // Load from database instead of hardcoded data
    setupScrollReveal();
    setupMouseLightEffect();
    setupSearch();
    setupSmoothScroll();
    setupRatingStars();
    trackBooksDiscovered();

    console.log('★ The library is alive and breathing.');
});

// ============================================
// STAGGER FADE-IN
// ============================================
function addStaggerDelay() {
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach((el, index) => {
        el.style.transitionDelay = `${index * 0.08}s`;
    });
}

setTimeout(addStaggerDelay, 100);
