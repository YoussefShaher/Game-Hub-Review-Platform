switchAuthTab

const API = '/nexusreview/api';

// ── HELPERS ───────────────────────────────────────────────────────────────────

function scoresToStars(s) {
  return s >= 90 ? '★★★★★'
       : s >= 70 ? '★★★★☆'
       : s >= 50 ? '★★★☆☆'
       : s >= 30 ? '★★☆☆☆'
       :           '★☆☆☆☆';
}

const consoleBadgeClass = {
  PS5:    'ps',
  Xbox:   'xbox',
  PC:     'pc',
  Switch: 'switch',
  Mobile: 'mobile-badge',
  PS4:    'ps4'
};

// ── APP STATE ─────────────────────────────────────────────────────────────────

let GAMES              = [];   // populated from API
let activeGenre        = 'All';
let activeConsole      = 'All';
let activeSearch       = '';
let currentUser        = null;

let visibleGamesCount  = 6;
let currentFilteredList = [];
let currentSearchQuery  = '';

// ── SESSION RESTORE ───────────────────────────────────────────────────────────
// Keeps the logged-in user across page reloads via localStorage

(function restoreSession() {
  const saved = localStorage.getItem('nx_user');
  if (saved) {
    try {
      currentUser = JSON.parse(saved);
      updateNavForUser();
    } catch (e) {
      // malformed data — ignore
    }
  }
})();

function updateNavForUser() {
  if (!currentUser) return;

  const logBtn  = document.querySelector('.btn-outline');
  const signBtn = document.querySelector('.btn-accent');

  if (logBtn)  { logBtn.textContent  = currentUser.username; logBtn.onclick  = null; }
  if (signBtn) { signBtn.textContent = 'Log Out';            signBtn.onclick = logOut; }
}

// ── API CALLS ─────────────────────────────────────────────────────────────────

async function apiGet(endpoint) {
  const res = await fetch(API + endpoint);
  if (!res.ok) throw new Error('API error ' + res.status);
  return res.json();
}

async function apiPost(endpoint, body) {
  const res = await fetch(API + endpoint, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(body)
  });
  return res.json();
}

// ── LOAD GAMES ────────────────────────────────────────────────────────────────

async function loadGames() {
  showGridLoading();
  try {
    const data = await apiGet('/games.php');
    GAMES = data.games || [];
    applyFilters();
    updateDynamicSections();
  } catch (e) {
    document.getElementById('games-grid').innerHTML =
      '<div class="no-results">' +
        '<div class="no-results-icon">⚠️</div>' +
        '<p>Could not load games. Is XAMPP running?</p>' +
      '</div>';
  }
}

function showGridLoading() {
  const skeleton =
    '<div class="game-card" style="opacity:.3;pointer-events:none;">' +
      '<div class="game-thumb bg-g1" style="height:130px;"></div>' +
      '<div class="game-body">' +
        '<div style="height:14px;background:var(--border);border-radius:4px;margin-bottom:8px;"></div>' +
        '<div style="height:11px;background:var(--border);border-radius:4px;width:60%;"></div>' +
      '</div>' +
    '</div>';

  document.getElementById('games-grid').innerHTML = Array(10).fill(skeleton).join('');
}

// ── DYNAMIC SECTIONS (Top Charts & Featured) ──────────────────────────────────

function updateDynamicSections() {
  // 1. Update Featured Game score/review count
  const featuredGame = GAMES.find(g => g.title === 'The Witcher 4');
  if (featuredGame) {
    const fScore = document.getElementById('featured-score');
    const fRevs  = document.getElementById('featured-reviews');
    if (fScore) fScore.textContent = featuredGame.avg_score || '—';
    if (fRevs)  fRevs.textContent  = `/100 · ${featuredGame.review_count || 0} reviews`;
  }

  // 2. Rebuild Top Charts
  const topGames         = [...GAMES].sort((a, b) => (b.avg_score || 0) - (a.avg_score || 0)).slice(0, 6);
  const chartsContainer  = document.getElementById('dynamic-charts');

  if (chartsContainer) {
    chartsContainer.innerHTML = topGames.map((g, index) => {
      const rank      = index + 1;
      const topClass  = rank <= 3 ? 'top3' : '';
      const gameIcon  = g.icon       || '🎮';
      const gameBg    = g.bg_class   || 'bg-g1';
      const score     = g.avg_score  || '—';
      const reviews   = g.review_count || 0;
      const thumbHtml = g.cover_image
        ? `<img src="${g.cover_image}" alt="${g.title}" onerror="this.outerHTML='<span>${gameIcon}</span>'">`
        : `<span>${gameIcon}</span>`;

      return `
        <div class="chart-item" onclick="selectGame(${g.id})">
          <div class="chart-rank ${topClass}">${rank}</div>
          <div class="chart-thumb ${gameBg}">${thumbHtml}</div>
          <div class="chart-info">
            <div class="chart-name">${g.title}</div>
            <div class="chart-detail">${g.genre} · ${reviews} reviews</div>
          </div>
          <div class="chart-score">${score}</div>
        </div>`;
    }).join('');
  }
}

