<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NexusReview — Gaming Hub</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     NAVIGATION
════════════════════════════════════════════════════════════ -->
<nav>
  <div class="nav-inner">
    <div class="logo" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
      NEXUS<span>REVIEW</span>
    </div>

    <div class="nav-links">
      <a href="#trending">Trending</a>
      <a href="#games">Games</a>
      <a href="#charts">Top Charts</a>
      <a href="news.php">News</a>
      <a href="#reviews">Reviews</a>
      <a href="#consoles">Consoles</a>
    </div>

    <div class="nav-right">
      <div class="search-wrap">
        <span class="search-icon">⌕</span>
        <input
          class="search-bar"
          type="text"
          placeholder="Search games..."
          id="search-input"
          autocomplete="off"
          spellcheck="false"
        >
        <button class="search-clear" id="search-clear" onclick="clearSearch()">×</button>
        <div class="search-dropdown" id="search-dropdown"></div>
      </div>

      <button class="btn btn-outline" onclick="openAuthModal('login')">Log In</button>
      <button class="btn btn-accent" onclick="openAuthModal('signup')">Sign Up</button>

      <button class="hamburger" onclick="toggleMenu()" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </div>
</nav>

<!-- ── MOBILE MENU ──────────────────────────────────────── -->
<div class="mobile-menu" id="mobile-menu">
  <a href="#games"    onclick="toggleMenu()">Games</a>
  <a href="#trending" onclick="toggleMenu()">Trending</a>
  <a href="news.php">News</a>
  <a href="#reviews"  onclick="toggleMenu()">Reviews</a>
  <a href="#charts"   onclick="toggleMenu()">Top Charts</a>
  <a href="#consoles" onclick="toggleMenu()">Consoles</a>
  
  <div class="mobile-menu-actions">
    <button class="btn btn-outline" style="flex:1;" onclick="openAuthModal('login'); toggleMenu()">Log In</button>
    <button class="btn btn-accent"  style="flex:1;" onclick="openAuthModal('signup'); toggleMenu()">Sign Up</button>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     AUTH MODAL
════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="auth-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('auth-modal')">✕</button>
    <h2 id="auth-title">WELCOME BACK</h2>
    <p class="modal-desc" id="auth-sub">Sign in to your account</p>

    <div class="modal-tabs">
      <button class="modal-tab active" onclick="switchAuthTab('login')">Log In</button>
      <button class="modal-tab"        onclick="switchAuthTab('signup')">Sign Up</button>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" placeholder="you@example.com">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" placeholder="••••••••">
    </div>
    <div class="form-group" id="signup-username" style="display:none;">
      <label>Username</label>
      <input type="text" placeholder="GamerTag_123">
    </div>

    <p id="auth-error" style="color:var(--accent2);font-size:12px;margin-bottom:8px;min-height:16px;"></p>

    <button
      class="btn btn-accent"
      style="width:100%;padding:12px;font-size:14px;"
      id="auth-btn"
      onclick="handleAuth()"
    >LOG IN</button>

    <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:1rem;">
      Forgot password? <a href="#" style="color:var(--accent);">Reset</a>
    </p>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     REVIEW MODAL
════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="review-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('review-modal')">✕</button>
    <h2>WRITE A <span style="color:var(--accent);">REVIEW</span></h2>
    <p class="modal-desc" id="review-game-name">Select a game</p>

    <div class="form-group">
      <label>Your Score</label>
      <div class="score-slider-wrap">
        <input
          type="range"
          class="score-slider"
          id="review-score-slider"
          min="1"
          max="100"
          value="50"
          oninput="updateScoreSlider(this.value)"
        >
        <div>
          <div class="score-display" id="score-display">50</div>
          <div class="score-desc"    id="score-desc">Good</div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Your Review</label>
      <textarea
        placeholder="Share your thoughts about this game..."
        id="review-text"
      ></textarea>
    </div>

    <div class="form-group">
      <label>Platform Played On</label>
      <select id="review-platform">
        <option value="">Select platform...</option>
        <option>PlayStation 5</option>
        <option>PlayStation 4</option>
        <option>Xbox Series X</option>
        <option>PC</option>
        <option>Nintendo Switch</option>
        <option>Mobile</option>
      </select>
    </div>

    <p id="review-error" style="color:var(--accent2);font-size:12px;margin-bottom:8px;min-height:16px;"></p>

    <button
      class="btn btn-accent"
      id="submit-review-btn"
      style="width:100%;padding:12px;font-size:14px;"
      onclick="submitReview()"
    >SUBMIT REVIEW</button>
  </div>
</div>

<!-- ── TOAST ─────────────────────────────────────────────── -->
<div id="toast">✓ Review submitted!</div>

