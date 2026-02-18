<?php
require_once dirname(__DIR__) . '/config/agaplinkdb.php';

class Announcement
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // GET ALL ANNOUNCEMENTS — newest first
    public function getAll(): array
    {
        $stmt = $this->conn->query(
            "SELECT a.announcement_id, a.title, a.content, a.image_path, a.created_at,
                    ad.name AS author_name
             FROM announcements a
             LEFT JOIN admin_users ad ON a.created_by = ad.id
             ORDER BY a.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET LATEST N ANNOUNCEMENTS (for landing page)
    public function getLatest(int $limit = 3): array
    {
        $stmt = $this->conn->prepare(
            "SELECT announcement_id, title, content, image_path, created_at
             FROM announcements
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CREATE NEW ANNOUNCEMENT
    public function create(string $title, string $content, int $created_by, ?string $image_path = null): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO announcements (title, content, image_path, created_by)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$title, $content, $image_path, $created_by]);
    }

    // DELETE ANNOUNCEMENT BY ID — returns image_path so caller can delete the file
    public function delete(int $announcement_id): ?string
    {
        $fetch = $this->conn->prepare(
            "SELECT image_path FROM announcements WHERE announcement_id = ?"
        );
        $fetch->execute([$announcement_id]);
        $row = $fetch->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $del = $this->conn->prepare(
            "DELETE FROM announcements WHERE announcement_id = ?"
        );
        $del->execute([$announcement_id]);

        return $row['image_path'] ?? null;
    }

    // HUMAN-READABLE RELATIVE DATE
    public static function relativeDate(string $dateString): string
    {
        $date      = new DateTime($dateString);
        $now       = new DateTime();
        $totalDays = (int)$now->diff($date)->days;

        if ($totalDays === 0)  return 'Today';
        if ($totalDays === 1)  return 'Yesterday';
        if ($totalDays < 7)   return $totalDays . ' days ago';
        if ($totalDays < 14)  return 'Last week';
        if ($totalDays < 31)  return floor($totalDays / 7) . ' weeks ago';
        if ($totalDays < 60)  return 'Last month';
        if ($totalDays < 365) return floor($totalDays / 30) . ' months ago';
        if ($totalDays < 730) return '1 year ago';
        return floor($totalDays / 365) . ' years ago';
    }
}
