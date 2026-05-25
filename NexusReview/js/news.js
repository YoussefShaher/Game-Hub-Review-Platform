function openArticle(id) {
  const a = ARTICLES[id];
  if (!a) return;

  const overlay = document.getElementById('article-overlay');

  const imgHtml = a.image
    ? `<img src="${a.image}" alt="${a.emoji} ${a.title}"
           onerror="this.outerHTML='<div class=\\'img-emoji\\'>${a.emoji}</div>'">`
    : `<div class="img-emoji">${a.emoji}</div>`;

  document.getElementById('article-full-content').innerHTML = `
    <button class="article-full-close" onclick="closeArticle()">← Back to News</button>

    <div class="article-full-img ${a.bg}">
      ${imgHtml}
    </div>

    <div class="article-cat"
         style="color:var(--accent3);font-family:var(--font-cond);font-size:12px;
                font-weight:600;letter-spacing:2px;text-transform:uppercase;margin-bottom:10px;">
      ${a.cat}
    </div>

    <h1>${a.title}</h1>

    <div class="article-full-meta">
      <span style="font-weight:600;color:var(--text);">${a.author}</span>
      <span style="color:var(--muted);">${a.date}</span>
      <span class="article-tag">${a.cat}</span>
    </div>

    <div class="article-full-body">${a.body}</div>`;

  overlay.classList.add('open');
  overlay.scrollTop = 0;
}

function closeArticle() {
  document.getElementById('article-overlay').classList.remove('open');
}


function filterCat(cat, el) {
  // Update active chip
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');

  // Show/hide article cards
  document.querySelectorAll('.article-card').forEach(c => {
    c.classList.toggle('hidden', cat !== 'All' && c.dataset.cat !== cat);
  });

  // Show/hide the featured zone based on whether its categories match
  const featured = document.getElementById('featured-zone');
  if (featured) {
    const cats = (featured.dataset.cats || '').split(',');
    featured.style.display = (cat === 'All' || cats.includes(cat)) ? '' : 'none';
  }
}

window.addEventListener('load', () => {
  const hash = window.location.hash.replace('#', '');
  if (hash && ARTICLES[hash]) openArticle(hash);
});