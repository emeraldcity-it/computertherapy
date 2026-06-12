<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

/*
|--------------------------------------------------------------------------
| Fallback content
|--------------------------------------------------------------------------
| These keep the homepage from looking empty if the database is unavailable
| or if you have not added enough records yet.
*/

$featuredGuides = [
    [
        'title' => 'Safe first steps when a Windows PC feels slow',
        'slug' => 'windows-pc-feels-slow-first-steps',
        'summary' => 'Check startup load, background apps, storage pressure, updates, and simple malware indicators before making bigger changes.',
        'badge' => 'Quick Fix',
        'category_name' => 'Windows',
        'difficulty' => 'Beginner friendly',
        'estimated_minutes' => 8,
    ],
    [
        'title' => 'Basic network troubleshooting checklist for home and small office setups',
        'slug' => 'basic-network-troubleshooting-checklist',
        'summary' => 'Follow a structured path from link lights and DHCP to DNS resolution, router checks, and upstream testing.',
        'badge' => 'How-To',
        'category_name' => 'Networking',
        'difficulty' => 'Checklist',
        'estimated_minutes' => 10,
    ],
    [
        'title' => 'How to get help remotely without giving up control',
        'slug' => 'remote-help-without-giving-up-control',
        'summary' => 'Understand ad-hoc support, session consent, privacy basics, and the difference between one-time help and unattended access.',
        'badge' => 'Remote Support',
        'category_name' => 'Security',
        'difficulty' => 'Remote support',
        'estimated_minutes' => 6,
    ],
];

$featuredTools = [
    [
        'name' => 'RustDesk',
        'slug' => 'rustdesk',
        'description' => 'Remote support and desktop access for guided troubleshooting and support sessions.',
        'category_name' => 'Remote',
        'website_url' => 'https://rustdesk.com',
    ],
    [
        'name' => 'Wireshark',
        'slug' => 'wireshark',
        'description' => 'Packet analysis for diagnosing traffic issues, protocol problems, and connectivity mysteries.',
        'category_name' => 'Network',
        'website_url' => 'https://www.wireshark.org',
    ],
    [
        'name' => 'CrystalDiskInfo',
        'slug' => 'crystaldiskinfo',
        'description' => 'Quick SMART visibility to check drive health before a disk problem becomes a data problem.',
        'category_name' => 'Storage',
        'website_url' => 'https://crystalmark.info/en',
    ],
    [
        'name' => 'Ventoy',
        'slug' => 'ventoy',
        'description' => 'Build a flexible multiboot USB toolkit for diagnostics, installers, and rescue utilities.',
        'category_name' => 'Boot',
        'website_url' => 'https://www.ventoy.net',
    ],
];

$categories = [
    [
        'name' => 'Windows support',
        'slug' => 'windows-support',
        'label' => 'Systems',
        'description' => 'Troubleshooting, updates, recovery steps, cleanup, and performance tuning.',
    ],
    [
        'name' => 'Linux and self-hosting',
        'slug' => 'linux-self-hosting',
        'label' => 'Open source',
        'description' => 'Server basics, containers, services, permissions, logs, and deployment notes.',
    ],
    [
        'name' => 'Networking and routers',
        'slug' => 'networking-routers',
        'label' => 'Infrastructure',
        'description' => 'DNS, Wi-Fi, switching, firewalls, WAN issues, and connectivity diagnostics.',
    ],
    [
        'name' => 'Security and malware',
        'slug' => 'security-malware',
        'label' => 'Protection',
        'description' => 'Safe handling steps, suspicious behavior checks, hygiene, and recovery guidance.',
    ],
    [
        'name' => 'Backup and recovery',
        'slug' => 'backup-recovery',
        'label' => 'Resilience',
        'description' => 'Planning, verification, restore testing, storage health, and disaster-readiness basics.',
    ],
    [
        'name' => 'Utilities and scripts',
        'slug' => 'utilities-scripts',
        'label' => 'Toolkit',
        'description' => 'Bookmarkable software, command-line helpers, and practical reference links.',
    ],
];

$recentDiscussions = [
    [
        'title' => 'Best lightweight remote support tools for home users',
        'slug' => 'best-lightweight-remote-support-tools',
        'category_name' => 'Remote Support',
        'reply_count' => 12,
        'updated_label' => 'Updated recently',
    ],
    [
        'title' => 'What should be in a modern bootable USB toolkit?',
        'slug' => 'modern-bootable-usb-toolkit',
        'category_name' => 'Utilities',
        'reply_count' => 8,
        'updated_label' => 'Updated today',
    ],
    [
        'title' => 'DNS issue or ISP issue: fastest way to tell?',
        'slug' => 'dns-or-isp-issue',
        'category_name' => 'Networking',
        'reply_count' => 19,
        'updated_label' => 'Updated today',
    ],
    [
        'title' => 'Early signs a hard drive is about to fail',
        'slug' => 'early-signs-hard-drive-failure',
        'category_name' => 'Backup & Recovery',
        'reply_count' => 6,
        'updated_label' => 'Updated yesterday',
    ],
];

