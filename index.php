<?php
// Biblioteca Obscura - Entry Point
// Use query param to track if coming from loader
$fromLoader = isset($_GET['from_loader']);

// First visit - show loader
if (!$fromLoader && !isset($_COOKIE['biblioteca_entered'])) {
    setcookie('biblioteca_entered', '1', time() + (86400 * 30), '/');
    header('Location: loader.html?redirect_to=index.php%3Ffrom_loader%3D1');
    exit();
}

// Prevent aggressive caching to ensure latest CSS/JS
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Either from loader or cookie is set - load main page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Obscura - The Grand Library</title>
    <link rel="stylesheet" href="assets/css/main.css?v=4">
    <link rel="stylesheet" href="assets/css/animations.css?v=4">
    <link rel="stylesheet" href="assets/css/magical.css?v=4">
    <link rel="stylesheet" href="assets/css/library.css?v=4">
</head>
<body>

<!-- PAGE ENTRANCE OVERLAY -->
<div class="page-entrance"></div>

<!-- PARTICLE SYSTEMS -->
<div id="particles-container"></div>
<div id="cursor-particles"></div>

<!-- THEME TOGGLE -->
<button id="theme-toggle" class="theme-toggle" title="Toggle Night/Day Mode">
    <span class="theme-icon">☾</span>
</button>

<!-- CURSOR GLOW -->
<div id="cursor-glow"></div>

<!-- MYSTICAL BACKDROP -->
<div class="mystical-backdrop"></div>

<!-- ===== HERO SECTION: GRAND HALL ===== -->
<section class="hero" id="hero">
    <div class="hero-inner">
        <!-- Atmospheric backdrop -->
        <div class="hero-backdrop">
            <div class="backdrop-line backdrop-line-1"></div>
            <div class="backdrop-line backdrop-line-2"></div>
            <div class="backdrop-light"></div>
        </div>

        <div class="hero-content fade-in">
            <div class="hero-ornament-top">✦ ✦ ✦</div>
            
            <h1 class="hero-title glow-text">Biblioteca Obscura</h1>
            
            <p class="hero-subtitle ink-text enchanted-text">
                "In the shadows of forgotten knowledge, we discover eternal truths"
            </p>
            
            <div class="hero-divider"></div>
            
            <div class="hero-actions">
                <button class="btn-primary btn-glow" id="explore-btn">★ Explore the Shelves</button>
                <button class="btn-secondary btn-glow" id="search-btn">◊ Search Archive</button>
            </div>

            <div class="hero-ornament-bottom">✦ ✦ ✦</div>
        </div>

        <!-- Floating books in hero -->
        <div class="floating-books-hero">
            <div class="floating-book" style="animation-delay: 0s">⬚</div>
            <div class="floating-book" style="animation-delay: 1s">⬚</div>
            <div class="floating-book" style="animation-delay: 2s">◆</div>
            <div class="floating-book" style="animation-delay: 1.5s">⬚</div>
        </div>
    </div>
</section>

<!-- MYSTICAL TRANSITION -->
<div class="mystical-divider-section">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== QUOTE ROTATOR - DAILY WISDOM ===== -->
<section class="quote-section" id="quote-section">
    <div class="quote-inner">
        <div class="quote-ornament">❖</div>
        <div class="quote-container fade-in scale-in">
            <div class="quote-symbol-open">"</div>
            <p class="quote-text" id="quote-text">Every book is a portal to infinite worlds.</p>
            <div class="quote-symbol-close">"</div>
            <p class="quote-author" id="quote-author">— Unknown Librarian</p>
        </div>
        <div class="quote-ornament">❖</div>
    </div>
</section>

<!-- MYSTICAL TRANSITION -->
<div class="mystical-divider-section mystic-mid">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow glow-mid"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== PRIMARY BOOKSHELF ===== -->
<section class="library-section" id="main-shelf">
    <div class="section-header fade-in">
        <div class="section-ornament-left">✦</div>
        <h2 class="section-title glow-text">The Main Shelf</h2>
        <div class="section-ornament-right">✦</div>
    </div>
    
    <p class="section-description fade-in">
        Curated masterpieces from the eternal collection
    </p>
    
    <div class="bookshelves-container">
        <div class="bookshelf" id="bookshelf-1"></div>
    </div>

    <!-- Book detail modal (hidden) -->
    <div id="book-modal" class="book-modal hidden">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <div class="modal-body">
                <div class="modal-cover" id="modal-cover"></div>
                <div class="modal-info">
                    <h2 id="modal-title"></h2>
                    <p class="modal-description" id="modal-description"></p>
                    <p class="modal-author" id="modal-author"></p>
                    <div class="modal-rating">
                        <span class="stars" id="modal-stars"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MYSTICAL DIVIDER -->
