/**
 * BIBLIOTECA OBSCURA - MAGICAL.JS
 * Where the impossible becomes reality ✨
 */

// ============================================
// 1. PARTICLE SYSTEM
// ============================================

class ParticleSystem {
    constructor() {
        this.container = document.getElementById('particles-container');
        this.particleCount = 0;
    }

    createParticle(x, y, color = 'rgba(194, 163, 93, 0.8)') {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        const duration = 2000 + Math.random() * 3000;
        const distance = 200 + Math.random() * 300;
        const drift = (Math.random() - 0.5) * 200;
        const angle = Math.random() * Math.PI * 2;
        
        particle.style.setProperty('--duration', duration + 'ms');
        particle.style.setProperty('--distance', `${-distance}px`);
        particle.style.setProperty('--drift', `${drift}px`);
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        particle.style.background = color;
        
        this.container.appendChild(particle);
        
        setTimeout(() => particle.remove(), duration);
    }

    burst(x, y, count = 8) {
        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                this.createParticle(x, y);
            }, i * 50);
        }
    }

    random(interval = 2000) {
        setInterval(() => {
            const x = Math.random() * window.innerWidth;
            const y = Math.random() * window.innerHeight;
            this.createParticle(x, y);
        }, interval);
    }
}

const particles = new ParticleSystem();

// Start random particle generation
particles.random(3000);

// ============================================
// 2. CURSOR EFFECTS
// ============================================

class CursorMagic {
    constructor() {
        this.glow = document.getElementById('cursor-glow');
        this.particleContainer = document.getElementById('cursor-particles');
        this.x = 0;
        this.y = 0;
        this.scrollY = 0;
        this.init();
    }

    init() {
        document.addEventListener('mousemove', (e) => {
            this.x = e.clientX;
            this.y = e.clientY;
            this.updateGlow();
            this.createTrail();
        });

        document.addEventListener('mouseenter', () => {
            this.glow.classList.add('active');
        });

        document.addEventListener('mouseleave', () => {
            this.glow.classList.remove('active');
        });

        // Track scroll for absolute positioning
        window.addEventListener('scroll', () => {
            this.scrollY = window.scrollY;
            if (this.x && this.y) {
                this.updateGlow();
            }
        });
    }

    updateGlow() {
        if (this.glow) {
            // Use absolute positioning with scroll adjustment
            this.glow.style.left = this.x + 'px';
            this.glow.style.top = (this.y + this.scrollY) + 'px';
        }
    }

    createTrail() {
        if (Math.random() > 0.7) {
            const particle = document.createElement('div');
            particle.classList.add('cursor-particle');
            
            const angle = Math.random() * Math.PI * 2;
            const distance = 20 + Math.random() * 30;
            const tx = Math.cos(angle) * distance;
            const ty = Math.sin(angle) * distance;
            
            // Add scrollY so particles follow page scroll
            particle.style.left = this.x + 'px';
            particle.style.top = (this.y + this.scrollY) + 'px';
            particle.style.setProperty('--tx', tx + 'px');
            particle.style.setProperty('--ty', ty + 'px');
            
            this.particleContainer.appendChild(particle);
            
            setTimeout(() => particle.remove(), 600);
        }
    }
}

const cursor = new CursorMagic();

// ============================================
// 3. THEME TOGGLE
// ============================================

class ThemeManager {
    constructor() {
        this.toggle = document.getElementById('theme-toggle');
        this.icon = this.toggle.querySelector('.theme-icon');
        this.currentTheme = 'dark';
        this.init();
    }

    init() {
        // Load saved theme
        const saved = localStorage.getItem('biblioteca-theme') || 'dark';
        this.setTheme(saved);
        
        this.toggle.addEventListener('click', () => {
            this.currentTheme = this.currentTheme === 'dark' ? 'day' : 'night';
            if (this.currentTheme === 'day') {
                this.currentTheme = 'dark';
            }
            this.cycleTheme();
        });
    }

    cycleTheme() {
        const themes = ['dark', 'night-mode', 'day-mode'];
        const current = themes.findIndex(t => document.body.classList.contains(t) || (t === 'dark' && !document.body.classList.contains('night-mode') && !document.body.classList.contains('day-mode')));
        const next = (current + 1) % themes.length;
        
        this.setTheme(themes[next]);
        localStorage.setItem('biblioteca-theme', themes[next]);
        
        // Create particles on theme change
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 12);
    }

    setTheme(theme) {
        document.body.classList.remove('dark', 'night-mode', 'day-mode');
        if (theme !== 'dark') {
            document.body.classList.add(theme);
        }

        const icons = {
            'dark': '☾',
            'night-mode': '◊',
            'day-mode': '◆'
        };
        
        this.icon.textContent = icons[theme] || '☾';
    }
}

