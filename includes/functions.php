<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_featured_guides(int $limit = 3): array
{
    $stmt = db()->prepare("
        SELECT g.*, c.name AS category_name
        FROM guides g
        LEFT JOIN categories c ON c.id = g.category_id
        WHERE g.is_published = 1 AND g.is_featured = 1
        ORDER BY g.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function get_featured_tools(int $limit = 4): array
{
    $stmt = db()->prepare("
        SELECT t.*, c.name AS category_name
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_published = 1 AND t.is_featured = 1
        ORDER BY t.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function get_active_categories(): array
{
    $stmt = db()->query("
        SELECT *
        FROM categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, name ASC
    ");

    return $stmt->fetchAll();
}

function get_guide_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("
        SELECT
            g.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM guides g
        LEFT JOIN categories c ON c.id = g.category_id
        WHERE g.slug = :slug
          AND g.is_published = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':slug' => $slug
    ]);

    $guide = $stmt->fetch();

    return $guide ?: null;
}

function get_all_guides(): array
{
    $stmt = db()->query("
        SELECT
            g.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM guides g
        LEFT JOIN categories c ON c.id = g.category_id
        WHERE g.is_published = 1
        ORDER BY g.is_featured DESC, g.created_at DESC
    ");

    return $stmt->fetchAll();
}

function get_guides_by_category_slug(string $categorySlug): array
{
    $stmt = db()->prepare("
        SELECT
            g.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM guides g
        INNER JOIN categories c ON c.id = g.category_id
        WHERE g.is_published = 1
          AND c.slug = :category_slug
          AND c.is_active = 1
        ORDER BY g.is_featured DESC, g.created_at DESC
    ");

    $stmt->execute([
        ':category_slug' => $categorySlug
    ]);

    return $stmt->fetchAll();
}

function get_category_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("
        SELECT *
        FROM categories
        WHERE slug = :slug
          AND is_active = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':slug' => $slug
    ]);

    $category = $stmt->fetch();

    return $category ?: null;
}

function get_all_tools(): array
{
    $stmt = db()->query("
        SELECT
            t.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_published = 1
        ORDER BY t.is_featured DESC, t.name ASC
    ");

    return $stmt->fetchAll();
}

function get_tools_by_category_slug(string $categorySlug): array
{
    $stmt = db()->prepare("
        SELECT
            t.*,
            c.name AS category_name,
            c.slug AS category_slug
        FROM tools t
        INNER JOIN categories c ON c.id = t.category_id
        WHERE t.is_published = 1
          AND c.slug = :category_slug
          AND c.is_active = 1
        ORDER BY t.is_featured DESC, t.name ASC
    ");

    $stmt->execute([
        ':category_slug' => $categorySlug
    ]);

    return $stmt->fetchAll();
}

function search_guides(string $query, int $limit = 20): array
{
    $like = '%' . $query . '%';

    $stmt = db()->prepare("
        SELECT
            g.*,
            c.name AS category_name,
            c.slug AS category_slug,
            'guide' AS result_type
        FROM guides g
        LEFT JOIN categories c ON c.id = g.category_id
        WHERE g.is_published = 1
          AND (
              g.title LIKE :query
              OR g.summary LIKE :query
              OR g.body LIKE :query
              OR c.name LIKE :query
          )
        ORDER BY g.is_featured DESC, g.created_at DESC
        LIMIT :limit
    ");

    $stmt->bindValue(':query', $like, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function search_tools(string $query, int $limit = 20): array
{
    $like = '%' . $query . '%';

    $stmt = db()->prepare("
        SELECT
            t.*,
            c.name AS category_name,
            c.slug AS category_slug,
            'tool' AS result_type
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_published = 1
          AND (
              t.name LIKE :query
              OR t.description LIKE :query
              OR t.platform LIKE :query
              OR t.license_type LIKE :query
              OR c.name LIKE :query
          )
        ORDER BY t.is_featured DESC, t.name ASC
        LIMIT :limit
    ");

    $stmt->bindValue(':query', $like, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function search_categories(string $query, int $limit = 10): array
{
    $like = '%' . $query . '%';

    $stmt = db()->prepare("
        SELECT
            *,
            'category' AS result_type
        FROM categories
        WHERE is_active = 1
          AND (
              name LIKE :query
              OR label LIKE :query
              OR description LIKE :query
          )
        ORDER BY sort_order ASC, name ASC
        LIMIT :limit
    ");

    $stmt->bindValue(':query', $like, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}
?>

