<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');
$type = trim($_GET['type'] ?? 'all');

$allowedTypes = ['all', 'guides', 'tools', 'categories'];

if (!in_array($type, $allowedTypes, true)) {
    $type = 'all';
}

$guideResults = [];
$toolResults = [];
$categoryResults = [];
$dbError = false;
$searched = $query !== '';

if ($searched) {
    try {
        if ($type === 'all' || $type === 'guides') {
            $guideResults = search_guides($query, 30);
        }

        if ($type === 'all' || $type === 'tools') {
            $toolResults = search_tools($query, 30);
        }

        if ($type === 'all' || $type === 'categories') {
            $categoryResults = search_categories($query, 20);
        }
    } catch (Throwable $e) {
        $dbError = true;
        error_log('Computer Therapy search error: ' . $e->getMessage());
    }
}

$totalResults = count($guideResults) + count($toolResults) + count($categoryResults);

$pageTitle = $searched
    ? 'Search results for "' . $query . '" | Computer Therapy'
    : 'Search | Computer Therapy';

$pageDescription = 'Search Computer Therapy guides, tools, categories, and troubleshooting resources.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>" />

    <link rel="stylesheet" href="/assets/css/home.css" />
    <link rel="icon" href="/favicon.ico" sizes="any">
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            <a href="/" class="brand" aria-label="Computer Therapy home">
                <img src="/assets/img/computertherapy.png" alt="Computer Therapy logo" class="brand-logo" />
                <div class="brand-text">
                    <strong>Computer Therapy</strong>
                    <span>Knowledge hub for practical troubleshooting and trusted utilities</span>
                </div>
            </a>

            <nav class="nav-links" aria-label="Primary navigation">
                <a href="/guides.php">Guides</a>
                <a href="/tools.php">Tools</a>
                <a href="/#categories">Topics</a>
                <a href="/#discussions">Discussions</a>
                <a href="/#about">About</a>
            </nav>

            <div class="nav-actions">
                <a class="btn btn-primary" href="/request-help.php">Ask a Question</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero compact-hero">
            <div class="container">
                <div class="eyebrow">Search Computer Therapy</div>

                <h1>Search the knowledge hub.</h1>

                <p>
                    Find guides, tools, categories, and practical troubleshooting resources.
                </p>
            </div>
        </section>

        <section class="search-band">
            <div class="container">
                <form class="search-card" action="/search.php" method="get">
                    <div class="search-row">
                        <input
                            type="search"
                            name="q"
                            value="<?= e($query) ?>"
                            placeholder="Search guides, tools, troubleshooting topics..."
                            aria-label="Search"
                        />

                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>

                    <div class="quick-tags">
                        <a class="tag <?= $type === 'all' ? 'active-tag' : '' ?>" href="/search.php?q=<?= urlencode($query) ?>&type=all">All</a>
                        <a class="tag <?= $type === 'guides' ? 'active-tag' : '' ?>" href="/search.php?q=<?= urlencode($query) ?>&type=guides">Guides</a>
                        <a class="tag <?= $type === 'tools' ? 'active-tag' : '' ?>" href="/search.php?q=<?= urlencode($query) ?>&type=tools">Tools</a>
                        <a class="tag <?= $type === 'categories' ? 'active-tag' : '' ?>" href="/search.php?q=<?= urlencode($query) ?>&type=categories">Categories</a>
                    </div>
                </form>
            </div>
        </section>

        <section>
            <div class="container">
                <div class="section-head">
                    <div>
                        <?php if ($searched): ?>
                            <h2>Results for “<?= e($query) ?>”</h2>
                            <p>
                                <?= $totalResults ?>
                                <?= $totalResults === 1 ? 'result' : 'results' ?>
                                found.
                            </p>
                        <?php else: ?>
                            <h2>Start a search</h2>
                            <p>Enter a topic, tool name, error message, or troubleshooting keyword.</p>
                        <?php endif; ?>
                    </div>

                    <a href="/" class="section-link">Back home</a>
                </div>

                <?php if ($dbError): ?>

                    <article class="card">
                        <h3>Search temporarily unavailable</h3>
                        <p>
                            The search database could not be loaded. Please check the server logs
                            and database connection.
                        </p>
                    </article>

                <?php elseif (!$searched): ?>

                    <div class="grid-3">
                        <article class="card category-card">
                            <small>Example</small>
                            <h3>Windows slow</h3>
                            <p>Search for startup apps, disk space, updates, and malware checks.</p>
                        </article>

                        <article class="card category-card">
                            <small>Example</small>
                            <h3>DNS</h3>
                            <p>Search network troubleshooting, name resolution, and router issues.</p>
                        </article>

                        <article class="card category-card">
                            <small>Example</small>
                            <h3>Backup</h3>
                            <p>Search recovery planning, drive health, and restore testing.</p>
                        </article>
                    </div>

                <?php elseif ($totalResults === 0): ?>

                    <article class="card">
                        <h3>No results found</h3>
                        <p>
                            No matches were found for “<?= e($query) ?>”.
                            Try a broader term like Windows, network, backup, security, Linux, or remote support.
                        </p>
                    </article>

                <?php else: ?>

                    <?php if (!empty($guideResults)): ?>
                        <div class="section-head search-group-head">
                            <div>
                                <h2>Guides</h2>
                                <p><?= count($guideResults) ?> guide <?= count($guideResults) === 1 ? 'match' : 'matches' ?>.</p>
                            </div>
                        </div>

                        <div class="grid-3 search-results-grid">
                            <?php foreach ($guideResults as $guide): ?>
                                <article class="card kb">
                                    <span class="badge"><?= e($guide['badge'] ?? 'Guide') ?></span>

                                    <h3>
                                        <a href="/guide.php?slug=<?= e($guide['slug'] ?? '') ?>">
                                            <?= e($guide['title'] ?? 'Untitled guide') ?>
                                        </a>
                                    </h3>

                                    <p><?= e($guide['summary'] ?? '') ?></p>

                                    <div class="meta">
                                        <?php if (!empty($guide['category_name'])): ?>
                                            <span><?= e($guide['category_name']) ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($guide['difficulty'])): ?>
                                            <span><?= e($guide['difficulty']) ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($guide['estimated_minutes'])): ?>
                                            <span><?= (int)$guide['estimated_minutes'] ?> min read</span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($toolResults)): ?>
                        <div class="section-head search-group-head">
                            <div>
                                <h2>Tools</h2>
                                <p><?= count($toolResults) ?> tool <?= count($toolResults) === 1 ? 'match' : 'matches' ?>.</p>
                            </div>
                        </div>

                        <div class="grid-4 search-results-grid">
                            <?php foreach ($toolResults as $tool): ?>
                                <?php
                                    $toolName = $tool['name'] ?? 'Unnamed tool';
                                    $toolUrl = $tool['website_url'] ?? '';
                                    $toolCategory = $tool['category_name'] ?? 'Tool';
                                    $platform = $tool['platform'] ?? '';
                                    $licenseType = $tool['license_type'] ?? '';
                                ?>

                                <article class="card tool-card">
                                    <div class="tool-title">
                                        <h3>
                                            <?php if (!empty($toolUrl)): ?>
                                                <a href="<?= e($toolUrl) ?>" target="_blank" rel="noopener noreferrer">
                                                    <?= e($toolName) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= e($toolName) ?>
                                            <?php endif; ?>
                                        </h3>

                                        <span><?= e($toolCategory) ?></span>
                                    </div>

                                    <p><?= e($tool['description'] ?? '') ?></p>

                                    <div class="meta">
                                        <?php if (!empty($platform)): ?>
                                            <span><?= e($platform) ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($licenseType)): ?>
                                            <span><?= e($licenseType) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($toolUrl)): ?>
                                        <p class="tool-link">
                                            <a href="<?= e($toolUrl) ?>" target="_blank" rel="noopener noreferrer">
                                                Visit website →
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($categoryResults)): ?>
                        <div class="section-head search-group-head">
                            <div>
                                <h2>Categories</h2>
                                <p><?= count($categoryResults) ?> category <?= count($categoryResults) === 1 ? 'match' : 'matches' ?>.</p>
                            </div>
                        </div>

                        <div class="grid-3 search-results-grid">
                            <?php foreach ($categoryResults as $category): ?>
                                <article class="card category-card">
                                    <small><?= e($category['label'] ?? 'Topic') ?></small>

                                    <h3>
                                        <a href="/guides.php?category=<?= e($category['slug'] ?? '') ?>">
                                            <?= e($category['name'] ?? 'Untitled category') ?>
                                        </a>
                                    </h3>

                                    <p><?= e($category['description'] ?? '') ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </section>

        <section id="cta">
            <div class="container">
                <div class="cta-panel">
                    <h2>Could not find what you needed?</h2>
                    <p>
                        Request help with a specific issue or suggest a topic for a future Computer Therapy guide.
                    </p>

                    <div class="cta-actions">
                        <a class="btn btn-secondary" href="/request-help.php">Request Help</a>
                        <a class="btn btn-secondary" href="/suggest-guide.php">Suggest a Guide</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-grid">
            <div>
                <h4>Computer Therapy</h4>
                <p>
                    A practical knowledge hub for troubleshooting, utilities, and useful computer support resources.
                </p>
            </div>

            <div>
                <h4>Explore</h4>
                <a href="/">Home</a>
                <a href="/guides.php">Guides</a>
                <a href="/tools.php">Tools</a>
                <a href="/request-help.php">Request Help</a>
            </div>

            <div>
                <h4>Next additions</h4>
                <a href="/search.php">Search</a>
                <a href="/privacy.php">Privacy</a>
                <a href="/admin/">Admin</a>
            </div>
        </div>
    </footer>

    <script src="/assets/js/home.js"></script>
</body>
</html>