const theme = new ThemeManager();

// ============================================
// 4. QUOTE ROTATION SYSTEM
// ============================================

const quotes = [
    { text: "Every book is a portal to another world.", author: "— Unknown Librarian" },
    { text: "In the silence of the library, we hear the voice of truth.", author: "— Anonymous Scholar" },
    { text: "The most beautiful thing is knowledge, the most powerful is understanding.", author: "— Forgotten Wisdom" },
    { text: "These dusty shelves hold the secrets of eternity.", author: "— Night Keeper" },
    { text: "A library is not just a building—it is a sanctuary for the soul.", author: "— The Archivist" },
    { text: "In darkness, we find the brightest wisdom.", author: "— Candlelight Sage" },
    { text: "Every spine you crack releases centuries of thought.", author: "— Page Turner" },
    { text: "The library remembers what the world forgets.", author: "— Eternal Voice" }
];

function rotateQuote() {
    const quote = quotes[Math.floor(Math.random() * quotes.length)];
    const textEl = document.getElementById('quote-text');
    const authorEl = document.getElementById('quote-author');
    
    if (textEl && authorEl) {
        textEl.style.opacity = '0';
        
        setTimeout(() => {
            textEl.textContent = quote.text;
            authorEl.textContent = quote.author;
            textEl.style.opacity = '1';
        }, 300);
    }
}

// Rotate quote on scroll to quote section
document.addEventListener('DOMContentLoaded', () => {
    rotateQuote();
    
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.target.id === 'quote-section' && entry.isIntersecting) {
                rotateQuote();
            }
        });
    }, { threshold: 0.5 });
    
    const quoteSection = document.getElementById('quote-section');
    if (quoteSection) observer.observe(quoteSection);
});

// Change quote every 8 seconds
setInterval(rotateQuote, 8000);

// ============================================
// 5. BOOK RECOMMENDATIONS
// ============================================

const bookRecommendations = [
    {
        title: "The Shadow Doctrine",
        description: "A treatise on finding truth in darkness and light through ancient texts.",
        rarity: "Rare"
    },
    {
        title: "Candlelit Chronicles",
        description: "Stories whispered in library halls, collected over centuries.",
        rarity: "Legendary"
    },
    {
        title: "The Manuscript Keeper",
        description: "A guide to the hidden knowledge guarded within these walls.",
        rarity: "Rare"
    },
    {
        title: "Letters Between Stars",
        description: "Correspondence between scholars across ages seeking eternal wisdom.",
        rarity: "Mythic"
    },
    {
        title: "The Golden Index",
        description: "A catalog of secrets, accessible only to those who truly search.",
        rarity: "Rare"
    },
    {
        title: "Whispers of Yesterday",
        description: "Echoes of knowledge from those who walked these halls before us.",
        rarity: "Uncommon"
    }
];

function generateRecommendations() {
    const container = document.getElementById('recommendations');
    if (!container) return;

    const shuffled = [...bookRecommendations].sort(() => Math.random() - 0.5);
    
    shuffled.forEach((book, index) => {
        const card = document.createElement('div');
        card.classList.add('recommendation-card', 'fade-in');
        card.style.animationDelay = (index * 0.1) + 's';
        
        card.innerHTML = `
            <div class="recommendation-title">⬥ ${book.title}</div>
            <p class="recommendation-text">${book.description}</p>
            <span class="recommendation-rarity">★ ${book.rarity}</span>
        `;
        
        card.addEventListener('click', () => {
            particles.burst(event.clientX, event.clientY, 10);
        });
        
        container.appendChild(card);
    });
}

// ============================================
// 6. VISITOR COUNTER
// ============================================

class VisitorCounter {
    constructor() {
        this.storageKey = 'biblioteca-visitors';
        this.initializeCounter();
    }

    initializeCounter() {
        let visitors = parseInt(localStorage.getItem(this.storageKey)) || 0;
        visitors++;
        localStorage.setItem(this.storageKey, visitors);
        
        const counter = document.getElementById('visitor-count');
        if (counter) {
            const number = counter.querySelector('.visitor-number');
            number.textContent = visitors;
        }

        // Update books discovered counter
        const booksDiscovered = parseInt(localStorage.getItem('books-discovered')) || 0;
        const booksCounter = document.getElementById('books-count');
        if (booksCounter) {
            booksCounter.textContent = Math.floor(booksDiscovered + Math.random() * 100 + 50);
        }
    }
}

const visitor = new VisitorCounter();

// ============================================
// 7. BOOK MODAL
// ============================================

