<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Sujets de tickets réalistes
     */
    private array $subjects = [
        'technical' => [
            'Impossible de se connecter à mon compte',
            'Erreur 500 lors de l\'accès au dashboard',
            'Le site est très lent depuis ce matin',
            'Problème d\'affichage sur mobile',
            'Les images ne se chargent pas',
            'Erreur lors du téléchargement de fichiers',
            'Le formulaire de contact ne fonctionne pas',
            'Problème avec l\'authentification à deux facteurs',
            'Les notifications ne s\'affichent plus',
            'Erreur lors de la sauvegarde des données',
        ],
        'billing' => [
            'Problème avec ma dernière facture',
            'Double prélèvement sur ma carte bancaire',
            'Je n\'ai pas reçu ma facture du mois',
            'Demande de remboursement',
            'Modification de mes informations de paiement',
            'Question sur les frais supplémentaires',
            'Résiliation de mon abonnement',
            'Upgrade vers un plan supérieur',
            'Code promo non appliqué',
            'Demande de devis personnalisé',
        ],
        'feature_request' => [
            'Ajout d\'un système de recherche avancée',
            'Possibilité d\'exporter les données en PDF',
            'Intégration avec Slack',
            'Mode sombre pour l\'interface',
            'Application mobile iOS',
            'Notifications par email',
            'Système de tags pour l\'organisation',
            'Ajout de graphiques et statistiques',
            'Import/Export CSV',
            'API REST pour intégrations',
        ],
        'bug' => [
            'Le bouton de validation ne répond pas',
            'Les données ne se synchronisent pas',
            'Erreur JavaScript dans la console',
            'Le compteur affiche des valeurs incorrectes',
            'Problème d\'encodage des caractères spéciaux',
            'Les filtres de recherche ne fonctionnent pas',
            'Pagination cassée sur la liste',
            'Problème de cache navigateur',
            'Affichage incorrect des dates',
            'Les liens de partage sont brisés',
        ],
        'other' => [
            'Question générale sur l\'utilisation',
            'Demande de documentation',
            'Suggestion d\'amélioration',
            'Demande de formation',
            'Question sur la sécurité des données',
            'Demande de support technique',
            'Information sur les mises à jour',
            'Question sur la conformité RGPD',
            'Demande de partenariat',
            'Feedback général',
        ],
    ];

    /**
     * Descriptions détaillées par type
     */
    private array $descriptions = [
        'technical' => [
            "Bonjour,\n\nDepuis ce matin, je ne parviens plus à accéder à mon compte. Le message d'erreur indique 'Identifiants incorrects' alors que je suis certain d'utiliser les bons.\n\nJ'ai déjà essayé de réinitialiser mon mot de passe mais je n'ai pas reçu l'email.\n\nPouvez-vous m'aider ?\n\nMerci",
            "Bonjour,\n\nJ'ai un problème technique urgent. Lorsque j'essaie d'accéder à certaines pages, j'obtiens une erreur 500.\n\nCela se produit notamment sur la page de configuration et le tableau de bord.\n\nPouvez-vous vérifier s'il y a un problème de votre côté ?\n\nCordialement",
            "Bonjour,\n\nLe site est extrêmement lent depuis quelques heures. Les pages mettent plus de 10 secondes à charger.\n\nCela rend l'utilisation presque impossible. Y a-t-il un problème en cours ?\n\nMerci de votre aide",
        ],
        'billing' => [
            "Bonjour,\n\nJ'ai constaté sur mon relevé bancaire que j'ai été prélevé deux fois pour le même mois.\n\nMontant : 49.99€ x2\nDate : 15/12/2024\n\nPouvez-vous vérifier et me rembourser le double prélèvement ?\n\nMerci",
            "Bonjour,\n\nJe souhaiterais obtenir une copie de ma facture du mois dernier.\n\nJe ne l'ai pas reçue par email et je ne la trouve pas dans mon espace client.\n\nPouvez-vous me l'envoyer ?\n\nCordialement",
            "Bonjour,\n\nJe souhaite résilier mon abonnement. Comment dois-je procéder ?\n\nMon contrat se termine-t-il immédiatement ou à la fin du mois ?\n\nMerci de me confirmer la procédure.",
        ],
        'feature_request' => [
            "Bonjour,\n\nJe trouve votre produit excellent mais il manque une fonctionnalité importante pour moi.\n\nSerait-il possible d'ajouter un système d'export en PDF des rapports ?\n\nCela me permettrait de partager facilement les données avec mes clients.\n\nMerci de considérer cette demande !",
            "Bonjour l'équipe,\n\nUne suggestion d'amélioration : ce serait génial d'avoir une application mobile.\n\nJe travaille souvent en déplacement et l'interface mobile web n'est pas très pratique.\n\nAvez-vous prévu quelque chose dans ce sens ?\n\nMerci !",
            "Bonjour,\n\nSerait-il possible d'intégrer une API REST ?\n\nJ'aimerais connecter votre service à mes autres outils de travail.\n\nUne documentation technique serait également la bienvenue.\n\nMerci d'avance !",
        ],
        'bug' => [
            "Bonjour,\n\nJ'ai découvert un bug dans l'interface.\n\nLorsque je clique sur le bouton 'Sauvegarder', rien ne se passe. Le formulaire ne se soumet pas.\n\nJ'utilise Chrome sur Windows 11.\n\nPouvez-vous corriger ce problème ?\n\nMerci",
            "Bonjour,\n\nIl semble y avoir un problème avec les données affichées.\n\nLes chiffres du dashboard ne correspondent pas à ceux des rapports détaillés.\n\nY a-t-il un souci de synchronisation ?\n\nMerci de vérifier",
            "Bonjour,\n\nJ'ai remarqué que les caractères accentués ne s'affichent pas correctement.\n\nAu lieu de 'é', j'ai des caractères bizarres.\n\nEst-ce un problème d'encodage ?\n\nCordialement",
        ],
        'other' => [
            "Bonjour,\n\nJe suis nouveau sur la plateforme et j'aurais quelques questions sur l'utilisation.\n\nOù puis-je trouver la documentation complète ?\n\nY a-t-il des tutoriels vidéo disponibles ?\n\nMerci pour votre aide !",
            "Bonjour,\n\nJe suis très satisfait de votre service et je voulais vous faire part de quelques suggestions.\n\nL'interface est intuitive mais quelques petits détails pourraient être améliorés.\n\nSeriez-vous intéressés par mes retours ?\n\nCordialement",
            "Bonjour,\n\nJ'ai une question concernant la sécurité de mes données.\n\nComment sont-elles stockées et protégées ?\n\nÊtes-vous conforme au RGPD ?\n\nMerci de m'éclairer sur ce point.",
        ],
    ];

    /**
     * Réponses types des agents
     */
    private array $agentReplies = [
        "Bonjour,\n\nMerci pour votre message.\n\nJ'ai bien pris en compte votre demande et je vais regarder ça de plus près.\n\nJe reviens vers vous très rapidement avec une solution.\n\nCordialement",
        "Bonjour,\n\nNous avons identifié le problème.\n\nNous travaillons actuellement sur une correction qui sera déployée dans les prochaines heures.\n\nJe vous tiendrai informé de l'avancement.\n\nMerci de votre patience !",
        "Bonjour,\n\nLe problème a été résolu de notre côté.\n\nPouvez-vous vérifier et me confirmer que tout fonctionne correctement maintenant ?\n\nN'hésitez pas si vous avez d'autres questions.\n\nBonne journée !",
        "Bonjour,\n\nVotre demande a été transmise à notre équipe technique.\n\nNous allons analyser la faisabilité et vous reviendrons avec un délai estimé.\n\nMerci pour votre suggestion !\n\nCordialement",
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer des utilisateurs (clients)
        $users = User::where('is_admin', false)->get();
        
        // Récupérer des admins (agents)
        $admins = User::where('is_admin', true)->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur trouvé. Créez des utilisateurs d\'abord.');
            return;
        }

        $this->command->info('🎫 Création de tickets de test...');

        $ticketsCreated = 0;
        $repliesCreated = 0;

        // Créer 50 tickets réalistes
        foreach (range(1, 50) as $index) {
            // Choisir une catégorie aléatoire
            $categories = array_keys($this->subjects);
            $category = $categories[array_rand($categories)];

            // Choisir un sujet et description
            $subject = $this->subjects[$category][array_rand($this->subjects[$category])];
            $description = $this->descriptions[$category][array_rand($this->descriptions[$category])];

            // Priorités pondérées (plus de medium/low que urgent)
            $priorities = ['low', 'low', 'medium', 'medium', 'medium', 'high', 'urgent'];
            $priority = $priorities[array_rand($priorities)];

            // Statuts pondérés
            $statuses = ['open', 'open', 'in_progress', 'in_progress', 'waiting', 'resolved', 'resolved', 'closed'];
            $status = $statuses[array_rand($statuses)];

            // Assigner ou non
            $assigned_to = (rand(1, 100) > 30 && $admins->isNotEmpty()) ? $admins->random()->id : null;

            // Date de création aléatoire (entre 60 jours et maintenant)
            $createdAt = now()->subDays(rand(1, 60))->subHours(rand(0, 23));

            // Créer le ticket
            $ticket = Ticket::create([
                'user_id' => $users->random()->id,
                'subject' => $subject,
                'description' => $description,
                'priority' => $priority,
                'status' => $status,
                'category' => $category,
                'assigned_to' => $assigned_to,
                'resolved_at' => in_array($status, ['resolved', 'closed']) ? $createdAt->copy()->addDays(rand(1, 5)) : null,
                'closed_at' => $status === 'closed' ? $createdAt->copy()->addDays(rand(2, 7)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $ticketsCreated++;

            // Ajouter des réponses (30% des tickets ont des réponses)
            if (rand(1, 100) <= 70 && $admins->isNotEmpty()) {
                $numberOfReplies = rand(1, 4);
                
                for ($i = 0; $i < $numberOfReplies; $i++) {
                    $replyDate = $createdAt->copy()->addHours(rand(2, 48));
                    
                    // Alterner entre client et agent
                    $isFromAgent = ($i % 2 === 1);
                    
                    TicketReply::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $isFromAgent ? ($assigned_to ?? $admins->random()->id) : $ticket->user_id,
                        'message' => $isFromAgent 
                            ? $this->agentReplies[array_rand($this->agentReplies)]
                            : "Merci pour votre réponse. Je confirme que le problème persiste / est résolu.",
                        'is_internal' => false,
                        'created_at' => $replyDate,
                        'updated_at' => $replyDate,
                    ]);
                    
                    $repliesCreated++;
                }
            }
        }

        $this->command->info("✅ {$ticketsCreated} tickets créés avec succès !");
        $this->command->info("💬 {$repliesCreated} réponses créées !");
        
        // Statistiques
        $this->command->newLine();
        $this->command->info('📊 Statistiques :');
        $this->command->table(
            ['Statut', 'Nombre'],
            [
                ['Ouverts', Ticket::where('status', 'open')->count()],
                ['En cours', Ticket::where('status', 'in_progress')->count()],
                ['En attente', Ticket::where('status', 'waiting')->count()],
                ['Résolus', Ticket::where('status', 'resolved')->count()],
                ['Fermés', Ticket::where('status', 'closed')->count()],
                ['Urgents', Ticket::where('priority', 'urgent')->count()],
            ]
        );
    }
}