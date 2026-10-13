<?php
namespace Controllers;

use PDO;

class PageController {
    public function page(string $slug): void {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Page Not Found - AFOREHEALTH']);
            return;
        }

        view('pages.show', [
            'pageTitle' => e($page['title']) . ' - AFOREHEALTH',
            'page' => $page
        ]);
    }

    public function faq(): void {
        $pdo = getDbConnection();
        $faqs = $pdo->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY category ASC, sort_order ASC")->fetchAll();

        $groupedFaqs = [];
        foreach ($faqs as $f) {
            $cat = $f['category'] ?: 'General';
            $groupedFaqs[$cat][] = $f;
        }

        view('pages.faq', [
            'pageTitle' => 'Frequently Asked Questions - AFOREHEALTH',
            'groupedFaqs' => $groupedFaqs
        ]);
    }

    public function contact(): void {
        view('pages.contact', [
            'pageTitle' => 'Contact Us & Support - AFOREHEALTH',
            'phone' => get_setting('store_phone'),
            'email' => get_setting('store_email'),
            'address' => get_setting('store_address'),
            'whatsapp' => get_setting('whatsapp_number')
        ]);
    }

    public function submitContact(): void {
        $name = trim($_POST['name'] ?? '');
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? 'General Inquiry');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || strlen($mobile) !== 10 || empty($message)) {
            set_flash('danger', 'Please provide your name, valid 10-digit mobile number, and your message.');
            redirect('/contact-us');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, mobile, email, subject, message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $mobile, !empty($email) ? $email : null, $subject, $message]);

        set_flash('success', 'Thank you! Your message has been received. Our team will get back to you within 24 hours.');
        redirect('/contact-us');
    }

    public function subscribeNewsletter(): void {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
        }

        $pdo = getDbConnection();
        try {
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            json_response(['success' => true, 'message' => 'Thank you for subscribing! Check your inbox for your 10% welcome discount code.']);
        } catch (\Exception $e) {
            json_response(['success' => true, 'message' => 'You are already subscribed to our newsletter!']);
        }
    }
}
