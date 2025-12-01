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
        // Get Auftrag ID from query (?auftrag=1)
        $auftragId = $request->query->getInt('auftrag', 1);
        
        // Fetch related Termine from repository
        $termine = $this->termineRepo->findByAuftrag($auftragId);
        
        $auftrag = $this->auftragRepo->findAuftragById($auftragId);
        
        $einheiten = $auftrag->getEinheitenIst();
        
        // Choose prompt
        if ($einheiten > 25) {
            $prompt = $this->chatOpenAiService->getOver25Prompt();
        } else {
            $prompt = $this->chatOpenAiService->getUnder25Prompt();
        }
        
        // Initialize output string
        $output = '';
        
        if (empty($termine)) {
            $output = 'No Termine found.';
        } else {
            foreach ($termine as $t) {
                // Append each note with a line break
                $note = $t->getNotizen() ?? '';
                $output .= $note . "\n";
            }
        }
        
        // Decode HTML entities (e.g., &lt; → <)
        $decoded = html_entity_decode($output, ENT_QUOTES, 'UTF-8');
        
        // Remove HTML tags completely
        $plain = strip_tags($decoded);
        
        // Clean up spacing and non-breaking spaces
        $plain = str_replace(['&nbsp;', '[nbsp]', '&amp;', '[&]'], [' ', ' ', '&', 'und'], $plain);
        
        // Trim extra whitespace and return as plain text
        
        $plain = trim($plain);
        
        $responseText = $this->chatOpenAiService->chatCurl($plain . " - " . $prompt);
        
        return new Response($einheiten." ".$responseText, 200, ['Content-Type' => 'text/plain']);
    }
    
}