class BookModal {
    constructor() {
        this.modal = document.getElementById('book-modal');
        this.closeBtn = this.modal ? this.modal.querySelector('.modal-close') : null;
        this.init();
    }

    init() {
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }

        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) this.close();
            });
        }

        // Add click handlers to books
        document.addEventListener('click', (e) => {
            if (e.target.closest('.book')) {
                const book = e.target.closest('.book');
                const title = book.dataset.title || 'Untitled Tome';
                this.open(title);
            }
        });
    }

    open(title) {
        if (!this.modal) return;

        this.modal.classList.remove('hidden');
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-description').textContent = 'A fascinating tome from our ancient collection...';
        document.getElementById('modal-author').textContent = '— Attributed to the Unknown';

        // Trigger particles
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 15);
    }

    close() {
        if (this.modal) {
            this.modal.classList.add('hidden');
        }
    }
}

const modal = new BookModal();

// ============================================
// 8. EASTER EGGS
// ============================================

class EasterEggs {
    constructor() {
        this.secretCode = [];
        this.targetCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'KeyB', 'KeyA'];
        this.init();
    }

    init() {
        document.addEventListener('keydown', (e) => {
            this.secretCode.push(e.code);
            this.secretCode = this.secretCode.slice(-this.targetCode.length);

            if (this.secretCode.join(',') === this.targetCode.join(',')) {
                this.triggerSecretMode();
            }

            // Ctrl+Alt+L for Library Mode
            if (e.ctrlKey && e.altKey && e.code === 'KeyL') {
                this.triggerLibraryMode();
            }
        });

        // Click pattern
        let clickCount = 0;
        document.addEventListener('click', (e) => {
            if (e.target.closest('.glow-text')) {
                clickCount++;
                if (clickCount === 3) {
                    this.triggerGoldenAge();
                    clickCount = 0;
                }
            }
        });
    }

    triggerSecretMode() {
        console.log('◊ SEKRIT MODE ACTIVATED');
        document.body.style.setProperty('--antique-gold', '#00FF00');
        particles.burst(window.innerWidth / 2, window.innerHeight / 2, 30);
        
        setTimeout(() => {
            document.body.style.setProperty('--antique-gold', '#C2A35D');
        }, 2000);
    }

    triggerLibraryMode() {
        console.log('⬥ INFINITE LIBRARY MODE');
        location.reload();
    }

    triggerGoldenAge() {
        console.log('★ GOLDEN AGE UNLOCKED');
        let currentHue = 0;
        const interval = setInterval(() => {
            currentHue = (currentHue + 5) % 360;
            document.body.style.filter = `hue-rotate(${currentHue}deg)`;
        }, 30);

        setTimeout(() => {
            clearInterval(interval);
            document.body.style.filter = 'hue-rotate(0deg)';
        }, 3000);
    }
}

const eggs = new EasterEggs();

// ============================================
// 9. PAGE FADE IN
// ============================================

window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.8s ease-out';
        document.body.style.opacity = '1';
    }, 100);
});

// ============================================
// 10. SCROLL-BASED EFFECTS
// ============================================

let scrollDepth = 0;
window.addEventListener('scroll', () => {
    scrollDepth = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
    
    // Create occasional particles on scroll
    if (Math.random() > 0.95) {
        particles.createParticle(
            Math.random() * window.innerWidth,
            window.scrollY + Math.random() * window.innerHeight
        );
    }
});

// ============================================
// 11. KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', (e) => {
    // Ctrl+/ for help
    if (e.ctrlKey && e.code === 'Slash') {
        console.log(`
✦ BIBLIOTECA OBSCURA - HIDDEN COMMANDS
═══════════════════════════════════════
↑↑↓↓←→←→B+A - Secret Mode
Ctrl+Alt+L - Reload Library
Ctrl+/ - This help
Click title 3x - Golden Age
        `);
    }
});

// ============================================
// 12. LOAD ALL MAGICAL COMPONENTS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('✦ Biblioteca Obscura awakening...');
    
    // Generate recommendations
    generateRecommendations();
    
    // Initialize all main.js functionality
    if (typeof buildBookShelves === 'function') {
        buildBookShelves();
    }
    
    console.log('★ The library is alive.');
});

// ============================================
// 13. AMBIENT EFFECTS
// ============================================

// Random ambient glow pulses
setInterval(() => {
    if (Math.random() > 0.7) {
        const x = Math.random() * window.innerWidth;
        const y = Math.random() * window.innerHeight;
        particles.createParticle(x, y, 'rgba(194, 163, 93, 0.4)');
    }
}, 1000);

console.log('☾ Biblioteca Obscura v2.0 - MAGICAL EDITION LOADED');
