<?php
namespace Controllers;

use Models\Review;

class ReviewController {
    public function submit(): void {
        if (!is_authenticated()) {
            json_response(['success' => false, 'message' => 'Please login to submit a review.'], 401);
        }

        $userId = auth_user()['id'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if (!$productId || empty($title) || empty($comment)) {
            json_response(['success' => false, 'message' => 'Please provide a rating, headline, and your honest review.'], 400);
        }

        $orderId = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;

        $reviewModel = new Review();
        $result = $reviewModel->submitReview([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId,
            'rating' => $rating,
            'title' => $title,
            'comment' => $comment
        ]);

        if (!$result['success']) {
            json_response($result, 400);
        }

        json_response($result);
    }
}