// ── LOAD REVIEWS ──────────────────────────────────────────────────────────────

async function loadReviews() {
  try {
    const data       = await apiGet('/reviews.php');
    const allReviews = data.reviews || [];
    if (allReviews.length) renderReviewCards(allReviews);
  } catch (e) {
    console.error('Could not load community reviews', e);
  }
}

function renderReviewCards(reviews) {
  const grid   = document.getElementById('reviews-grid');
  const colors = [
    ['#1a0a2e', '#b59eff'],
    ['#0a1a0a', '#50c878'],
    ['#2e0a0a', '#ff9a99'],
    ['#0a1a2e', '#7ab8ff']
  ];

  grid.innerHTML = reviews.slice(0, 8).map(r => {
    const c         = colors[r.id % colors.length];
    const initials  = r.username ? r.username.slice(0, 2).toUpperCase() : 'AN';
    const stars     = scoresToStars(r.score);
    const gameTitle = GAMES.find(g => g.id === r.game_id)?.title || 'Unknown Game';
    const date      = new Date(r.created_at).toLocaleDateString('en-US', {
      month: 'short', day: 'numeric', year: 'numeric'
    });

    return `
      <div class="review-card">
        <div class="review-header">
          <div class="avatar" style="background:${c[0]};color:${c[1]};">${initials}</div>
          <div>
            <div class="review-user">${r.username}</div>
            <div class="review-date">${date}</div>
          </div>
          <div style="margin-left:auto;">
            <span class="score-pill">${r.score}</span>
          </div>
        </div>
        <div class="review-game">${gameTitle}</div>
        <div class="review-text">${r.body}</div>
        <div class="review-footer">
          <div class="stars">${stars}</div>
          <span class="helpful">👍 ${r.helpful} helpful</span>
        </div>
      </div>`;
  }).join('');
}

// ── FUZZY SEARCH ──────────────────────────────────────────────────────────────

function fuzzyScore(q, t) {
  const ql = q.toLowerCase();
  const tl = t.toLowerCase();

  if (tl.includes(ql)) return 100 + (ql.length / tl.length) * 50;

  let qi = 0, score = 0, last = -1;
  for (let i = 0; i < tl.length && qi < ql.length; i++) {
    if (tl[i] === ql[qi]) {
      score += last === i - 1 ? 10 : 3;
      last   = i;
      qi++;
    }
  }
  return qi === ql.length ? score : 0;
}

function scoreGame(q, g) {
  if (!q) return 1;
  const devName = g.developer || g.dev || '';
  return Math.max(
    fuzzyScore(q, g.title)  * 3,
    fuzzyScore(q, devName)  * 1.5,
    fuzzyScore(q, g.genre)  * 1.2,
    ...(g.consoles || []).map(c => fuzzyScore(q, c))
  );
}

function highlight(text, query) {
  if (!query) return text;

  const idx = text.toLowerCase().indexOf(query.toLowerCase());
  if (idx !== -1) {
    return (
      text.slice(0, idx) +
      '<mark>' + text.slice(idx, idx + query.length) + '</mark>' +
      text.slice(idx + query.length)
    );
  }

  let result = '';
  let qi     = 0;
  const ql   = query.toLowerCase();

  for (let i = 0; i < text.length; i++) {
    if (qi < ql.length && text[i].toLowerCase() === ql[qi]) {
      result += '<mark>' + text[i] + '</mark>';
      qi++;
    } else {
      result += text[i];
    }
  }
  return result;
}

