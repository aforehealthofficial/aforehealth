<?php
namespace Controllers;

use Models\ProductQuestion;

class QuestionController {
    public function submit(): void {
        if (!is_authenticated()) {
            json_response(['success' => false, 'message' => 'Please log in to submit a question.'], 401);
        }

        $userId = (int)auth_user()['id'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $question = trim($_POST['question'] ?? '');

        if (!$productId || empty($question)) {
            json_response(['success' => false, 'message' => 'Please provide a valid question.'], 400);
        }

        $model = new ProductQuestion();
        $res = $model->submitQuestion($productId, $userId, $question);

        json_response($res, $res['success'] ? 200 : 422);
    }
}
