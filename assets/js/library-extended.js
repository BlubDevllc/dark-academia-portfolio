/**
 * BIBLIOTECA OBSCURA - LIBRARY EXTENDED FEATURES
 * All the magical interactions and advanced behaviors
 */

// ============================================
// ENHANCED BOOK GENERATION
// ============================================

function generateEnhancedBooks() {
    const sampleBooks = [
        { title: "The Codex of Shadows", author: "Unknown Keeper", cover: "#7A6666", desc: "Ancient tome filled with forgotten secrets" },
        { title: "Echoes of Forever", author: "The Wanderer", cover: "#5C4D4D", desc: "A book that exists in all times simultaneously" },
        { title: "The Infinite Library", author: "The Architect", cover: "#6D6A4A", desc: "Instructions for building impossible spaces" },
        { title: "Whispers in the Dark", author: "The Listener", cover: "#3B4D3B", desc: "Conversations from the void between worlds" },
        { title: "Golden Secrets", author: "The Seeker", cover: "#8B7D4D", desc: "Knowledge worth more than gold itself" },
        { title: "The Last Manuscript", author: "The Final Scribe", cover: "#4D5C5C", desc: "One book to rule them all" },
        { title: "Twilight Studies", author: "The Scholar", cover: "#6B4D4D", desc: "Research into the spaces between day and night" },
        { title: "Sacred Geometries", author: "The Mathematician", cover: "#5C6B4D", desc: "Patterns that explain everything" },
        { title: "The Memory Palace", author: "The Rememberer", cover: "#7A6B5C", desc: "A building constructed entirely of memories" },
        { title: "Forbidden Knowledge", author: "The Collector", cover: "#3B3B5C", desc: "What we don't yet know we need to know" }
    ];

    return sampleBooks;
}

// ============================================
// ENHANCED SEARCH WITH ADVANCED FILTERING
// ============================================

class AdvancedSearch {
    constructor() {
        this.books = generateEnhancedBooks();
        this.input = document.getElementById('search-input');
        this.results = document.getElementById('search-results');
        this.init();
    }

    init() {
        this.input.addEventListener('input', (e) => this.search(e.target.value));
        this.input.addEventListener('focus', () => this.results.style.display = 'block');
    }

    search(query) {
        this.results.innerHTML = '';
        
        if (!query.trim()) {
            this.results.style.display = 'none';
            return;
        }

        const filtered = this.books.filter(book => 
            book.title.toLowerCase().includes(query.toLowerCase()) ||
            book.author.toLowerCase().includes(query.toLowerCase()) ||
            book.desc.toLowerCase().includes(query.toLowerCase())
        );

        if (filtered.length === 0) {
            this.results.innerHTML = '<p class="search-result-item">No tomes found in this search...</p>';
            return;
        }

        filtered.forEach((book, index) => {
            const item = document.createElement('div');
            item.classList.add('search-result-item');
            item.innerHTML = `
                <div class="result-title">${book.title}</div>
                <div class="result-author">by ${book.author}</div>
                <div class="result-desc">${book.desc}</div>
            `;
            item.style.animationDelay = `${index * 0.1}s`;
            item.addEventListener('click', () => this.openBook(book));
            this.results.appendChild(item);
        });

        this.results.style.display = 'block';
        
        // Burst particles on new results
        particles.burst(this.input.getBoundingClientRect().x + 300, 
                       this.input.getBoundingClientRect().y, 15);
    }

    openBook(book) {
        openBookModal(book);
        this.results.style.display = 'none';
    }
}

// ============================================
// ENHANCED BOOK MODAL SYSTEM
// ============================================

const modal = {
    el: document.getElementById('book-modal'),
    open: function(bookData) {
        document.getElementById('modal-title').textContent = bookData.title;
        document.getElementById('modal-description').textContent = bookData.desc || 'A mysterious tome from the eternal collection.';
        document.getElementById('modal-author').textContent = `— ${bookData.author}`;
        document.getElementById('modal-cover').textContent = '⬥';
        
        // Generate random stars
        const stars = Math.floor(Math.random() * 2) + 4;
        document.getElementById('modal-stars').textContent = '★'.repeat(stars);
        
        this.el.classList.remove('hidden');
        this.el.classList.add('visible');
        document.body.style.overflow = 'hidden';
        
        // Burst particles on modal open
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 30);
    },
    close: function() {
        this.el.classList.add('hidden');
        this.el.classList.remove('visible');
        document.body.style.overflow = 'auto';
    }
};

document.getElementById('book-modal').addEventListener('click', (e) => {
    if (e.target.id === 'book-modal' || e.target.classList.contains('modal-close')) {
        modal.close();
    }
});

// ============================================
// TIMELINE INTERACTION SYSTEM
// ============================================

class TimelineAnimations {
    constructor() {
        this.items = document.querySelectorAll('.timeline-item');
        this.init();
    }

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInScale 0.8s ease-out forwards';
                    particles.burst(
                        entry.target.getBoundingClientRect().x + 50,
                        entry.target.getBoundingClientRect().y,
                        10
                    );
                }
            });
        }, { threshold: 0.5 });

        this.items.forEach(item => observer.observe(item));
    }
}