// ── SEARCH DROPDOWN ───────────────────────────────────────────────────────────

let focusedIdx = -1;

const searchInput    = document.getElementById('search-input');
const searchDropdown = document.getElementById('search-dropdown');
const searchClearBtn = document.getElementById('search-clear');

searchInput.addEventListener('input', () => {
  activeSearch = searchInput.value.trim();
  searchClearBtn.style.display = activeSearch ? 'block' : 'none';
  focusedIdx = -1;
  renderDropdown(activeSearch);
  applyFilters();
});

searchInput.addEventListener('keydown', e => {
  const items = searchDropdown.querySelectorAll('.search-item');

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    focusedIdx = Math.min(focusedIdx + 1, items.length - 1);
    updateFocus(items);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    focusedIdx = Math.max(focusedIdx - 1, -1);
    updateFocus(items);
  } else if (e.key === 'Enter') {
    if (focusedIdx >= 0 && items[focusedIdx]) {
      items[focusedIdx].click();
    } else {
      closeDropdown();
      document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
    }
  } else if (e.key === 'Escape') {
    clearSearch();
  }
});

searchInput.addEventListener('focus', () => {
  if (activeSearch) renderDropdown(activeSearch);
});

function updateFocus(items) {
  items.forEach((el, i) => el.classList.toggle('focused', i === focusedIdx));
  if (items[focusedIdx]) items[focusedIdx].scrollIntoView({ block: 'nearest' });
}

function renderDropdown(query) {
  if (!query) { closeDropdown(); return; }

  const scored = GAMES
    .map(g => ({ g, s: scoreGame(query, g) }))
    .filter(x => x.s > 0)
    .sort((a, b) => b.s - a.s)
    .slice(0, 6);

  if (!scored.length) {
    searchDropdown.innerHTML = `<div class="search-no-results">No games found for "<strong>${query}</strong>"</div>`;
    searchDropdown.classList.add('open');
    return;
  }

  let html = '<div class="search-section-label">Games</div>';

  scored.forEach(({ g }) => {
    const gameIcon  = g.icon       || '🎮';
    const gameBg    = g.bg_class   || 'bg-g1';
    const score     = g.avg_score  || g.score || '—';
    const thumbHtml = g.cover_image
      ? `<img src="${g.cover_image}" onerror="this.outerHTML='<span>${gameIcon}</span>'">`
      : `<span>${gameIcon}</span>`;

    html += `
      <div class="search-item" onclick="selectGame(${g.id})">
        <div class="search-item-icon ${gameBg}">${thumbHtml}</div>
        <div class="search-item-info">
          <div class="search-item-title">${highlight(g.title, query)}</div>
          <div class="search-item-meta">${g.genre} · ${g.developer || g.dev} · ${(g.consoles || []).join(', ')}</div>
        </div>
        <div class="search-item-score">${score}</div>
      </div>`;
  });

  // Suggest matching genres
  const genreMatches = [...new Set(GAMES.map(g => g.genre))]
    .filter(gn => gn.toLowerCase().includes(query.toLowerCase()));

  if (genreMatches.length) {
    html += '<div class="search-section-label" style="margin-top:4px;">Genres</div>';
    genreMatches.slice(0, 3).forEach(gn => {
      const count = GAMES.filter(g => g.genre === gn).length;
      html += `
        <div class="search-item" onclick="filterByGenre('${gn}')">
          <div class="search-item-icon">🎯</div>
          <div class="search-item-info">
            <div class="search-item-title">${highlight(gn, query)}</div>
            <div class="search-item-meta">${count} games</div>
          </div>
        </div>`;
    });
  }

  html += `
    <div class="search-footer">
      <span>↑↓ navigate</span>
      <span><kbd>Enter</kbd> search · <kbd>Esc</kbd> clear</span>
    </div>`;

  searchDropdown.innerHTML = html;
  searchDropdown.classList.add('open');
}

function selectGame(id) {
  const g = GAMES.find(x => x.id === id);
  if (!g) return;

  searchInput.value = g.title;
  activeSearch      = g.title;
  closeDropdown();
  activeGenre   = 'All';
  activeConsole = 'All';

  document.querySelectorAll('#genre-filter .filter-chip')
    .forEach((c, i) => c.classList.toggle('active', i === 0));
  document.querySelectorAll('#console-filter .filter-chip')
    .forEach((c, i) => c.classList.toggle('active', i === 0));

  applyFilters();
  document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
}