<!-- ══════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">

    <div class="hero-text">
      <div class="hero-tag">⚡ Your ultimate gaming companion</div>
      <h1 class="hero-title">GAME<br><span class="line2">REVIEWS</span></h1>
      <p class="hero-desc">
        Discover, rate, and discuss the best games across every platform.
        Community-driven scores you can trust.
      </p>
      <div class="hero-actions">
        <button
          class="btn btn-accent"
          style="padding:12px 28px;font-size:15px;"
          onclick="document.getElementById('games').scrollIntoView({ behavior: 'smooth' })"
        >Explore Games</button>
        <a href="news.php" class="btn btn-outline" style="padding:12px 28px;font-size:15px;">Latest News</a>
      </div>
      <div class="hero-stats">
        <div><div class="hero-stat-num">12K+</div><div class="hero-stat-label">Games</div></div>
        <div><div class="hero-stat-num">84K+</div><div class="hero-stat-label">Reviews</div></div>
        <div><div class="hero-stat-num">31K+</div><div class="hero-stat-label">Members</div></div>
      </div>
    </div>

    <div class="hero-featured">
      <div style="font-family:var(--font-cond);font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;">
        Featured This Week
      </div>
      <div class="featured-card">
        <div class="featured-img bg-g1">
          <img
            src="images/The Witcher 4.jpg"
            alt="The Witcher 4"
            onerror="this.outerHTML='<div class=\'img-fallback\'>🧙</div>'"
          >
          <div class="featured-badge">MUST PLAY</div>
        </div>
        <div class="featured-body">
          <h3>The Witcher 4</h3>
          <p>Action RPG · CD Projekt RED · 2025</p>
          <div style="display:flex;align-items:baseline;gap:8px;">
            <div class="score-big" id="featured-score">97</div>
            <div class="score-label" id="featured-reviews">/100 · 4,210 reviews</div>
          </div>
          <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
            <span class="console-badge ps">PS5</span>
            <span class="console-badge xbox">Xbox</span>
            <span class="console-badge pc">PC</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     TRENDING
════════════════════════════════════════════════════════════ -->
<section class="section" id="trending" style="padding-bottom:0;">
  <div class="section-header">
    <div class="section-title">🔥 <span>Trending</span> Now</div>
    <div class="section-sub">Updated hourly</div>
  </div>
  <div class="trending-row">
    <div class="trending-card">
      <div class="trend-img bg-g1">
        <img src="images/Dying Light 3.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🧟</div>'">
        <div class="trend-rank">1</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Dying Light 3</div>
        <div class="trend-change">↑ 240%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g2">
        <img src="images/Starfield 2.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🚀</div>'">
        <div class="trend-rank">2</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Starfield 2</div>
        <div class="trend-change">↑ 190%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g3">
        <img src="images/Dragon Age 4.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>⚔️</div>'">
        <div class="trend-rank">3</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Dragon Age 4</div>
        <div class="trend-change">↑ 150%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g4">
        <img src="images/Halo Infinite 2.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🔫</div>'">
        <div class="trend-rank">4</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Halo Infinite 2</div>
        <div class="trend-change down">↓ 12%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g5">
        <img src="images/Gran Turismo 9.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🏎️</div>'">
        <div class="trend-rank">5</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Gran Turismo 9</div>
        <div class="trend-change">↑ 88%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g6">
        <img src="images/The Witcher 4.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🧙</div>'">
        <div class="trend-rank">6</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Witcher 4</div>
        <div class="trend-change">↑ 320%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g1">
        <img src="images/Marvel Rivals.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🦸</div>'">
        <div class="trend-rank">7</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Marvel Rivals</div>
        <div class="trend-change">↑ 75%</div>
      </div>
    </div>
    <div class="trending-card">
      <div class="trend-img bg-g2">
        <img src="images/Subnautica 3.jpg" onerror="this.outerHTML='<div class=\'trend-emoji\'>🌊</div>'">
        <div class="trend-rank">8</div>
      </div>
      <div class="trend-body">
        <div class="trend-title">Subnautica 3</div>
        <div class="trend-change down">↓ 5%</div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     ALL GAMES
