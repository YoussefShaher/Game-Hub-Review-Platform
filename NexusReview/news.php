<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/db.php';

// ── FETCH ARTICLES ─────────────────────────────────────────────────────────────
try {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT id, title, slug, category, image, bg_class, emoji,
               excerpt, body, author_name, read_time, created_at
        FROM news
        WHERE published = 1
        ORDER BY created_at DESC
    ");
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $articles = [];
}

// Split articles into layout zones
$hero = $articles[0] ?? null;
$side = array_slice($articles, 1, 2);
$grid = array_slice($articles, 3);

// Build JS-safe map for the article overlay (slug → data)
$jsArticles = [];
foreach ($articles as $a) {
    $jsArticles[$a['slug']] = [
        'title'  => $a['title'],
        'cat'    => $a['category'],
        'author' => $a['author_name'],
        'date'   => date('F j Y', strtotime($a['created_at'])),
        'bg'     => $a['bg_class'],
        'emoji'  => $a['emoji'],
        'image'  => $a['image'] ?? '',
        'body'   => $a['body'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NexusReview — News</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/news.css">
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     NAVIGATION
════════════════════════════════════════════════════════════ -->
<nav>
  <div class="nav-inner">
    <a href="index.php" class="logo">NEXUS<span>REVIEW</span></a>

    <div class="nav-links">
      <a href="index.php#games">Games</a>
      <a href="index.php#trending">Trending</a>
      <a href="news.php" class="active">News</a>
      <a href="index.php#reviews">Reviews</a>
      <a href="index.php#charts">Top Charts</a>
      <a href="index.php#consoles">Consoles</a>
    </div>

    <div class="nav-right">
      <button class="btn btn-outline">Log In</button>
      <button class="btn btn-accent">Sign Up</button>
      <button
        class="hamburger"
        onclick="document.getElementById('mob-menu').classList.toggle('open')"
        aria-label="Menu"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </div>
</nav>

<!-- ── MOBILE MENU ──────────────────────────────────────── -->
<div class="mobile-menu" id="mob-menu">
  <a href="index.php#games">Games</a>
  <a href="index.php#trending">Trending</a>
  <a href="news.php">News</a>
  <a href="index.php#reviews">Reviews</a>
  <a href="index.php#charts">Top Charts</a>
  <a href="index.php#consoles">Consoles</a>
</div>

<!-- ══════════════════════════════════════════════════════════
     FULL ARTICLE OVERLAY
════════════════════════════════════════════════════════════ -->
<div class="article-overlay" id="article-overlay">
  <div class="article-full" id="article-full-content"></div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════ -->
<div class="page-header">
  <div class="page-header-inner">
    <h1>GAMING <span>NEWS</span></h1>
    <p>
      The latest from the gaming world — hardware announcements,
      game updates, esports, and in-depth reviews.
    </p>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     CATEGORY FILTER BAR
════════════════════════════════════════════════════════════ -->
<div class="cat-bar">
  <div class="cat-chips">
    <div class="cat-chip active"  onclick="filterCat('All',      this)">All</div>
    <div class="cat-chip"         onclick="filterCat('Industry',  this)">Industry</div>
    <div class="cat-chip"         onclick="filterCat('Update',    this)">Updates</div>
    <div class="cat-chip"         onclick="filterCat('Review',    this)">Reviews</div>
    <div class="cat-chip"         onclick="filterCat('Esports',   this)">Esports</div>
    <div class="cat-chip"         onclick="filterCat('Hardware',  this)">Hardware</div>
    <div class="cat-chip"         onclick="filterCat('DLC',       this)">DLC &amp; Expansions</div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     ARTICLES
════════════════════════════════════════════════════════════ -->
<div class="news-page">

<?php if (empty($articles)): ?>

  <div class="empty-state">
    <h2>NO ARTICLES</h2>
    <p>Run <code>setup.php</code> to seed the database, then refresh this page.</p>
  </div>

<?php else: ?>

  <?php
  $featuredCats = array_filter(array_merge(
      $hero ? [$hero['category']] : [],
      array_column($side, 'category')
  ));
  ?>

  <!-- Featured Zone (hero + side cards) -->
  <div
    class="featured-news"
    id="featured-zone"
    data-cats="<?= htmlspecialchars(implode(',', $featuredCats)) ?>"
  >

    <?php if ($hero): ?>
    <div class="article-hero" onclick="openArticle('<?= htmlspecialchars($hero['slug']) ?>')">
      <div class="article-hero-img <?= htmlspecialchars($hero['bg_class']) ?>">
        <?php if (!empty($hero['image'])): ?>
          <img
            src="<?= htmlspecialchars($hero['image']) ?>"
            alt="<?= $hero['emoji'] ?> <?= htmlspecialchars($hero['title']) ?>"
            onerror="this.outerHTML='<div class=&quot;img-emoji&quot;><?= $hero['emoji'] ?></div>'"
          >
        <?php else: ?>
          <div class="img-emoji"><?= $hero['emoji'] ?></div>
        <?php endif; ?>
      </div>
      <div class="article-body">
        <div class="article-cat"><?= htmlspecialchars($hero['category']) ?></div>
        <h2><?= htmlspecialchars($hero['title']) ?></h2>
        <p><?= htmlspecialchars($hero['excerpt']) ?></p>
        <div class="article-meta">
          <span class="author"><?= htmlspecialchars($hero['author_name']) ?></span>
          <span><?= date('F j Y', strtotime($hero['created_at'])) ?></span>
          <span class="article-tag"><?= htmlspecialchars($hero['category']) ?></span>
          <span><?= htmlspecialchars($hero['read_time']) ?> read</span>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($side)): ?>
    <div class="side-articles">
      <?php foreach ($side as $s): ?>
      <div class="side-card" onclick="openArticle('<?= htmlspecialchars($s['slug']) ?>')">
        <div class="side-card-img <?= htmlspecialchars($s['bg_class']) ?>">
          <?php if (!empty($s['image'])): ?>
            <img
              src="<?= htmlspecialchars($s['image']) ?>"
              alt="<?= $s['emoji'] ?> <?= htmlspecialchars($s['title']) ?>"
              onerror="this.outerHTML='<div class=&quot;img-emoji&quot;><?= $s['emoji'] ?></div>'"
            >
          <?php else: ?>
            <div class="img-emoji"><?= $s['emoji'] ?></div>
          <?php endif; ?>
        </div>
        <div class="side-card-body">
          <div class="article-cat"><?= htmlspecialchars($s['category']) ?></div>
          <h3><?= htmlspecialchars($s['title']) ?></h3>
          <div class="article-meta">
            <span class="author"><?= htmlspecialchars($s['author_name']) ?></span>
            <span><?= date('F j', strtotime($s['created_at'])) ?></span>
            <span class="article-tag"><?= htmlspecialchars($s['category']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div><!-- /featured-news -->

  <!-- All Articles Grid -->
  <div class="section-divider">
    <h2>ALL <span>ARTICLES</span></h2>
    <hr>
  </div>

  <div class="articles-grid" id="articles-grid">
    <?php foreach ($grid as $a): ?>
    <div
      class="article-card"
      data-cat="<?= htmlspecialchars($a['category']) ?>"
      onclick="openArticle('<?= htmlspecialchars($a['slug']) ?>')"
    >
      <div class="article-card-img <?= htmlspecialchars($a['bg_class']) ?>">
        <?php if (!empty($a['image'])): ?>
          <img
            src="<?= htmlspecialchars($a['image']) ?>"
            alt="<?= $a['emoji'] ?> <?= htmlspecialchars($a['title']) ?>"
            onerror="this.outerHTML='<div class=&quot;img-emoji&quot;><?= $a['emoji'] ?></div>'"
          >
        <?php else: ?>
          <div class="img-emoji"><?= $a['emoji'] ?></div>
        <?php endif; ?>
      </div>
      <div class="article-card-body">
        <div class="article-cat"><?= htmlspecialchars($a['category']) ?></div>
        <h3><?= htmlspecialchars($a['title']) ?></h3>
        <p><?= htmlspecialchars($a['excerpt']) ?></p>
        <div class="article-card-footer">
          <span class="read-time">
            <?= date('F j', strtotime($a['created_at'])) ?> · <?= htmlspecialchars($a['read_time']) ?>
          </span>
          <span class="read-more">Read →</span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

</div><!-- /news-page -->

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer>
  <a href="index.php" class="logo" style="display:inline-block;margin-bottom:8px;">
    NEXUS<span style="color:var(--accent2)">REVIEW</span>
  </a>
  <p>&copy; 2026 NexusReview · Built for gamers by gamers</p>
</footer>

<!-- ── INLINE: article data map for JS ──────────────────── -->
<script>
  const ARTICLES = <?= json_encode($jsArticles, JSON_HEX_TAG) ?>;
</script>
<script src="js/news.js"></script>

</body>
</html>