// ============================================
// THOUGHT CARDS INTERACTION
// ============================================

class ThoughtCardSystem {
    constructor() {
        this.cards = document.querySelectorAll('.thought-card');
        this.init();
    }

    init() {
        this.cards.forEach((card, index) => {
            card.addEventListener('mouseenter', () => {
                // Glow effect
                card.style.filter = 'drop-shadow(0 0 30px rgba(194,163,93,0.6))';
                
                // Particle burst at card position
                const rect = card.getBoundingClientRect();
                particles.burst(rect.x + rect.width / 2, rect.y + rect.height / 2, 8);
            });

            card.addEventListener('mouseleave', () => {
                card.style.filter = 'drop-shadow(0 0 10px rgba(0,0,0,0.3))';
            });
        });
    }
}

// ============================================
// BOOK RECOMMENDATIONS (RESTRICTED SECTION)
// ============================================

function generateRecommendations() {
    const recommendations = [
        { title: "The Codex of Shadows", rarity: "Legendary", symbol: "✦", color: "#FFD700" },
        { title: "Echoes of Forever", rarity: "Mythic", symbol: "❖", color: "#C0C0C0" },
        { title: "The Infinite Manuscript", rarity: "Transcendent", symbol: "⬥", color: "#CD7F32" },
    ];

    const container = document.getElementById('recommendations');
    if (!container) return;

    container.innerHTML = '';
    
    recommendations.forEach((rec, index) => {
        const div = document.createElement('div');
        div.classList.add('rare-book');
        div.innerHTML = `
            <div class="rare-cover">${rec.symbol}</div>
            <p class="rare-title">${rec.title}</p>
            <p class="rare-rarity" style="color: ${rec.color};">${rec.rarity}</p>
        `;
        div.style.animationDelay = `${index * 0.2}s`;
        
        div.addEventListener('click', () => {
            openBookModal({ title: rec.title, author: "Unknown", desc: `A ${rec.rarity.toLowerCase()} tome.` });
            particles.burst(
                div.getBoundingClientRect().x + 125,
                div.getBoundingClientRect().y + 125,
                20
            );
        });
        
        div.addEventListener('mouseenter', () => {
            particles.burst(
                div.getBoundingClientRect().x + 125,
                div.getBoundingClientRect().y + 125,
                5
            );
        });

        container.appendChild(div);
    });
}

// ============================================
// ENHANCED QUOTE ROTATION
// ============================================

class QuoteSystem {
    constructor() {
        this.quotes = [
            { text: "Every book is a portal to infinite worlds.", author: "Unknown Librarian" },
            { text: "The library breathes with the wisdom of ages.", author: "The Keeper" },
            { text: "In darkness, we find clarity.", author: "The Seeker" },
            { text: "Knowledge is the most precious treasure.", author: "The Collector" },
            { text: "A single page can change everything.", author: "The Reader" },
            { text: "The library never closes for those who truly seek.", author: "Ancient Proverb" },
            { text: "Words are portals through which souls converse.", author: "The Philosopher" },
            { text: "The best libraries are built in the heart.", author: "The Wanderer" }
        ];

        this.currentIndex = 0;
        this.textEl = document.getElementById('quote-text');
        this.authorEl = document.getElementById('quote-author');
        this.init();
    }

    init() {
        setInterval(() => this.rotate(), 8000);
        
        // Also rotate on scroll to quote section
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.rotate();
                }
            });
        }, { threshold: 0.5 });

        const section = document.getElementById('quote-section');
        if (section) observer.observe(section);
    }

    rotate() {
        this.currentIndex = (this.currentIndex + 1) % this.quotes.length;
        const quote = this.quotes[this.currentIndex];

        this.textEl.style.animation = 'none';
        setTimeout(() => {
            this.textEl.style.animation = 'fadeInScale 0.8s ease-out forwards';
        }, 10);

        this.textEl.textContent = `"${quote.text}"`;
        this.authorEl.textContent = `— ${quote.author}`;

        // Particle burst on quote change
        particles.burst(window.innerWidth / 2, 300, 12);
    }
}

// ============================================
// SCROLL REVEAL SYSTEM
// ============================================

class ScrollRevealSystem {
    constructor() {
        this.elements = document.querySelectorAll('.fade-in, .scale-in');
        this.init();
    }

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) scale(1)';
                    
                    // Particle burst
                    const rect = entry.target.getBoundingClientRect();
                    if (Math.random() > 0.7) {
                        particles.burst(rect.x + rect.width / 2, rect.y + rect.height / 2, 5);
                    }
                }
            });
        }, { threshold: 0.1 });

        this.elements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px) scale(0.95)';
            el.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
            observer.observe(el);
        });
    }
}

// ============================================
// VISITOR TRACKING & ANALYTICS
// ============================================

class VisitorAnalytics {
    constructor() {
        this.init();
    }