<div class="mystical-divider-section">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== SEARCH CATALOG ===== -->
<section class="catalog-section" id="catalog">
    <div class="section-header fade-in">
        <div class="section-ornament-left">⬚</div>
        <h2 class="section-title glow-text">The Catalog</h2>
        <div class="section-ornament-right">⬚</div>
    </div>

    <div class="catalog-drawer fade-in">
        <input 
            type="text" 
            id="search-input" 
            class="search-bar" 
            placeholder="Search the archives..."
        >
        <div class="search-results" id="search-results"></div>
    </div>
</section>

<!-- MYSTICAL DIVIDER -->
<div class="mystical-divider-section">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== MARGINALIA: PERSONAL NOTES ===== -->
<section class="thoughts-section" id="thoughts">
    <div class="section-header fade-in">
        <div class="section-ornament-left">✎</div>
        <h2 class="section-title glow-text">Marginalia</h2>
        <div class="section-ornament-right">✎</div>
    </div>

    <p class="section-description fade-in">
        Annotations in the margin of existence...
    </p>

    <div class="thoughts-grid">
        <article class="thought-card fade-in scale-in">
            <div class="thought-ornament">◆</div>
            <p class="thought-text">
                "A library is not just a collection of books. It is a sanctuary where the forgotten voices of the past speak eternal truths."
            </p>
            <p class="thought-author">— The Keeper</p>
        </article>

        <article class="thought-card fade-in scale-in">
            <div class="thought-ornament">◆</div>
            <p class="thought-text">
                "Every page turned is a step deeper into mystery. Every word read is a connection to infinite possibilities."
            </p>
            <p class="thought-author">— The Wanderer</p>
        </article>

        <article class="thought-card fade-in scale-in">
            <div class="thought-ornament">◆</div>
            <p class="thought-text">
                "In darkness, we find clarity. In silence, we hear the loudest truths. This is where knowledge breathes."
            </p>
            <p class="thought-author">— The Seeker</p>
        </article>
    </div>
</section>

<!-- MYSTICAL DIVIDER -->
<div class="mystical-divider-section">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== JOURNEY THROUGH TIME ===== -->
<section class="timeline-section" id="timeline">
    <div class="section-header fade-in">
        <div class="section-ornament-left">⌛</div>
        <h2 class="section-title glow-text">The Keeper's Journey</h2>
        <div class="section-ornament-right">⌛</div>
    </div>

    <p class="section-description fade-in">
        A chronicle of transformation across ages...
    </p>

    <div class="timeline">
        <div class="timeline-item fade-in">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h3 class="timeline-year">Year of Discovery</h3>
                <p class="timeline-description">Found the first ancient tome in the tower's depths. Its pages whispered of forgotten worlds.</p>
            </div>
        </div>

        <div class="timeline-item fade-in">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h3 class="timeline-year">Year of Awakening</h3>
                <p class="timeline-description">The library began to breathe. Golden light danced across shelves. Magic stirred in the dust.</p>
            </div>
        </div>

        <div class="timeline-item fade-in">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h3 class="timeline-year">Year of Illumination</h3>
                <p class="timeline-description">All books glowed with ethereal light. The boundary between worlds grew thin. Visitors began to arrive.</p>
            </div>
        </div>

        <div class="timeline-item fade-in">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h3 class="timeline-year">Year of Mysteries</h3>
                <p class="timeline-description">What lay ahead remained unknown. The library evolved with each visitor's touch. Destiny unfolded page by page.</p>
            </div>
        </div>

        <div class="timeline-item fade-in">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h3 class="timeline-year">Year of Eternity</h3>
                <p class="timeline-description">The library transcended time. Every moment existed simultaneously. Past, present, and future merged into infinite now.</p>
            </div>
        </div>
    </div>
</section>

<!-- MYSTICAL DIVIDER -->
<div class="mystical-divider-section">
    <div class="divider-ornament">✦</div>
    <div class="divider-glow"></div>
    <div class="divider-ornament">✦</div>
</div>

<!-- ===== RESTRICTED SECTION: RARE BOOKS ===== -->
<section class="restricted-section" id="restricted">
    <div class="restricted-header">
        <div class="restricted-seal">◆</div>
        <h2 class="section-title glow-text">The Restricted Vault</h2>
        <div class="restricted-seal">◆</div>
    </div>

    <p class="section-description fade-in">
        Only the rarest, most mystical tomes rest here...
    </p>

    <div class="restricted-grid fade-in" id="recommendations">
        <!-- Generated by JS -->
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer fade-in">
    <div class="footer-content">
        <p class="footer-text">
            Visited by <span class="visitor-count" id="visitor-count">?</span> seekers
        </p>
        <p class="footer-text">
            ⬚ <span id="books-discovered">0</span> books discovered
        </p>
        <p class="footer-divider">✦</p>
        <p class="footer-text">
            "May you find wisdom in these shadows"
        </p>
    </div>
</footer>

<!-- Load JavaScript -->
<script src="assets/js/main.js?v=4"></script>
<script src="assets/js/magical.js?v=4"></script>
<script src="assets/js/library-extended.js?v=4"></script>

</body>
</html>

</body>
</html>
