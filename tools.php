<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categorySlug = trim($_GET['category'] ?? '');
$tools = [];
$categories = [];
$currentCategory = null;
$dbError = false;

try {
    $categories = get_active_categories();

    if ($categorySlug !== '') {
        $currentCategory = get_category_by_slug($categorySlug);

        if ($currentCategory) {
            $tools = get_tools_by_category_slug($categorySlug);
        } else {
            http_response_code(404);
        }
    } else {
        $tools = get_all_tools();
    }
} catch (Throwable $e) {
    $dbError = true;
    error_log('Computer Therapy tools page DB error: ' . $e->getMessage());
}

$pageTitle = $currentCategory
    ? $currentCategory['name'] . ' Tools | Computer Therapy'
    : 'Tools Library | Computer Therapy';

$pageDescription = $currentCategory
    ? ($currentCategory['description'] ?? 'Browse Computer Therapy tools by topic.')
    : 'Browse useful computer troubleshooting tools, utilities, diagnostics, and trusted resources.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>" />

    <link rel="stylesheet" href="/assets/css/home.css" />
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
                <a class="btn btn-secondary" href="/search.php">Search</a>
                <a class="btn btn-primary" href="/request-help.php">Ask a Question</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero compact-hero">
            <div class="container">
                <div class="eyebrow">Computer Therapy Tools Library</div>

                <?php if ($currentCategory): ?>
                    <h1><?= e($currentCategory['name']) ?> tools</h1>
                    <p><?= e($currentCategory['description'] ?? 'Browse tools in this category.') ?></p>
                <?php elseif ($categorySlug !== '' && !$currentCategory): ?>
                    <h1>Category not found</h1>
                    <p>The requested tool category does not exist or is not active.</p>
                <?php else: ?>
                    <h1>Useful tools for practical troubleshooting.</h1>
                    <p>
                        Browse trusted utilities, diagnostics, remote support tools, boot media,
                        network analyzers, and other resources worth keeping close.
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <section class="search-band">
            <div class="container">
                <form class="search-card" action="/search.php" method="get">
                    <input type="hidden" name="type" value="tools" />

                    <div class="search-row">
                        <input
                            type="search"
                            name="q"
                            placeholder="Search tools..."
                            aria-label="Search tools"
                        />

                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>

                    <?php if (!empty($categories)): ?>
                        <div class="quick-tags">
                            <a class="tag" href="/tools.php">All tools</a>

                            <?php foreach ($categories as $category): ?>
                                <a class="tag" href="/tools.php?category=<?= e($category['slug']) ?>">
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
                            <h2><?= e($currentCategory['name']) ?> tools</h2>
                        <?php else: ?>
                            <h2>All tools</h2>
                        <?php endif; ?>

                        <p>
                            <?= count($tools) ?>
                            <?= count($tools) === 1 ? 'published tool' : 'published tools' ?>
                            <?= $currentCategory ? 'in this category.' : 'available.' ?>
                        </p>
                    </div>

                    <a href="/" class="section-link">Back home</a>
                </div>

                <?php if ($dbError): ?>
                    <article class="card">
                        <h3>Tools temporarily unavailable</h3>
                        <p>
                            The tools database could not be loaded. Please check the server logs
                            and database connection.
                        </p>
                    </article>
                <?php elseif (empty($tools)): ?>
                    <article class="card">
                        <h3>No tools found</h3>
                        <p>
                            No published tools are available here yet. Add tools to the database
                            or try another category.
                        </p>
                    </article>
                <?php else: ?>
                    <div class="grid-4">
                        <?php foreach ($tools as $tool): ?>
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
            </div>
        </section>

        <section id="cta">
            <div class="container">
                <div class="cta-panel">
                    <h2>Know a tool worth adding?</h2>
                    <p>
                        Suggest a utility, diagnostic program, recovery tool, remote support app,
                        or trusted reference site for the Computer Therapy library.
                    </p>

                    <div class="cta-actions">
                        <a class="btn btn-secondary" href="/submit-tool.php">Submit a Tool</a>
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
