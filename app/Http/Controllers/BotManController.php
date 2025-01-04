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
                    case 'working_hours':
                        $botResponse = $this->handleWorkingHours($botman);
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
                    'what is your services' => 1.0,
                    'Information about our services' => 1.0,
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
                    'follow up request' => 0.8,
                ],
                'keywords' => ['track', 'status', 'check', 'follow', 'where']
            ],
            'working_hours' => [
                'patterns' => [
                    'what are the working hours' => 1.0,
                    'uitc hours' => 1.0,
                    'office hours' => 0.9,
                    'when is uitc open' => 0.9,
                    'uitc operating hours' => 0.9
                ],
                'keywords' => ['hours', 'open', 'time', 'working', 'uitc', 'office']
            ],
           'technical_support' => [
                'patterns' => [
                    'technical support' => 1.0,
                    'tech support' => 1.0,
                    'help with' => 0.9,
                    'issue with' => 0.9,
                    'problem with' => 0.8,
                    'reset password' => 1.0,
                    'cannot login' => 0.9,
                    'forgot password' => 0.9,
                    'internet not working' => 1.0,
                    'printer issue' => 1.0,
                    'computer repair' => 1.0,
                    'software installation' => 1.0
                ],
                'keywords' => [
                    'technical', 'tech', 'support', 'help', 'issue', 'problem', 
                    'password', 'reset', 'login', 'internet', 'network', 
                    'printer', 'computer', 'repair', 'software', 'install'
                ]
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
        Log::info('Unknown query received: ' . $message);
    
        $unknownMessage = "I'm not quite sure what you're asking about. Here are some things I can help you with:\n\n" .
            "1. Information about our services\n" .
            "2. Submitting a service request\n" .
            "3. Tracking your request status\n" .
            "4. Technical support\n\n" .
            "Please let me know which one you'd like to learn more about!";
    
        $botman->reply(nl2br($unknownMessage));
        return $unknownMessage;
    }
    
    private function handleGreeting($botman)
    {
        $responses = [
            "Hello! Welcome to the Service Request Management System support. How can I assist you today?",
            "Hi there! How can I help you with your service request?",
            "Greetings! How may I assist you with your inquiry?",
            "Hello! Let me know how I can assist you today!"
        ];
    
        $response = $responses[array_rand($responses)];
        $botman->reply($response);
        return $response;
    }
    
    private function handleServiceInfo($botman)
    {
        $serviceInfo = "Here are the services we offer:\n\n" .
            "1. MS Office 365, MS Teams, TUP Email:\n" .
            "   - Create MS Office/TUP Email Account\n" .
            "   - Reset MS Office/TUP Email Password\n" .
            "   - Change of Data\n\n" .
            "2. Attendance Record:\n" .
            "   - Daily Time Record\n" .
            "   - Biometric Enrollment and Employee ID\n\n" .
            "3. TUP Web ERS, ERS, and TUP Portal:\n" .
            "   - Reset TUP Web Password\n" .
            "   - Reset ERS Password\n" .
            "   - Change of Data\n\n" .
            "4. Internet and Telephone Management:\n" .
            "   - New Internet Connection\n" .
            "   - New Telephone Connection\n" .
            "   - Internet/Telephone Repair and Maintenance\n\n" .
            "5. ICT Equipment Management:\n" .
            "   - Computer Repair and Maintenance\n" .
            "   - Printer Repair and Maintenance\n" .
            "   - Request to use LED Screen\n\n" .
            "6. Software and Website Management:\n" .
            "   - Install Application/Information System/Software\n" .
            "   - Post Publication/Update of Information in Website\n\n" .
            "7. Data, Documents, and Reports Handled by the UITC:\n" .
            "   - Data Handled by the UITC\n" .
            "   - Documents Handled by the UITC\n" .
            "   - Reports Handled by the UITC";
            //"Would you like more details about any specific service?";
        
        $botman->reply(nl2br($serviceInfo));
        return $serviceInfo;
    }    
    
    private function handleRequestService($botman)
    {
        $requestMessage = "To submit a service request, please follow these steps:\n\n" .
            "1. Log into your account.\n" .
            "2. Go to the **'Submit Request'** section.\n" .
            "3. Fill out the service request form with all necessary details.\n" .
            "4. Submit your request.";
       
    
        $botman->reply(nl2br($requestMessage));
        return $requestMessage;
    }
    
    private function handleTrackStatus($botman)
    {
        $statusMessage = "You can track your service request status by:\n\n" .
            "1. Logging into your account.\n" .
            "2. Navigating to the **'My Requests'** section.\n" .
            "3. Searching for your request using the request ID or filter status.";
          //  "Do you need further assistance with tracking your request?"
    
        $botman->reply(nl2br($statusMessage));
        return $statusMessage;
    }

      /**
     * Handle UITC working hours query
     */
    private function handleWorkingHours($botman)
    {
        $workingHoursMessage = "Open Monday to Friday: 8:00 AM - 5:00 PM\n\n" .
            /*"Saturday:\n" .
            "- Limited Service: 8:00 AM - 12:00 PM\n\n" .
            "Sunday and Holidays: Closed\n\n" .*/
            "Note: Service hours may vary during special events or academic breaks.";
        
        $botman->reply(nl2br($workingHoursMessage));
        return $workingHoursMessage;
    }
    
    /**
     * Handle technical support queries
     */
    private function handleTechnicalSupport($botman, $message)
    {
        $message = strtolower($message);
        $supportMessage = "";

        // Password Reset Scenarios
        if (strpos($message, 'reset password') !== false || 
            strpos($message, 'forgot password') !== false || 
            strpos($message, 'cannot login') !== false) {
            $supportMessage = "Password Reset Assistance:\n\n" .
                "For different systems, follow these steps:\n\n" .
                "1. TUP Web ERS Password:\n" .
                "   - Visit https://ers.tup.edu.ph/aims/students/ or https://ers.tup.edu.ph/aims/faculty/\n" .
                "   - Click 'Forgot Password'\n" .
                "   - Enter your necessary details\n\n" .
                "2. MS Office/TUP Email Password:\n" .
                "   - Go to office.com or mail.tup.edu.ph\n" .
                "   - Click 'Can't access your account?'\n" .
                "   - Follow password recovery steps\n\n" .
                "3. If issues persist, submit a service request to UITC.";
        } 
        // Internet Connection Issues
        elseif (strpos($message, 'internet') !== false || 
                strpos($message, 'network') !== false) {
            $supportMessage = "Internet Connection Troubleshooting:\n\n" .
                "1. Basic Troubleshooting:\n" .
                "   - Restart your router/modem\n" .
                "   - Check all cable connections\n" .
                "   - Verify Wi-Fi/Ethernet settings\n\n" .
                "2. Campus Network Specific:\n" .
                "   - Ensure you're on TUP network\n" .
                "   - Check device network settings\n" .
                "   - Verify account is active\n\n" .
                "3. Persistent Issues:\n" .
                "   - Submit a request form with:\n" .
                "     * Device details\n" .
                "     * Location\n" .
                "     * Specific error messages";
        } 
        // Printer Issues
        elseif (strpos($message, 'printer') !== false) {
            $supportMessage = "Printer Troubleshooting Guide:\n\n" .
                "1. Basic Checks:\n" .
                "   - Ensure printer is powered on\n" .
                "   - Check paper and ink/toner levels\n" .
                "   - Verify cable/network connections\n\n" .
                "2. Common Problems:\n" .
                "   - Paper jam\n" .
                "   - Offline status\n" .
                "   - Print quality issues\n\n" .
                "3. Resolution Steps:\n" .
                "   - Restart printer\n" .
                "   - Reinstall printer drivers\n" .
                "   - Check printer queue\n\n" .
                "Need advanced help? Submit a service request.";
        } 
        // Computer Repair
        elseif (strpos($message, 'computer') !== false || 
                strpos($message, 'repair') !== false) {
            $supportMessage = "Computer Repair and Maintenance:\n\n" .
                "1. Diagnostic Checklist:\n" .
                "   - Startup issues\n" .
                "   - Performance problems\n" .
                "   - Hardware malfunctions\n" .
                "   - Software conflicts\n\n" .
                "2. Recommended Actions:\n" .
                "   - Backup important data\n" .
                "   - Run system diagnostics\n" .
                "   - Check for software updates\n\n" .
                "3. UITC Support Process:\n" .
                "   - Submit a request and wait for the approval from UITC \n" .
                "   - Bring device to UITC\n" .
                "   - Provide detailed issue description\n";
        } 
        // Software Installation
        elseif (strpos($message, 'software') !== false || 
                strpos($message, 'install') !== false) {
            $supportMessage = "Software Installation Guidelines:\n\n" .
                "1. Approved Software:\n" .
                "   - MS Office\n" .
                "   - Antivirus\n" .
                "   - Academic/Research Tools\n" .
                "   - Department-specific Software\n\n" .
                "2. Installation Process:\n" .
                "   - Verify software compatibility\n" .
                "   - Check system requirements\n" .
                "   - Use official installation sources\n\n" .
                "3. Request Procedure:\n" .
                "   - Submit software installation request\n" .
                "   - Provide installation details\n" .
                "   - Wait for UITC approval";
        } 
        // Generic Technical Support
        else {
            $supportMessage = "Technical Support Guidance:\n\n" .
                "1. Identify Your Issue:\n" .
                "   - Password problems\n" .
                "   - Network connectivity\n" .
                "   - Printer issues\n" .
                "   - Computer maintenance\n" .
                "   - Software installation\n\n" .
                "2. Prepare Information:\n" .
                "   - Device details\n" .
                "   - Specific error messages\n" .
                "   - Steps you've already tried\n\n" .
                "3. Contact Methods:\n" .
                "   - Submit a request\n" .
                "   - Visit UITC office or send an email to uitc@tup.edu.ph\n";
        }

        $botman->reply(nl2br($supportMessage));
        return $supportMessage;
    }
    

}