function filterByGenre(genre) {
  activeGenre  = genre;
  activeSearch = '';
  searchInput.value              = '';
  searchClearBtn.style.display   = 'none';
  closeDropdown();

  document.querySelectorAll('#genre-filter .filter-chip')
    .forEach(c => c.classList.toggle('active', c.textContent === genre));

  applyFilters();
  document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
}

function clearSearch() {
  searchInput.value            = '';
  activeSearch                 = '';
  searchClearBtn.style.display = 'none';
  closeDropdown();
  applyFilters();
  searchInput.focus();
}

function closeDropdown() {
  searchDropdown.classList.remove('open');
  focusedIdx = -1;
}

document.addEventListener('click', e => {
  if (!e.target.closest('.search-wrap')) closeDropdown();
});

// ── RENDER GAMES GRID ─────────────────────────────────────────────────────────
function renderGames(list, query) {
  currentFilteredList = list;
  currentSearchQuery  = query;
  renderGamesGrid();
}

function renderGamesGrid() {
  const grid        = document.getElementById('games-grid');
  const loadMoreBtn = document.getElementById('load-more-container');

  if (!currentFilteredList.length) {
    grid.innerHTML = '<div class="no-results"><div class="no-results-icon">🔍</div><p>No games match your filters.</p></div>';
    document.getElementById('results-count').textContent = '0 games';
    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
    return;
  }

  document.getElementById('results-count').textContent =
    currentFilteredList.length + ' game' + (currentFilteredList.length !== 1 ? 's' : '');

  const visibleList = currentFilteredList.slice(0, visibleGamesCount);

  grid.innerHTML = visibleList.map(g => {
    const gameIcon  = g.icon      || '🎮';
    const gameBg    = g.bg_class  || 'bg-g1';
    const score     = g.avg_score || g.score || '—';
    const stars     = scoresToStars(parseInt(score) || 0);
    const badges    = (g.consoles || [])
      .map(c => `<span class="console-badge ${consoleBadgeClass[c] || 'ps'}">${c}</span>`)
      .join('');
    const titleHtml = currentSearchQuery ? highlight(g.title, currentSearchQuery) : g.title;
    const thumbHtml = g.cover_image
      ? `<img src="${g.cover_image}" alt="${g.title}" onerror="this.outerHTML='<div class=\\'thumb-fallback\\'>${gameIcon}</div>'">`
      : `<div class="thumb-fallback">${gameIcon}</div>`;

    return `
      <div class="game-card">
        <div class="game-thumb ${gameBg}">
          ${thumbHtml}
          <div class="game-thumb-label">${score}</div>
        </div>
        <div class="game-body">
          <div class="game-title">${titleHtml}</div>
          <div class="game-genre">${g.genre}</div>
          <div class="game-footer">
            <span class="stars">${stars}</span>
            <span class="score-pill">${score}</span>
          </div>
          <div class="console-tags">${badges}</div>
          <button class="btn-review" onclick="openReviewModal('${g.title.replace(/'/g, "\\'")}', ${g.id})">
            + Write a Review
          </button>
        </div>
      </div>`;
  }).join('');

  if (loadMoreBtn) {
    loadMoreBtn.style.display = currentFilteredList.length > visibleGamesCount ? 'block' : 'none';
  }
}

function loadMoreGames() {
  visibleGamesCount += 5;
  renderGamesGrid();
}

// ── FILTERS ───────────────────────────────────────────────────────────────────

function applyFilters() {
  const q       = activeSearch.toLowerCase();
  let filtered  = GAMES.filter(g => {
    const mG = activeGenre   === 'All' || g.genre === activeGenre;
    const mC = activeConsole === 'All' || (g.consoles || []).includes(activeConsole);
    const mS = !q || scoreGame(q, g) > 0;
    return mG && mC && mS;
  });

  if (q) filtered = filtered.sort((a, b) => scoreGame(q, b) - scoreGame(q, a));

  visibleGamesCount = 10;
  renderGames(filtered, activeSearch);
}