    init() {
        // Get visitor count
        let visitors = parseInt(localStorage.getItem('biblioteca_visitors')) || 0;
        visitors++;
        localStorage.setItem('biblioteca_visitors', visitors);
        document.getElementById('visitor-count').textContent = visitors;

        // Get books discovered
        let discovered = parseInt(localStorage.getItem('biblioteca_books_discovered')) || Math.floor(Math.random() * 50);
        localStorage.setItem('biblioteca_books_discovered', discovered);
        document.getElementById('books-discovered').textContent = discovered;

        // Log to console
        console.log(`%c ✦ Biblioteca Obscura v2.0 - MAGICAL EDITION LOADED`, 
            'font-size: 16px; color: #C2A35D; font-weight: bold;');
        console.log(`%c ◇ Visitor: ${visitors} | ⬥ Discovered: ${discovered}`,
            'font-size: 12px; color: #8B7D75;');
    }
}

// ============================================
// EASTER EGGS & HIDDEN COMMANDS
// ============================================

class EasterEggs {
    constructor() {
        this.konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        this.konamiIndex = 0;
        this.clickCount = 0;
        this.init();
    }

    init() {
        // Konami code
        document.addEventListener('keydown', (e) => {
            if (e.key === this.konamiCode[this.konamiIndex]) {
                this.konamiIndex++;
                if (this.konamiIndex === this.konamiCode.length) {
                    this.activateGreenMode();
                    this.konamiIndex = 0;
                }
            } else {
                this.konamiIndex = 0;
            }

            // Ctrl+Alt+L - Reload library
            if (e.ctrlKey && e.altKey && e.key === 'l') {
                console.log("%c ◊ Reloading Biblioteca Obscura...", 'color: #C2A35D; font-size: 14px;');
                location.reload();
            }

            // Ctrl+/ - Help
            if (e.ctrlKey && e.key === '/') {
                console.clear();
                console.log('%c ✨ BIBLIOTECA OBSCURA - SECRET COMMANDS ✨', 'font-size: 18px; color: #C2A35D; font-weight: bold;');
                console.log('%c Konami Code: ⬆️⬆️⬇️⬇️⬅️➡️⬅️➡️BA', 'font-size: 12px; color: #E2D3B7;');
                console.log('%c Ctrl+Alt+L: Reload library', 'font-size: 12px; color: #E2D3B7;');
                console.log('%c Click title 3x: Golden Age', 'font-size: 12px; color: #E2D3B7;');
            }
        });

        // Triple-click title for Golden Age
        const title = document.querySelector('.hero-title');
        if (title) {
            title.addEventListener('click', () => {
                this.clickCount++;
                if (this.clickCount === 3) {
                    this.activateGoldenAge();
                    this.clickCount = 0;
                }
            });

            setTimeout(() => {
                this.clickCount = 0;
            }, 500);
        }
    }

    activateGreenMode() {
        console.log("%c ⚫ GREEN MODE ACTIVATED! ⚫", 'color: lime; font-size: 18px; font-weight: bold;');
        document.body.style.filter = 'hue-rotate(115deg) saturate(2)';
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 50);
        
        setTimeout(() => {
            document.body.style.filter = 'none';
        }, 2000);
    }

    activateGoldenAge() {
        console.log("%c ✩ GOLDEN AGE ACTIVATED! ✩", 'color: gold; font-size: 18px; font-weight: bold;');
        document.body.style.filter = 'sepia(100%) brightness(1.2)';
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 50);
        
        setTimeout(() => {
            document.body.style.filter = 'none';
        }, 3000);
    }
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all systems
    new AdvancedSearch();
    new TimelineAnimations();
    new ThoughtCardSystem();
    new QuoteSystem();
    new ScrollRevealSystem();
    new VisitorAnalytics();
    new EasterEggs();
    
    // Generate recommendations
    generateRecommendations();

    // Setup bookshelf
    const bookshelf = document.getElementById('bookshelf-1');
    if (bookshelf) {
        generateEnhancedBooks().forEach(book => {
            const cover = document.createElement('div');
            cover.classList.add('book-cover');
            cover.innerHTML = `
                <div class="book-spine-text">${book.title}</div>
                <div class="book-title">${book.title}</div>
                <span style="font-size: 2rem;">⬥</span>
            `;
            cover.style.background = `linear-gradient(135deg, ${book.cover} 0%, ${book.cover}dd 100%)`;
            
            cover.addEventListener('click', () => {
                openBookModal(book);
                particles.burst(
                    cover.getBoundingClientRect().x + 75,
                    cover.getBoundingClientRect().y + 150,
                    15
                );
            });

            cover.addEventListener('mouseenter', () => {
                particles.burst(
                    cover.getBoundingClientRect().x + 75,
                    cover.getBoundingClientRect().y + 150,
                    3
                );
            });

            bookshelf.appendChild(cover);
        });
    }

    console.log("%c ◆ All magical systems initialized!", 'color: #C2A35D; font-size: 12px;');
});

// Helper function to open book modal
function openBookModal(book) {
    modal.open(book);
}

// Keyboard escape to close modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        modal.close();
    }
});