════════════════════════════════════════════════════════════ -->
<section class="section" id="games">
  <div class="section-header">
    <div class="section-title">🎮 <span>Games</span></div>
    <div class="section-sub" id="results-count"></div>
  </div>

  <div class="filter-label">Genre</div>
  <div class="filter-bar" id="genre-filter">
    <div class="filter-chip active" onclick="setFilter('genre','All',this)">All</div>
    <div class="filter-chip" onclick="setFilter('genre','Action RPG',this)">Action RPG</div>
    <div class="filter-chip" onclick="setFilter('genre','FPS',this)">FPS</div>
    <div class="filter-chip" onclick="setFilter('genre','Strategy',this)">Strategy</div>
    <div class="filter-chip" onclick="setFilter('genre','Sports',this)">Sports</div>
    <div class="filter-chip" onclick="setFilter('genre','Horror',this)">Horror</div>
    <div class="filter-chip" onclick="setFilter('genre','Adventure',this)">Adventure</div>
    <div class="filter-chip" onclick="setFilter('genre','Fighting',this)">Fighting</div>
    <div class="filter-chip" onclick="setFilter('genre','Racing',this)">Racing</div>
    <div class="filter-chip" onclick="setFilter('genre','Simulation',this)">Simulation</div>
  </div>

  <div class="filter-label">Console</div>
  <div class="filter-bar" id="console-filter" style="margin-bottom:2rem;">
    <div class="filter-chip active" onclick="setFilter('console','All',this)">All</div>
    <div class="filter-chip" onclick="setFilter('console','PS5',this)">PlayStation 5</div>
    <div class="filter-chip" onclick="setFilter('console','Xbox',this)">Xbox Series X</div>
    <div class="filter-chip" onclick="setFilter('console','PC',this)">PC</div>
    <div class="filter-chip" onclick="setFilter('console','Switch',this)">Nintendo Switch</div>
    <div class="filter-chip" onclick="setFilter('console','PS4',this)">PlayStation 4</div>
    <div class="filter-chip" onclick="setFilter('console','Mobile',this)">Mobile</div>
  </div>

  <div class="games-grid" id="games-grid"></div>

  <div id="load-more-container" style="text-align:center;margin-top:2.5rem;display:none;">
    <button
      class="btn btn-outline"
      style="padding:12px 32px;font-size:14px;"
      onclick="loadMoreGames()"
    >Load More Games ↓</button>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     TOP CHARTS + NEWS (two-column)
════════════════════════════════════════════════════════════ -->
<div style="max-width:1400px;margin:0 auto;padding:0 1.5rem 3rem;">
  <div class="two-col">

    <!-- Top Charts -->
    <div id="charts">
      <div class="section-header">
        <div class="section-title">🏆 Top <span>Charts</span></div>
      </div>
      <div class="chart-list" id="dynamic-charts"></div>
    </div>

    <!-- News Sidebar -->
    <div id="news">
      <div class="section-header">
        <div class="section-title">📰 <span>News</span></div>
        <a href="news.php" class="section-sub" style="color:var(--accent3);">All news →</a>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;">

        <a class="news-main" href="news.php#ps6">
          <div class="news-img bg-g2">
            <img src="images/ps6-news.jpg" alt="📡 PlayStation 6"
                 onerror="this.outerHTML='<div class=\'img-emoji\'>📡</div>'">
          </div>
          <div class="news-body">
            <div class="news-cat">Industry</div>
            <h3>PlayStation 6 Officially Announced</h3>
            <p>Sony confirms PS6 for late 2026 with 8K support and new DualSense Edge 2.</p>
            <div style="font-size:11px;color:var(--muted);margin-top:6px;">April 29, 2026 · 5 min read</div>
          </div>
        </a>

        <a class="news-mini" href="news.php#fortnite">
          <div class="news-mini-img bg-g1">
            <img src="images/fortnite-update.jpg" alt="🎮 Fortnite" onerror="this.outerHTML='🎮'">
          </div>
          <div>
            <div class="news-mini-cat">Update</div>
            <div class="news-mini-title">Fortnite Chapter 6 adds new weapons and biomes</div>
            <div class="news-date">April 28</div>
          </div>
        </a>

        <a class="news-mini" href="news.php#worlds">
          <div class="news-mini-img bg-g3">
            <img src="images/worlds-egypt.jpg" alt="🏆 Esports" onerror="this.outerHTML='🏆'">
          </div>
          <div>
            <div class="news-mini-cat">Esports</div>
            <div class="news-mini-title">Worlds 2026 — Egypt to host the finals</div>
            <div class="news-date">April 27</div>
          </div>
        </a>

        <a class="news-mini" href="news.php#witcher-review">
          <div class="news-mini-img bg-g6">
            <img src="images/witcher-review.jpg" alt="🔥 Review" onerror="this.outerHTML='🔥'">
          </div>
          <div>
            <div class="news-mini-cat">Review</div>
            <div class="news-mini-title">Witcher 4: CDPR's most ambitious game yet</div>
            <div class="news-date">April 26</div>
          </div>
        </a>

      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     COMMUNITY REVIEWS
