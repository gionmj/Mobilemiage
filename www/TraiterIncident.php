<?php
namespace App\Application\Actions\Incidents;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class TraiterIncident
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function updateIncidentStatus(int $idIncident, int $newStatus): bool
    {
        $allowedStatus = [1, 2, 3]; // Statuts autorisés

        if (!in_array($newStatus, $allowedStatus)) {
            return false; // Statut non autorisé
        }

        // Vérifier si le nouveau statut est différent du statut actuel
        $updateStatus = $this->pdo->prepare("UPDATE incidents SET idStatut = :newStatus WHERE id_incident = :idIncident");
        $success = $updateStatus->execute(['newStatus' => $newStatus, 'idIncident' => $idIncident]);

        return $success;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $idIncident = $data['idIncident'];
        $idAgent = $data['idAgent'];
        $newStatus = $data['newStatus']; // Nouvel état choisi par l'utilisateur

        // Vérification de l'existence de l'incident
        $checkIncident = $this->pdo->prepare("SELECT idStatut FROM incidents WHERE id_incident = :idIncident");
        $checkIncident->execute(['idIncident' => $idIncident]);
        $incident = $checkIncident->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            $response->getBody()->write(json_encode(['error' => "L'incident $idIncident n'existe pas"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Vérifier si le nouveau statut est différent du statut actuel
        if ($newStatus != $incident['idStatut']) {
            // Autoriser la transition vers l'état "traité" (statut 3) quel que soit l'état précédent
            $statusUpdated = $this->updateIncidentStatus($idIncident, $newStatus);

            if ($statusUpdated) {
                $statusDescription = [
                    1 => 'non traité',
                    2 => 'en cours',
                    3 => 'traité'
                ];

                $response->getBody()->write(json_encode([
                    'message' => "Le statut de l'incident $idIncident a été mis à jour avec succès",
                    'incident' => [
                        'idIncident' => $idIncident,
                        'status' => $statusDescription[$newStatus]
                    ]
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode([
                    'error' => "Échec de la mise à jour du statut pour l'incident $idIncident"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        } else {
            // Si le nouveau statut est identique au statut actuel, renvoyer un message
            $response->getBody()->write(json_encode([
                'message' => "Le statut de l'incident $idIncident est déjà à '$newStatus'"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
}
