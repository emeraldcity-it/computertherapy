<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categorySlug = trim($_GET['category'] ?? '');
$guides = [];
$categories = [];
$currentCategory = null;
$dbError = false;

try {
    $categories = get_active_categories();

    if ($categorySlug !== '') {
        $currentCategory = get_category_by_slug($categorySlug);

        if ($currentCategory) {
            $guides = get_guides_by_category_slug($categorySlug);
        } else {
            http_response_code(404);
        }
    } else {
        $guides = get_all_guides();
    }
} catch (Throwable $e) {
    $dbError = true;
    error_log('Computer Therapy guides page DB error: ' . $e->getMessage());
}

$pageTitle = $currentCategory
    ? $currentCategory['name'] . ' Guides | Computer Therapy'
    : 'Guides | Computer Therapy';

$pageDescription = $currentCategory
    ? ($currentCategory['description'] ?? 'Browse Computer Therapy guides by topic.')
    : 'Browse practical Computer Therapy troubleshooting guides for Windows, Linux, networking, security, backups, and utilities.';
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
                <a href="/#guides">Guides</a>
                <a href="/#tools">Tools</a>
                <a href="/#categories">Topics</a>
                <a href="/#discussions">Discussions</a>
                <a href="/#about">About</a>
            </nav>

            <div class="nav-actions">
                <a class="btn btn-secondary" href="/search.php">Search</a>
                <a class="btn btn-primary" href="/request-help.php">Ask a Question</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero compact-hero">
            <div class="container">
                <div class="eyebrow">Computer Therapy Guides</div>

                <?php if ($currentCategory): ?>
                    <h1><?= e($currentCategory['name']) ?></h1>
                    <p><?= e($currentCategory['description'] ?? 'Browse guides in this category.') ?></p>
                <?php elseif ($categorySlug !== '' && !$currentCategory): ?>
                    <h1>Category not found</h1>
                    <p>The requested guide category does not exist or is not active.</p>
                <?php else: ?>
                    <h1>Browse practical troubleshooting guides.</h1>
                    <p>
                        Find step-by-step guides for common computer, network, security, backup,
                        and remote-support issues.
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <section class="search-band">
            <div class="container">
                <form class="search-card" action="/search.php" method="get">
                    <div class="search-row">
                        <input
                            type="search"
                            name="q"
                            placeholder="Search guides..."
                            aria-label="Search guides"
                        />

                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>

                    <?php if (!empty($categories)): ?>
                        <div class="quick-tags">
                            <a class="tag" href="/guides.php">All guides</a>

                            <?php foreach ($categories as $category): ?>
                                <a class="tag" href="/guides.php?category=<?= e($category['slug']) ?>">
                                    <?= e($category['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <section>
            <div class="container">
                <div class="section-head">
                    <div>
                        <?php if ($currentCategory): ?>
                            <h2><?= e($currentCategory['name']) ?> guides</h2>
                        <?php else: ?>
                            <h2>All guides</h2>
                        <?php endif; ?>

                        <p>
                            <?= count($guides) ?>
                            <?= count($guides) === 1 ? 'published guide' : 'published guides' ?>
                            <?= $currentCategory ? 'in this category.' : 'available.' ?>
                        </p>
                    </div>

                    <a href="/" class="section-link">Back home</a>
                </div>

                <?php if ($dbError): ?>
                    <article class="card">
                        <h3>Guides temporarily unavailable</h3>
                        <p>
                            The guides database could not be loaded. Please check the server logs
                            and database connection.
                        </p>
                    </article>
                <?php elseif (empty($guides)): ?>
                    <article class="card">
                        <h3>No guides found</h3>
                        <p>
                            No published guides are available here yet. Add guides to the database
                            or try another category.
                        </p>
                    </article>
                <?php else: ?>
                    <div class="grid-3">
                        <?php foreach ($guides as $guide): ?>
                            <article class="card kb">
                                <span class="badge">
                                    <?= e($guide['badge'] ?? 'Guide') ?>
                                </span>

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
            </div>
        </section>

        <section id="cta">
            <div class="container">
                <div class="cta-panel">
                    <h2>Need a guide that is not here yet?</h2>
                    <p>
                        Suggest a topic or request help with a specific computer, network,
                        backup, or security problem.
                    </p>

                    <div class="cta-actions">
                        <a class="btn btn-secondary" href="/suggest-guide.php">Suggest a Guide</a>
                        <a class="btn btn-secondary" href="/request-help.php">Request Help</a>
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