function setFilter(type, value, el) {
  if (type === 'genre') {
    activeGenre = value;
    document.querySelectorAll('#genre-filter .filter-chip').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
  } else {
    activeConsole = value;
    document.querySelectorAll('#console-filter .filter-chip').forEach(c => c.classList.remove('active'));
    if (el) {
      el.classList.add('active');
    } else {
      const map = {
        'All':            'All',
        'PlayStation 5':  'PS5',
        'Xbox Series X':  'Xbox',
        'PC':             'PC',
        'Nintendo Switch':'Switch',
        'PlayStation 4':  'PS4',
        'Mobile':         'Mobile'
      };
      document.querySelectorAll('#console-filter .filter-chip')
        .forEach(c => { if (map[c.textContent] === value) c.classList.add('active'); });
    }
  }
  applyFilters();
}

function jumpToFilter(con) {
  setFilter('console', con, null);
  document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
}

// ── HAMBURGER ─────────────────────────────────────────────────────────────────

function toggleMenu() {
  document.getElementById('mobile-menu').classList.toggle('open');
}

// ── AUTH ──────────────────────────────────────────────────────────────────────

function openAuthModal(tab) {
  document.getElementById('auth-modal').classList.add('open');
  switchAuthTab(tab);
}

function switchAuthTab(tab) {
  document.querySelectorAll('.modal-tab').forEach((t, i) =>
    t.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'signup'))
  );

  document.getElementById('signup-username').style.display =
    tab === 'signup' ? 'block' : 'none';

  document.getElementById('auth-btn').textContent =
    tab === 'login' ? 'LOG IN' : 'CREATE ACCOUNT';

  document.getElementById('auth-title').textContent =
    tab === 'login' ? 'WELCOME BACK' : 'JOIN THE HUB';

  document.getElementById('auth-sub').textContent =
    tab === 'login' ? 'Sign in to your account' : 'Create your free account';

  document.getElementById('auth-error').textContent = '';
}

async function handleAuth() {
  const btn      = document.getElementById('auth-btn');
  const errEl    = document.getElementById('auth-error');
  const isLogin  = btn.textContent === 'LOG IN';
  const email    = document.querySelector('#auth-modal input[type=email]').value.trim();
  const pass     = document.querySelector('#auth-modal input[type=password]').value;
  const uname    = document.getElementById('signup-username')?.querySelector('input')?.value?.trim();

  errEl.textContent = '';

  if (!email || !pass) { errEl.textContent = 'Please fill in all fields.'; return; }

  btn.textContent = '...';
  btn.disabled    = true;

  try {
    const action = isLogin ? 'login' : 'register';
    const body   = isLogin ? { email, password: pass } : { email, password: pass, username: uname };
    const res    = await apiPost(`/auth.php?action=${action}`, body);

    if (res.error) {
      errEl.textContent = res.error;
      btn.textContent   = isLogin ? 'LOG IN' : 'CREATE ACCOUNT';
      btn.disabled      = false;
      return;
    }

    currentUser = { id: res.user_id, username: res.username, role: res.role };
    localStorage.setItem('nx_user', JSON.stringify(currentUser));
    updateNavForUser();
    closeModal('auth-modal');
    showToast(`Welcome, ${currentUser.username}!`);
  } catch (e) {
    errEl.textContent = 'Server error. Check XAMPP is running.';
    btn.textContent   = isLogin ? 'LOG IN' : 'CREATE ACCOUNT';
    btn.disabled      = false;
  }
}

async function logOut() {
  await apiGet('/auth.php?action=logout').catch(() => {});
  currentUser = null;
  localStorage.removeItem('nx_user');
  location.reload();
}

// ── REVIEWS ───────────────────────────────────────────────────────────────────

let activeReviewGameId = null;

function openReviewModal(gameName, gameId) {
  if (!currentUser) {
    openAuthModal('login');
    showToast('Log in to write a review');
    return;
  }

  activeReviewGameId = gameId;
  document.getElementById('review-modal').classList.add('open');
  document.getElementById('review-game-name').textContent  = gameName || 'Select a game';
  document.getElementById('review-text').value             = '';
  document.getElementById('review-platform').value         = '';
  document.getElementById('review-score-slider').value     = 50;
  document.getElementById('review-error').textContent      = '';
  updateScoreSlider(50);
}