════════════════════════════════════════════════════════════ -->
<section class="section" id="reviews" style="padding-top:1rem;">
  <div class="section-header">
    <div class="section-title">💬 Community <span>Reviews</span></div>
  </div>
  <div class="reviews-grid" id="reviews-grid"></div>
</section>

<!-- ══════════════════════════════════════════════════════════
     BROWSE BY CONSOLE
════════════════════════════════════════════════════════════ -->
<section class="section" id="consoles">
  <div class="section-header">
    <div class="section-title">🕹️ Browse by <span>Console</span></div>
  </div>
  <div class="consoles-grid">
    <div class="console-card ps-card"     onclick="jumpToFilter('PS5')">
      <div class="console-icon">🎮</div>
      <div class="console-name">PlayStation 5</div>
      <div class="console-count">3,420 games</div>
    </div>
    <div class="console-card xbox-card"   onclick="jumpToFilter('Xbox')">
      <div class="console-icon">🟢</div>
      <div class="console-name">Xbox Series X</div>
      <div class="console-count">2,890 games</div>
    </div>
    <div class="console-card pc-card"     onclick="jumpToFilter('PC')">
      <div class="console-icon">💻</div>
      <div class="console-name">PC</div>
      <div class="console-count">8,200 games</div>
    </div>
    <div class="console-card switch-card" onclick="jumpToFilter('Switch')">
      <div class="console-icon">🔴</div>
      <div class="console-name">Nintendo Switch</div>
      <div class="console-count">4,100 games</div>
    </div>
    <div class="console-card ps4-card"    onclick="jumpToFilter('PS4')">
      <div class="console-icon">📀</div>
      <div class="console-name">PlayStation 4</div>
      <div class="console-count">5,600 games</div>
    </div>
    <div class="console-card mobile-card" onclick="jumpToFilter('Mobile')">
      <div class="console-icon">📱</div>
      <div class="console-name">Mobile</div>
      <div class="console-count">12,000 games</div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════════ -->
<footer class="site-footer">
  <div class="footer-accent-bar"></div>
  <div class="footer-inner">

    <div class="footer-top">

      <div class="footer-brand-col">
        <div class="footer-brand-logo" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
          NEXUS<span>REVIEW</span>
        </div>
        <p class="footer-tagline">
          Your definitive source for game reviews, news, and rankings —
          written by gamers, for gamers.
        </p>
        <div class="footer-socials">
          <a class="footer-social-btn" href="#" title="Facebook">
            <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a class="footer-social-btn" href="#" title="Twitter/X">
            <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </a>
          <a class="footer-social-btn" href="#" title="Instagram">
            <svg viewBox="0 0 24 24">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" style="fill:var(--bg2)"/>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" style="stroke:var(--bg2);stroke-width:2;stroke-linecap:round"/>
            </svg>
          </a>
          <a class="footer-social-btn" href="#" title="YouTube">
            <svg viewBox="0 0 24 24">
              <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/>
              <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" style="fill:var(--bg2)"/>
            </svg>
          </a>
        </div>
      </div>

      <div class="footer-nav-col">
        <h4>Navigate</h4>
        <ul>
          <li><a href="#trending">Trending</a></li>
          <li><a href="#games">All Games</a></li>
          <li><a href="#charts">Top Charts</a></li>
          <li><a href="news.php">News</a></li>
          <li><a href="#reviews">Reviews</a></li>
          <li><a href="#consoles">Consoles</a></li>
        </ul>
      </div>

      <div class="footer-nav-col">
        <h4>Company</h4>
        <ul>
          <li><a href="javascript:void(0)">About Us</a></li>
          <li><a href="javascript:void(0)">Help Center</a></li>
          <li><a href="javascript:void(0)">Careers</a></li>
          <li><a href="javascript:void(0)">Advertise</a></li>
          <li><a href="javascript:void(0)">Press Kit</a></li>
        </ul>
      </div>

      <div class="footer-nav-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="javascript:void(0)">Privacy Policy</a></li>
          <li><a href="javascript:void(0)">Terms of Use</a></li>
          <li><a href="javascript:void(0)">Cookie Policy</a></li>
          <li><a href="javascript:void(0)">Accessibility</a></li>
          <li><a href="javascript:void(0)">Contact Us</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <p class="footer-copy">&copy; 2026 <span>NexusReview</span> — All rights reserved.</p>
      <div class="footer-brands">
        <span class="footer-brands-label">Part of</span>
        <span class="footer-brand-tag">GAMESPOT</span>
        <span class="footer-brand-tag tag-box">TV GUIDE</span>
        <span class="footer-brand-tag tag-italic">GAMEFAQS</span>
      </div>
    </div>

  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
