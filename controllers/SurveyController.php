<?php
require_once __DIR__ . '/../config.php';

class SurveyController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // Surveys CRUD
    public function listByAgent(int $agentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM surveys WHERE agent_id = :aid ORDER BY created_at DESC");
        $stmt->execute(['aid' => $agentId]);
        return $stmt->fetchAll();
    }

    public function get(int $surveyId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM surveys WHERE survey_id = :id");
        $stmt->execute(['id' => $surveyId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $agentId, string $title, string $description = null): int
    {
        $stmt = $this->db->prepare("INSERT INTO surveys (agent_id, title, description) VALUES (:aid, :title, :desc)");
        $stmt->execute(['aid' => $agentId, 'title' => $title, 'desc' => $description]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $surveyId, string $title, string $description = null): bool
    {
        $stmt = $this->db->prepare("UPDATE surveys SET title = :title, description = :desc WHERE survey_id = :id");
        return $stmt->execute(['title' => $title, 'desc' => $description, 'id' => $surveyId]);
    }

    public function delete(int $surveyId): bool
    {
        // Cascade delete choices and questions and assignments
        $this->db->prepare("DELETE FROM survey_choices WHERE question_id IN (SELECT question_id FROM survey_questions WHERE survey_id = :id)")->execute(['id' => $surveyId]);
        $this->db->prepare("DELETE FROM survey_questions WHERE survey_id = :id")->execute(['id' => $surveyId]);
        $this->db->prepare("DELETE FROM survey_assignments WHERE survey_id = :id")->execute(['id' => $surveyId]);
        $stmt = $this->db->prepare("DELETE FROM surveys WHERE survey_id = :id");
        return $stmt->execute(['id' => $surveyId]);
    }

    // Questions CRUD
    public function listQuestions(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM survey_questions WHERE survey_id = :sid ORDER BY position ASC, question_id ASC");
        $stmt->execute(['sid' => $surveyId]);
        return $stmt->fetchAll();
    }

    public function addQuestion(int $surveyId, string $type, string $text, int $position = 0): int
    {
        $stmt = $this->db->prepare("INSERT INTO survey_questions (survey_id, question_text, question_type, position) VALUES (:sid, :q, :t, :p)");
        $stmt->execute(['sid' => $surveyId, 'q' => $text, 't' => $type, 'p' => $position]);
        return (int)$this->db->lastInsertId();
    }

    public function updateQuestion(int $questionId, string $type, string $text, int $position = 0): bool
    {
        $stmt = $this->db->prepare("UPDATE survey_questions SET question_text = :q, question_type = :t, position = :p WHERE question_id = :id");
        return $stmt->execute(['q' => $text, 't' => $type, 'p' => $position, 'id' => $questionId]);
    }

    public function deleteQuestion(int $questionId): bool
    {
        $this->db->prepare("DELETE FROM survey_choices WHERE question_id = :qid")->execute(['qid' => $questionId]);
        $stmt = $this->db->prepare("DELETE FROM survey_questions WHERE question_id = :qid");
        return $stmt->execute(['qid' => $questionId]);
    }

    // Choices CRUD (for multiple_choice questions)
    public function listChoices(int $questionId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM survey_choices WHERE question_id = :qid ORDER BY position ASC, choice_id ASC");
        $stmt->execute(['qid' => $questionId]);
        return $stmt->fetchAll();
    }

    public function addChoice(int $questionId, string $text, int $position = 0): int
    {
        $stmt = $this->db->prepare("INSERT INTO survey_choices (question_id, choice_text, position) VALUES (:qid, :txt, :pos)");
        $stmt->execute(['qid' => $questionId, 'txt' => $text, 'pos' => $position]);
        return (int)$this->db->lastInsertId();
    }

    public function updateChoice(int $choiceId, string $text, int $position = 0): bool
    {
        $stmt = $this->db->prepare("UPDATE survey_choices SET choice_text = :txt, position = :pos WHERE choice_id = :cid");
        return $stmt->execute(['txt' => $text, 'pos' => $position, 'cid' => $choiceId]);
    }

    public function deleteChoice(int $choiceId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM survey_choices WHERE choice_id = :cid");
        return $stmt->execute(['cid' => $choiceId]);
    }

    // Assignments
    public function listAssignments(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT sa.client_id FROM survey_assignments sa WHERE sa.survey_id = :sid");
        $stmt->execute(['sid' => $surveyId]);
        return array_column($stmt->fetchAll(), 'client_id');
    }

    public function assignToClients(int $surveyId, array $clientIds): void
    {
        $this->db->prepare("DELETE FROM survey_assignments WHERE survey_id = :sid")->execute(['sid' => $surveyId]);
        if (empty($clientIds)) return;
        $stmt = $this->db->prepare("INSERT INTO survey_assignments (survey_id, client_id) VALUES (:sid, :cid)");
        foreach ($clientIds as $cid) {
            $stmt->execute(['sid' => $surveyId, 'cid' => (int)$cid]);
        }
    }

    public function listClients(): array
    {
        $stmt = $this->db->prepare("SELECT user_id, first_name, last_name, email FROM user WHERE role = 'client' ORDER BY first_name, last_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Front-office helpers
    public function listAssignedForClient(int $clientId): array
    {
        $sql = "SELECT s.* FROM surveys s
                JOIN survey_assignments sa ON sa.survey_id = s.survey_id
                WHERE sa.client_id = :cid
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $clientId]);
        return $stmt->fetchAll();
    }

    public function getQuestionsWithChoices(int $surveyId): array
    {
        $questions = $this->listQuestions($surveyId);
        if (!$questions) return [];
        // Collect MC question IDs
        $mcIds = array_map(function($q){ return $q['question_id']; }, array_filter($questions, function($q){ return $q['question_type'] === 'multiple_choice'; }));
        $choicesByQ = [];
        if (!empty($mcIds)) {
            $in = implode(',', array_map('intval', $mcIds));
            $stmt = $this->db->query("SELECT * FROM survey_choices WHERE question_id IN ($in) ORDER BY position ASC, choice_id ASC");
            foreach ($stmt->fetchAll() as $row) {
                $choicesByQ[$row['question_id']][] = $row;
            }
        }
        foreach ($questions as &$q) {
            $q['choices'] = ($q['question_type'] === 'multiple_choice') ? ($choicesByQ[$q['question_id']] ?? []) : [];
        }
        return $questions;
    }

    public function saveResponse(int $surveyId, int $clientId, array $answers): bool
    {
        // Check assignment
        $chk = $this->db->prepare("SELECT 1 FROM survey_assignments WHERE survey_id = :sid AND client_id = :cid");
        $chk->execute(['sid' => $surveyId, 'cid' => $clientId]);
        if (!$chk->fetch()) return false;

        // Insert response
        $this->db->prepare("INSERT INTO survey_responses (survey_id, client_id) VALUES (:sid, :cid)")
                 ->execute(['sid' => $surveyId, 'cid' => $clientId]);
        $responseId = (int)$this->db->lastInsertId();

        // Map question types
        $qStmt = $this->db->prepare("SELECT question_id, question_type FROM survey_questions WHERE survey_id = :sid");
        $qStmt->execute(['sid' => $surveyId]);
        $qTypes = [];
        foreach ($qStmt->fetchAll() as $qr) { $qTypes[(int)$qr['question_id']] = $qr['question_type']; }

        $ins = $this->db->prepare("INSERT INTO survey_answers (response_id, question_id, yes_no, rating, choice_id, answer_text)
                                   VALUES (:rid, :qid, :yn, :rating, :choice_id, :answer_text)");

        foreach ($answers as $qid => $raw) {
            $qid = (int)$qid;
            if (!isset($qTypes[$qid])) continue;
            $type = $qTypes[$qid];
            $yn = null; $rating = null; $choiceId = null; $text = null;
            if ($type === 'yes_no') {
                $val = is_string($raw) ? strtolower(trim($raw)) : $raw;
                if ($val === 'yes' || $val === '1' || $val === 1 || $val === true) { $yn = 1; }
                elseif ($val === 'no' || $val === '0' || $val === 0 || $val === false) { $yn = 0; }
                else { continue; }
            } elseif ($type === 'rating_1_5') {
                $n = (int)$raw;
                if ($n >= 1 && $n <= 5) { $rating = $n; } else { continue; }
            } elseif ($type === 'multiple_choice') {
                $choiceId = (int)$raw;
                // Validate choice belongs to question
                $cStmt = $this->db->prepare("SELECT 1 FROM survey_choices WHERE choice_id = :cid AND question_id = :qid");
                $cStmt->execute(['cid' => $choiceId, 'qid' => $qid]);
                if (!$cStmt->fetch()) continue;
            } else {
                $text = (string)$raw;
            }
            $ins->execute([
                'rid' => $responseId,
                'qid' => $qid,
                'yn' => $yn,
                'rating' => $rating,
                'choice_id' => $choiceId,
                'answer_text' => $text,
            ]);
        }
        return true;
    }

    // Reporting for agents
    public function listResponsesSummary(int $surveyId): array
    {
        // Get responses with respondent info
        // Some schemas may not have created_at on survey_responses; order by response_id DESC as fallback
        $sql = "SELECT sr.response_id, u.first_name, u.last_name, u.email
                FROM survey_responses sr
                JOIN user u ON u.user_id = sr.client_id
                WHERE sr.survey_id = :sid
                ORDER BY sr.response_id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $surveyId]);
        return $stmt->fetchAll();
    }

    public function listResponseAnswers(int $responseId): array
    {
        $sql = "SELECT a.question_id, q.question_text, q.question_type, a.yes_no, a.rating, a.choice_id, a.answer_text,
                       c.choice_text
                FROM survey_answers a
                JOIN survey_questions q ON q.question_id = a.question_id
                LEFT JOIN survey_choices c ON c.choice_id = a.choice_id
                WHERE a.response_id = :rid
                ORDER BY q.position ASC, q.question_id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rid' => $responseId]);
        return $stmt->fetchAll();
    }
}


