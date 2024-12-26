<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class BotManController extends Controller
{
    private $confidenceThreshold = 0.6;

        /**
     * Save chat message to database
     */
    private function saveChatMessage($message, $sender)
    {
        // Ensure user is authenticated before saving
        if (Auth::check()) {
            ChatHistory::create([
                'user_id' => Auth::id(),
                'message' => $message,
                'sender' => $sender
            ]);
        }
    }

    /**
     * Retrieve chat history for a user
     */
    private function getChatHistory($userId = null)
    {
        $userId = $userId ?? (Auth::check() ? Auth::id() : null);

        if ($userId) {
            return ChatHistory::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(50) // Limit to last 50 messages
                ->get();
        }

        return collect(); // Return empty collection if no user
    }

    /**
     * Handle the incoming messages from the BotMan chatbot.
     */
    public function handle()
    {
        $botman = app('botman');
        
        // Listen for any message
        $botman->hears('{message}', function($botman, $message) {
            // Only save if user is authenticated
            if (Auth::check()) {
                // Save user message
                $this->saveChatMessage($message, 'user');
            }

            // Convert the message to lowercase and remove special characters
            $message = strtolower(preg_replace('/[^\w\s]/', '', $message));
            
            // Get the intent and confidence score
            $intent = $this->determineIntent($message);
            
            // Existing intent handling logic
            $botResponse = null;
            if ($intent['confidence'] >= $this->confidenceThreshold) {
                switch ($intent['type']) {
                    case 'greeting':
                        $botResponse = $this->handleGreeting($botman);
                        break;
                    case 'service_info':
                        $botResponse = $this->handleServiceInfo($botman);
                        break;
                    case 'request_service':
                        $botResponse = $this->handleRequestService($botman);
                        break;
                    case 'track_status':
                        $botResponse = $this->handleTrackStatus($botman);
                        break;
                    case 'technical_support':
                        $botResponse = $this->handleTechnicalSupport($botman, $message);
                        break;
                    default:
                        $botResponse = $this->handleUnknownQuery($botman, $message);
                }
            } else {
                $botResponse = $this->handleUnknownQuery($botman, $message);
            }

            // Save bot response only if user is authenticated
            if (Auth::check() && $botResponse) {
                $this->saveChatMessage($botResponse, 'bot');
            }
        });

        $botman->listen();
    }


     /**
     * API method to retrieve chat history
     */
    public function getChatHistoryApi()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve chat history for current user
        $chatHistory = $this->getChatHistory();

        // Return as JSON response
        return response()->json($chatHistory);
    }


    /**
     * Determine the intent of the user's message
     */
    private function determineIntent($message)
    {
        $intents = [
            'greeting' => [
                'patterns' => [
                    'hi' => 1.0,
                    'hello' => 1.0,
                    'hey' => 0.9,
                    'good morning' => 0.9,
                    'good afternoon' => 0.9,
                    'good evening' => 0.9,
                    'whats up' => 0.8,
                    'greetings' => 0.8
                ],
                'keywords' => ['hi', 'hello', 'hey', 'morning', 'afternoon', 'evening', 'greetings']
            ],
            'service_info' => [
                'patterns' => [
                    'what services' => 1.0,
                    'available services' => 1.0,
                    'services offered' => 0.9,
                    'list of services' => 0.9,
                    'show services' => 0.8,
                    'tell me about services' => 0.8
                ],
                'keywords' => ['service', 'services', 'offer', 'available', 'provide', 'help', 'support']
            ],
            'request_service' => [
                'patterns' => [
                    'request service' => 1.0,
                    'submit request' => 1.0,
                    'make request' => 0.9,
                    'new request' => 0.9,
                    'create request' => 0.8,
                    'start request' => 0.8
                ],
                'keywords' => ['request', 'submit', 'create', 'new', 'make', 'start']
            ],
            'track_status' => [
                'patterns' => [
                    'track status' => 1.0,
                    'check status' => 1.0,
                    'request status' => 0.9,
                    'where is my request' => 0.9,
                    'follow up request' => 0.8
                ],
                'keywords' => ['track', 'status', 'check', 'follow', 'where']
            ],
            'technical_support' => [
                'patterns' => [
                    'technical support' => 1.0,
                    'tech support' => 1.0,
                    'help with' => 0.9,
                    'issue with' => 0.9,
                    'problem with' => 0.8
                ],
                'keywords' => ['technical', 'tech', 'support', 'help', 'issue', 'problem']
            ]
        ];

        $bestMatch = ['type' => 'unknown', 'confidence' => 0];

        foreach ($intents as $type => $data) {
            // Check exact patterns
            foreach ($data['patterns'] as $pattern => $confidence) {
                if (strpos($message, $pattern) !== false) {
                    if ($confidence > $bestMatch['confidence']) {
                        $bestMatch = ['type' => $type, 'confidence' => $confidence];
                    }
                }
            }

            // Check keywords
            if ($bestMatch['confidence'] < $this->confidenceThreshold) {
                $keywordMatches = 0;
                $totalKeywords = count($data['keywords']);
                foreach ($data['keywords'] as $keyword) {
                    if (strpos($message, $keyword) !== false) {
                        $keywordMatches++;
                    }
                }
                $confidence = $keywordMatches / $totalKeywords;
                if ($confidence > $bestMatch['confidence']) {
                    $bestMatch = ['type' => $type, 'confidence' => $confidence];
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Handle unknown queries
     */
    private function handleUnknownQuery($botman, $message)
    {
        // Log unknown queries for improvement
        Log::info('Unknown query received: ' . $message);
        
        $unknownMessage = "I'm not quite sure what you're asking about. Here are some things I can help you with:
        1. Information about our services
        2. Submitting a service request
        3. Tracking your request status
        4. Technical support

        Please let me know which one you'd like to know more about!";

        $botman->reply($unknownMessage);
        return $unknownMessage;
    }

    /**
     * Handle greeting messages
     */
    private function handleGreeting($botman)
    {
        $responses = [
            "Hello! Welcome to the Service Request Management System support. How can I assist you today?",
            "Hi there! Welcome to the Service Request Management System support. What can I help you with?",
            "Greetings! Welcome to the Service Request Management System. How may I help you?",
            "Hello! Welcome to the Service Request Management System I'm here to help. What do you need?"
        ];
        
        $response = $responses[array_rand($responses)];
        $botman->reply($response);
        return $response;
    }

    /**
     * Handle service information requests
     */
    private function handleServiceInfo($botman)
    {
        $serviceInfo = "Here are the services we offer:

        1. Account Management:
           • MS Office 365, MS Teams, TUP Email
           • Password Reset and Account Updates

        2. Technical Support:
           • Computer and Printer Issues
           • Internet Connectivity
           • Software Installation

        3. Equipment Services:
           • Hardware Repairs
           • Printer Support
           • Network Setup

        Would you like more details about any specific service?";

        $botman->reply($serviceInfo);
        return $serviceInfo; // Return the response string
    }

    /**
     * Handle service request guidance
     */
    private function handleRequestService($botman)
    {
        $requestMessage ="To submit a service request, please follow these steps:
        1. Log into your account
        2. Go to the 'Submit Request' section
        3. Fill out the service request form
        4. Provide all necessary details
        5. Submit your request

        Would you like me to guide you to the request form?";

        $botman->reply($requestMessage);
        return $requestMessage;
    }

    /**
     * Handle status tracking queries
     */
    private function handleTrackStatus($botman)
    {
        $statusMessage = "You can track your service request status by:
        1. Logging into your account
        2. Going to 'My Requests' section
        3. Finding your request using the ID or date
        
        Do you need help finding your request?";

        $botman->reply($statusMessage);
        return $statusMessage;
    }

    /**
     * Handle technical support queries
     */
    private function handleTechnicalSupport($botman, $message)
    {
        $supportMessage = "";
        
        if (strpos($message, 'internet') !== false) {
            $supportMessage = "For internet issues, try these steps:
            1. Restart your router
            2. Check cable connections
            3. Run network diagnostics
            
            If the problem persists, please submit a service request.";
        } elseif (strpos($message, 'printer') !== false) {
            $supportMessage = "For printer issues:
            1. Check if the printer is powered on
            2. Verify paper and ink levels
            3. Ensure printer is connected to network
            
            Need more help? Submit a service request.";
        } else {
            $supportMessage = "For technical support, please:
            1. Describe your issue in detail
            2. Submit a service request
            3. Our team will respond promptly
            
            Would you like to submit a request now?";
        }
    
        $botman->reply($supportMessage);
        return $supportMessage;
    }
    

}
