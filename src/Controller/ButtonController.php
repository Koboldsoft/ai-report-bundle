<?php

namespace Koboldsoft\AiReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Koboldsoft\AiReportBundle\Repository\MmTermineRepository;
use Koboldsoft\AiReportBundle\Repository\MmAuftragRepository;
use Koboldsoft\AiReportBundle\Service\OpenAiChatService;

class ButtonController extends AbstractController
{
    private $termineRepo;
    private $auftragRepo;
    private $chatOpenAiService;
    
    
    public function __construct(MmTermineRepository $termineRepo,MmAuftragRepository $auftragRepo, OpenAiChatService $chatOpenAiService)
    {
        $this->termineRepo = $termineRepo;
        
        $this->auftragRepo = $auftragRepo;
        
        $this->chatOpenAiService = $chatOpenAiService;
    }
    
    /**
     * @Route("/button/press", name="button_press", methods={"GET", "POST"})
     */
    public function press(Request $request): Response
    {
        // Auftrag-ID aus Query (?auftrag=1), Default 1
        $auftragId = $request->query->getInt('auftrag', 1);
        
        // Termine zu diesem Auftrag holen
        $termine = $this->termineRepo->findByAuftrag($auftragId);
        
        // Auftrag selbst holen (für Einheiten)
        $auftrag = $this->auftragRepo->findAuftragById($auftragId);
        $einheiten = $auftrag ? $auftrag->getEinheitenIst() : 0;
        
        // Prompt wählen (Over/Under 25)
        if ($einheiten > 25) {
            $prompt = $this->chatOpenAiService->getPromptOver25();
        } else {
            $prompt = $this->chatOpenAiService->getPromptUnder25();
        }
        
        // Notizen der Termine zusammenbauen
        $output = '';
        
        if (empty($termine)) {
            $output = 'Keine Termindokumentationen gefunden.';
        } else {
            foreach ($termine as $t) {
                $note = $t->getNotizen() ?? '';
                $output .= $note . "\n";
            }
        }
        
        // HTML-Entities dekodieren
        $decoded = html_entity_decode($output, ENT_QUOTES, 'UTF-8');
        
        // HTML-Tags entfernen
        $plain = strip_tags($decoded);
        
        // Sonderfälle und Spacing bereinigen
        $plain = str_replace(
            ['&nbsp;', '[nbsp]', '&amp;', '[&]'],
            [' ',      ' ',      '&',     'und'],
            $plain
            );
        
        $plain = trim($plain);
        
        // Prompt + Input zusammenbauen: erst Prompt, dann Input
        $finalInput = $prompt . "\n\nINPUT START\n" . $plain;
        
        // Anfrage an OpenAI
        $responseText = $this->chatOpenAiService->chatCurl($finalInput);
        
        return new Response($responseText ?? 'Fehler bei der AI-Auswertung.', 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
    
}

