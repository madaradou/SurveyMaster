<?php
require_once __DIR__ . '/../config.php';

class CampaignController
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // CRUD (admin creates campaigns)
    public function listByAdmin(int $adminId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE created_by = :aid ORDER BY created_at DESC");
        $stmt->execute(['aid' => $adminId]);
        return $stmt->fetchAll();
    }

    public function get(int $campaignId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE campaign_id = :id");
        $stmt->execute(['id' => $campaignId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $adminId, string $title, ?string $description = null, ?string $start_at = null, ?string $end_at = null, string $status = 'draft'): int
    {
        $stmt = $this->db->prepare("INSERT INTO campaigns (created_by, title, description, start_at, end_at, status) VALUES (:aid, :title, :descr, :start_at, :end_at, :status)");
        $stmt->execute(['aid' => $adminId, 'title' => $title, 'descr' => $description, 'start_at' => $start_at, 'end_at' => $end_at, 'status' => $status]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $campaignId, string $title, ?string $description = null, ?string $start_at = null, ?string $end_at = null, string $status = 'draft'): bool
    {
        $stmt = $this->db->prepare("UPDATE campaigns SET title = :title, description = :descr, start_at = :start_at, end_at = :end_at, status = :status WHERE campaign_id = :id");
        return $stmt->execute(['title' => $title, 'descr' => $description, 'start_at' => $start_at, 'end_at' => $end_at, 'status' => $status, 'id' => $campaignId]);
    }

    public function delete(int $campaignId): bool
    {
        $this->db->prepare("DELETE FROM campaign_surveys WHERE campaign_id = :id")->execute(['id' => $campaignId]);
        $this->db->prepare("DELETE FROM campaign_agents WHERE campaign_id = :id")->execute(['id' => $campaignId]);
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE campaign_id = :id");
        return $stmt->execute(['id' => $campaignId]);
    }

    // Many-to-many: campaigns <-> surveys
    public function listSurveys(int $campaignId): array
    {
        $sql = "SELECT s.* FROM surveys s JOIN campaign_surveys cs ON cs.survey_id = s.survey_id WHERE cs.campaign_id = :cid ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $campaignId]);
        return $stmt->fetchAll();
    }

    public function getCampaignSurveyIds(int $campaignId): array
    {
        $stmt = $this->db->prepare("SELECT survey_id FROM campaign_surveys WHERE campaign_id = :cid");
        $stmt->execute(['cid' => $campaignId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'survey_id'));
    }

    public function setCampaignSurveys(int $campaignId, array $surveyIds): void
    {
        $this->db->prepare("DELETE FROM campaign_surveys WHERE campaign_id = :cid")->execute(['cid' => $campaignId]);
        if (empty($surveyIds)) return;
        $stmt = $this->db->prepare("INSERT INTO campaign_surveys (campaign_id, survey_id) VALUES (:cid, :sid)");
        foreach ($surveyIds as $sid) {
            $stmt->execute(['cid' => $campaignId, 'sid' => (int)$sid]);
        }
    }

    public function getSurveyCampaignIds(int $surveyId): array
    {
        $stmt = $this->db->prepare("SELECT campaign_id FROM campaign_surveys WHERE survey_id = :sid");
        $stmt->execute(['sid' => $surveyId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'campaign_id'));
    }

    public function setSurveyCampaigns(int $surveyId, array $campaignIds): void
    {
        $this->db->prepare("DELETE FROM campaign_surveys WHERE survey_id = :sid")->execute(['sid' => $surveyId]);
        if (empty($campaignIds)) return;
        $stmt = $this->db->prepare("INSERT INTO campaign_surveys (campaign_id, survey_id) VALUES (:cid, :sid)");
        foreach ($campaignIds as $cid) {
            $stmt->execute(['cid' => (int)$cid, 'sid' => $surveyId]);
        }
    }

    // Many-to-many: campaigns <-> agents
    public function listForAgentMember(int $agentId): array
    {
        $sql = "SELECT c.* FROM campaigns c JOIN campaign_agents ca ON ca.campaign_id = c.campaign_id WHERE ca.agent_id = :aid ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['aid' => $agentId]);
        return $stmt->fetchAll();
    }

    public function listAgents(): array
    {
        $stmt = $this->db->prepare("SELECT user_id, first_name, last_name, email FROM user WHERE role = 'agent' ORDER BY first_name, last_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCampaignAgentIds(int $campaignId): array
    {
        $stmt = $this->db->prepare("SELECT agent_id FROM campaign_agents WHERE campaign_id = :cid");
        $stmt->execute(['cid' => $campaignId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'agent_id'));
    }

    public function setCampaignAgents(int $campaignId, array $agentIds): void
    {
        $this->db->prepare("DELETE FROM campaign_agents WHERE campaign_id = :cid")->execute(['cid' => $campaignId]);
        if (empty($agentIds)) return;
        $stmt = $this->db->prepare("INSERT INTO campaign_agents (campaign_id, agent_id) VALUES (:cid, :aid)");
        foreach ($agentIds as $aid) {
            $stmt->execute(['cid' => $campaignId, 'aid' => (int)$aid]);
        }
    }
}