/*
|--------------------------------------------------------------------------
| Database content
|--------------------------------------------------------------------------
| If the database is working and has records, it replaces the fallback content.
| If anything fails, the page still loads and logs the database error.
*/

try {
    $dbGuides = get_featured_guides(3);
    $dbTools = get_featured_tools(4);
    $dbCategories = get_active_categories();

    if (!empty($dbGuides)) {
        $featuredGuides = $dbGuides;
    }

    if (!empty($dbTools)) {
        $featuredTools = $dbTools;
    }

    if (!empty($dbCategories)) {
        $categories = $dbCategories;
    }

    try {
        $stmt = db()->query("
            SELECT 
                d.*,
                c.name AS category_name
            FROM discussions d
            LEFT JOIN categories c ON c.id = d.category_id
            WHERE d.is_published = 1
            ORDER BY COALESCE(d.updated_at, d.created_at) DESC
            LIMIT 4
        ");

        $dbDiscussions = $stmt->fetchAll();

        if (!empty($dbDiscussions)) {
            $recentDiscussions = $dbDiscussions;
        }
    } catch (Throwable $e) {
        error_log('Computer Therapy discussions query error: ' . $e->getMessage());
    }
} catch (Throwable $e) {
    error_log('Computer Therapy homepage DB error: ' . $e->getMessage());
}

$pageTitle = 'Computer Therapy | Knowledge Hub';
$pageDescription = 'Computer Therapy is a practical knowledge hub for troubleshooting guides, trusted utilities, and real-world computer support resources.';
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
                <a href="#guides">Guides</a>
                <a href="#tools">Tools</a>
                <a href="#categories">Topics</a>
                <a href="#discussions">Discussions</a>
                <a href="#about">About</a>
            </nav>

            <div class="nav-actions">
                <a class="btn btn-secondary" href="#search">Search</a>
                <a class="btn btn-primary" href="/request-help.php">Ask a Question</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="eyebrow">Knowledge Hub • Tools • Troubleshooting</div>

                    <h1>Practical computer help without the clutter.</h1>

                    <p>
                        Computer Therapy is a growing knowledge hub for troubleshooting guides, useful utilities,
                        and real-world fixes for Windows, Linux, networking, security, and backup problems.
                    </p>

                    <p>
                        Start with a guide, browse trusted tools, or use the request-help workflow when you need
                        guided troubleshooting.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn-primary" href="#guides">Browse Guides</a>
                        <a class="btn btn-secondary" href="#tools">Explore Tools</a>
                    </div>
                </div>

                <aside class="hero-panel" aria-label="Featured brand panel">
                    <img src="/assets/img/computertherapy.png" alt="Computer Therapy homepage logo feature" />

                    <div class="panel-caption">
                        <div class="mini-stat">
                            <strong>Curated resources</strong>
                            <span>Tools and links worth bookmarking.</span>
                        </div>

                        <div class="mini-stat">
                            <strong>Real fixes</strong>
                            <span>Guides built from actual support scenarios.</span>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="search-band" id="search">
            <div class="container">
                <form class="search-card" action="/search.php" method="get">
                    <div class="search-row">
                        <input
                            type="search"
                            name="q"
                            placeholder="Search guides, tools, troubleshooting topics, and discussions..."
                            aria-label="Search the knowledge hub"
                        />

                        <button class="btn btn-primary" type="submit">Search Hub</button>
                    </div>

                    <div class="quick-tags">
                        <a class="tag" href="#categories">Windows</a>
                        <a class="tag" href="#categories">Linux</a>
                        <a class="tag" href="#categories">Networking</a>
                        <a class="tag" href="#categories">Security</a>
                        <a class="tag" href="#categories">Remote Support</a>
                        <a class="tag" href="#categories">Backup</a>
                    </div>
                </form>
            </div>
        </section>

        <section id="guides">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>Featured guides</h2>
                        <p>Start with concise, practical walkthroughs that solve common problems fast.</p>
                    </div>

                    <a href="/guides.php" class="section-link">View all guides</a>
                </div>

                <div class="grid-3">
                    <?php foreach ($featuredGuides as $guide): ?>
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
            </div>
        </section>

        <section id="tools">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>Useful tools</h2>
                        <p>A curated shelf of utilities, diagnostics, and trusted sites for troubleshooting and maintenance.</p>
                    </div>

                    <a href="/tools.php" class="section-link">Browse the tools library</a>
                </div>

                <div class="grid-4">
                    <?php foreach ($featuredTools as $tool): ?>
                        <?php
                            $toolName = $tool['name'] ?? 'Unnamed tool';
                            $toolUrl = $tool['website_url'] ?? '';
                            $toolCategory = $tool['category_name'] ?? 'Tool';
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
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="categories">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>Browse by topic</h2>
                        <p>Use broad categories first, then drill into specific issues, utilities, and fixes.</p>
                    </div>
                </div>

                <div class="grid-3">
                    <?php foreach ($categories as $category): ?>
                        <article class="card category-card">
                            <small><?= e($category['label'] ?? 'Topic') ?></small>

                            <h3>
                                <a href="/category.php?slug=<?= e($category['slug'] ?? '') ?>">
                                    <?= e($category['name'] ?? 'Untitled category') ?>
                                </a>
                            </h3>

                            <p><?= e($category['description'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="discussions">
            <div class="container">
                <div class="section-head">
                    <div>
                        <h2>Recent discussions</h2>
                        <p>Seed the community with real troubleshooting prompts, field notes, and comparison threads.</p>
                    </div>

                    <a href="/discussions.php" class="section-link">Visit discussions</a>
                </div>

                <div class="grid-2">
                    <div class="card discussion">
                        <?php foreach ($recentDiscussions as $discussion): ?>
                            <?php
                                $replyCount = (int)($discussion['reply_count'] ?? 0);

                                if (!empty($discussion['updated_at'])) {
                                    $updatedLabel = 'Updated ' . date('M j, Y', strtotime($discussion['updated_at']));
                                } elseif (!empty($discussion['created_at'])) {
                                    $updatedLabel = 'Posted ' . date('M j, Y', strtotime($discussion['created_at']));
                                } else {
                                    $updatedLabel = $discussion['updated_label'] ?? 'Updated recently';
                                }
                            ?>

                            <div class="thread">
                                <div>
                                    <strong>
                                        <a href="/discussion.php?slug=<?= e($discussion['slug'] ?? '') ?>">
                                            <?= e($discussion['title'] ?? 'Untitled discussion') ?>
                                        </a>
                                    </strong>

                                    <span>
                                        <?= e($discussion['category_name'] ?? 'Discussion') ?>
                                        • <?= $replyCount ?> <?= $replyCount === 1 ? 'reply' : 'replies' ?>
                                        • <?= e($updatedLabel) ?>
                                    </span>
                                </div>

                                <div class="count">
                                    <?= $replyCount ?> <?= $replyCount === 1 ? 'reply' : 'replies' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <aside class="card" id="about">
                        <span class="badge">Why this site</span>

                        <h3>Built for practical troubleshooting</h3>

                        <p>
                            Computer Therapy is designed as a useful front door for people who need help fast:
                            searchable guides, trustworthy tools, and troubleshooting notes grounded in real support work.
                        </p>

                        <div class="meta">
                            <span>Knowledge hub</span>
                            <span>Curated links</span>
                            <span>Community learning</span>
                        </div>

                        <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0;">

                        <p>
                            Over time, this can grow into a deeper library with utility pages, troubleshooting checklists,
                            contributor posts, and a dedicated request-help workflow.
                        </p>
                    </aside>
                </div>
            </div>
        </section>

        <section id="cta">
            <div class="container">
                <div class="cta-panel">
                    <h2>Have a problem that needs a real human?</h2>

                    <p>
                        Use the knowledge hub to self-serve first, then reach out when you need guided help,
                        remote troubleshooting, or a second set of eyes on a stubborn issue.
                    </p>

                    <div class="cta-actions">
                        <a class="btn btn-secondary" href="/request-help.php">Request Help</a>
                        <a class="btn btn-secondary" href="/submit-tool.php">Submit a Tool</a>
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
                <a href="#guides">Guides</a>
                <a href="#tools">Tools</a>
                <a href="#categories">Topics</a>
                <a href="#discussions">Discussions</a>
            </div>

            <div>
                <h4>Next additions</h4>
                <a href="#about">About</a>
                <a href="/request-help.php">Contact</a>
                <a href="/privacy.php">Privacy</a>
                <a href="/admin/">Admin</a>
            </div>
        </div>
    </footer>

    <script src="/assets/js/home.js"></script>
</body>
</html>

