<?php
namespace Controllers\Admin;

use PDO;

class AdminBannerController {
    public function index(): void {
        $pdo = getDbConnection();
        $banners = $pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll();

        view('admin.banners.index', [
            'pageTitle' => 'Home Banners Management - AFOREHEALTH Admin',
            'banners' => $banners
        ]);
    }

    public function store(): void {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $link = trim($_POST['link'] ?? '/shop');
        $ctaText = trim($_POST['cta_text'] ?? 'Shop Now');
        $position = trim($_POST['position'] ?? 'home_hero');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (empty($title)) {
            set_flash('danger', 'Banner title is required.');
            redirect('/admin/banners');
        }

        // Upload Desktop Image
        $imageDesktop = null;
        if (!empty($_FILES['image_desktop']['tmp_name'])) {
            $imageDesktop = upload_file($_FILES['image_desktop'], 'banners');
        }

        if (!$imageDesktop) {
            set_flash('danger', 'Desktop banner image is required.');
            redirect('/admin/banners');
        }

        // Upload Mobile Image (optional)
        $imageMobile = null;
        if (!empty($_FILES['image_mobile']['tmp_name'])) {
            $imageMobile = upload_file($_FILES['image_mobile'], 'banners');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO banners (title, subtitle, image_desktop, image_mobile, link, cta_text, position, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $subtitle, $imageDesktop, $imageMobile, $link, $ctaText, $position, $sortOrder, $status
        ]);

        set_flash('success', "Banner '{$title}' created successfully.");
        redirect('/admin/banners');
    }

    public function update(int $id): void {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $link = trim($_POST['link'] ?? '/shop');
        $ctaText = trim($_POST['cta_text'] ?? 'Shop Now');
        $position = trim($_POST['position'] ?? 'home_hero');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if (!$banner) {
            set_flash('danger', 'Banner not found.');
            redirect('/admin/banners');
        }

        $imageDesktop = $banner['image_desktop'];
        if (!empty($_FILES['image_desktop']['tmp_name'])) {
            $newDesktop = upload_file($_FILES['image_desktop'], 'banners');
            if ($newDesktop) {
                $imageDesktop = $newDesktop;
            }
        }

        $imageMobile = $banner['image_mobile'];
        if (!empty($_FILES['image_mobile']['tmp_name'])) {
            $newMobile = upload_file($_FILES['image_mobile'], 'banners');
            if ($newMobile) {
                $imageMobile = $newMobile;
            }
        }

        $upd = $pdo->prepare("
            UPDATE banners SET
                title = ?, subtitle = ?, image_desktop = ?, image_mobile = ?,
                link = ?, cta_text = ?, position = ?, sort_order = ?, status = ?
            WHERE id = ?
        ");
        $upd->execute([
            $title, $subtitle, $imageDesktop, $imageMobile,
            $link, $ctaText, $position, $sortOrder, $status,
            $id
        ]);

        set_flash('success', "Banner '{$title}' updated successfully.");
        redirect('/admin/banners');
    }

    public function toggle(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT status FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $cur = $stmt->fetchColumn();

        if ($cur !== false) {
            $newStatus = $cur === 'active' ? 'inactive' : 'active';
            $pdo->prepare("UPDATE banners SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            set_flash('success', "Banner status changed to {$newStatus}.");
        }

        redirect('/admin/banners');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Banner deleted successfully.');
        redirect('/admin/banners');
    }
}