async function submitReview() {
  const text     = document.getElementById('review-text').value.trim();
  const score    = Math.round(document.getElementById('review-score-slider').value);
  const platform = document.getElementById('review-platform').value;
  const errEl    = document.getElementById('review-error');
  const btn      = document.getElementById('submit-review-btn');

  errEl.textContent = '';

  if (!text)        { errEl.textContent = 'Please write your review.';  return; }
  if (!currentUser) { errEl.textContent = 'You must be logged in.';     return; }

  btn.textContent = 'Saving...';
  btn.disabled    = true;

  try {
    const res = await apiPost('/reviews.php', {
      game_id: activeReviewGameId,
      user_id: currentUser.id,
      score,
      body: text,
      platform
    });

    if (res.error) {
      errEl.textContent = res.error;
      btn.textContent   = 'SUBMIT REVIEW';
      btn.disabled      = false;
      return;
    }

    // Optimistically add card to the community reviews section
    const game    = document.getElementById('review-game-name').textContent;
    const stars   = scoresToStars(score);
    const colors  = [['#1a0a2e','#b59eff'],['#0a1a0a','#50c878'],['#2e0a0a','#ff9a99'],['#0a1a2e','#7ab8ff']];
    const c       = colors[currentUser.id % colors.length];
    const initials = currentUser.username.slice(0, 2).toUpperCase();
    const card    = document.createElement('div');

    card.className = 'review-card';
    card.innerHTML = `
      <div class="review-header">
        <div class="avatar" style="background:${c[0]};color:${c[1]};">${initials}</div>
        <div>
          <div class="review-user">${currentUser.username}</div>
          <div class="review-date">Just now</div>
        </div>
        <div style="margin-left:auto;">
          <span class="score-pill">${score}</span>
        </div>
      </div>
      <div class="review-game">${game}</div>
      <div class="review-text">${text}</div>
      <div class="review-footer">
        <div class="stars">${stars}</div>
        <span class="helpful">👍 0 helpful</span>
      </div>`;

    document.getElementById('reviews-grid').prepend(card);

    btn.textContent = 'SUBMIT REVIEW';
    btn.disabled    = false;
    closeModal('review-modal');
    showToast('Review submitted!');
  } catch (e) {
    errEl.textContent = 'Server error. Check XAMPP is running.';
    btn.textContent   = 'SUBMIT REVIEW';
    btn.disabled      = false;
  }
}

function updateScoreSlider(val) {
  val = Math.round(val);
  document.getElementById('score-display').textContent = val;

  const descs = [
    '', // 0 placeholder
    ...Array(10).fill('Terrible'),
    ...Array(10).fill('Awful'),
    ...Array(10).fill('Poor'),
    ...Array(10).fill('Below Average'),
    ...Array(10).fill('Mediocre'),
    ...Array(10).fill('Average'),
    ...Array(10).fill('Good'),
    ...Array(10).fill('Great'),
    ...Array(10).fill('Excellent'),
    ...Array(10).fill('Masterpiece')
  ];

  document.getElementById('score-desc').textContent = descs[val] || '';

  const pct   = (val - 1) / 99 * 100;
  const color = val >= 80 ? '#e8ff3a'
              : val >= 60 ? '#ff9a44'
              : val >= 40 ? '#ff6b6b'
              :             '#ff3a6e';

  document.getElementById('score-display').style.color = color;
  document.getElementById('review-score-slider').style.background =
    `linear-gradient(to right, ${color} ${pct}%, var(--border) ${pct}%)`;
}

// ── MODAL HELPERS ─────────────────────────────────────────────────────────────

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

function showToast(msg) {
  const t = document.getElementById('toast');
  if (msg) t.textContent = msg;
  t.style.transform = 'translateY(0)';
  t.style.opacity   = '1';
  setTimeout(() => {
    t.style.transform = 'translateY(80px)';
    t.style.opacity   = '0';
  }, 2500);
}

// Close modals when clicking the backdrop
['auth-modal', 'review-modal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function (e) {
    if (e.target === this) closeModal(id);
  });
});

// ── BOOT ──────────────────────────────────────────────────────────────────────

updateScoreSlider(50);
loadGames().then(() => loadReviews());