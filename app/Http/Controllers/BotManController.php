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
                    case 'request_volume':
                        $botResponse = $this->handleRequestVolume($botman);
                        break;
                    case 'request_form_info':
                        $botResponse = $this->handleRequestFormInfo($botman, $message);
                        break;
                    case 'satisfaction_info':
                        $botResponse = $this->handleSatisfactionInfo($botman);
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
     * Perform fuzzy matching between two strings
     * 
     * @param string $pattern The pattern to search for
     * @param string $text The text to search in
     * @param float $threshold Minimum similarity threshold (0.0-1.0)
     * @return float Similarity score (0.0-1.0)
     */
    private function fuzzyMatch($pattern, $text, $threshold = 0.7) 
    {
        // Convert to lowercase for case-insensitive matching
        $pattern = strtolower($pattern);
        $text = strtolower($text);
        
        // Exact substring match gets highest score
        if (strpos($text, $pattern) !== false) {
            return 1.0;
        }
        
        // Check for whole word matches
        $textWords = explode(' ', $text);
        $patternWords = explode(' ', $pattern);
        
        // For single word patterns, check similarity with individual words
        if (count($patternWords) == 1) {
            foreach ($textWords as $word) {
                similar_text($pattern, $word, $percent);
                $score = $percent / 100;
                if ($score >= $threshold) {
                    return $score;
                }
            }
        }
        
        // For multi-word patterns, check phrase similarity
        similar_text($pattern, $text, $percent);
        return $percent / 100;
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
            'request_volume' => [
                'patterns' => [
                    'how many requests' => 1.0,
                    'request volume' => 1.0,
                    'daily requests' => 0.9,
                    'number of requests' => 0.9,
                    'request count' => 0.8,
                    'request statistics' => 0.8,
                    'how many service requests' => 1.0,
                    'request total' => 0.9,
                    'average requests' => 0.9,
                    'requests per day' => 0.9,
                    'daily ticket count' => 0.8,
                    'request metrics' => 0.8,
                    'service request volume' => 1.0,
                    'how many tickets' => 1.0,
                    'how many requests do you get' => 1.0,
                    'how many requests do you receive' => 1.0,
                    'daily volume' => 0.9,
                    'ticket volume' => 0.9,
                    'service volume' => 0.9,
                    'busy are you' => 0.8,
                    'workload' => 0.8,
                    'how busy is uitc' => 0.9,
                    'how many people ask for help' => 0.9,
                    'request trends' => 0.8,
                    'uitc requests' => 0.9
                ],
                'keywords' => ['requests', 'volume', 'many', 'daily', 'count', 'statistics', 'number', 
                    'total', 'average', 'metrics', 'tickets', 'busy', 'workload', 
                    'frequency', 'service', 'help', 'support']
            ],
            'request_form_info' => [
                'patterns' => [
                    'find service request form' => 1.0,
                    'where is the service request form' => 1.0,
                    'where can i find the service request form' => 1.0,
                    'how to access the request form' => 0.9,
                    'where do i submit a request' => 0.9,
                    'difference between student and faculty' => 1.0,
                    'student versus faculty requests' => 1.0,
                    'whats the difference between student and faculty' => 1.0,
                    'how long does it take' => 0.9,
                    'process time' => 0.9,
                    'processing time' => 0.9,
                    'request approval' => 0.9,
                    'know if request approved' => 1.0,
                    'how will i know if approved' => 1.0,
                    'cancel request' => 1.0,
                    'cancel my submission' => 0.9,
                    'can i cancel my request' => 1.0,
                    'required documents' => 0.9,
                    'supporting documents' => 0.9,
                    'do i need to provide documents' => 1.0,
                    'documents needed' => 0.9,
                    'who to contact' => 0.9,
                    'contact for questions' => 0.9,
                    'who should i contact' => 1.0,
                    'request limit' => 1.0,
                    'maximum requests' => 0.9,
                    'limit on requests' => 0.9,
                    'how many requests can i submit' => 1.0,
                    'is there a limit' => 0.8,
                    'processed during weekends' => 1.0,
                    'processed on holidays' => 1.0,
                    'will my request be processed during weekend' => 1.0,
                    'request processing on weekends' => 0.9,
                    'what happens after submit' => 1.0,
                    'what happens next' => 0.9,
                    'after submitting request' => 1.0,
                    'who approves' => 1.0,
                    'who approves requests' => 1.0,
                    'approval process' => 0.9,
                    'in progress status' => 1.0,
                    'what does in progress mean' => 1.0,
                    'status in progress' => 1.0,
                    'need to visit office' => 1.0,
                    'visit uitc office' => 1.0,
                    'do i need to go to uitc' => 1.0,
                    'how are requests prioritized' => 1.0,
                    'request priority' => 1.0,
                    'prioritization' => 0.9,
                    'can i update my request' => 1.0,
                    'edit my request' => 1.0,
                    'change request details' => 1.0,
                    'modify submitted request' => 1.0
                ],
                'keywords' => [
                    'form', 'request', 'find', 'where', 'access', 'submit', 'difference', 
                    'student', 'faculty', 'versus', 'time', 'long', 'process', 'approval', 
                    'approved', 'know', 'status', 'cancel', 'delete', 'remove', 'document', 
                    'supporting', 'provide', 'needed', 'contact', 'question', 'help', 
                    'limit', 'maximum', 'many',
                    'weekend', 'holiday', 'processed', 'after', 'submit', 'happens', 'next', 
                    'approves', 'approval', 'progress', 'mean', 'visit', 'office', 'go', 
                    'prioritized', 'priority', 'update', 'edit', 'change', 'modify'
                ]
            ],

            'satisfaction_info' => [
                'patterns' => [
                    'customer satisfaction' => 1.0,
                    'user feedback' => 1.0,
                    'satisfaction survey' => 1.0,
                    'rate my experience' => 1.0,
                    'give feedback' => 1.0,
                    'rate the service' => 1.0,
                    'provide feedback' => 0.9,
                    'submit feedback' => 0.9,
                    'service rating' => 0.9,
                    'evaluate service' => 0.9,
                    'review my request' => 0.9,
                    'how can i rate' => 1.0,
                    'fill out survey' => 0.9,
                    'satisfaction form' => 1.0,
                    'service review' => 0.9,
                    'how was my experience' => 0.9,
                    'leave a review' => 0.9,
                    'feedback form' => 1.0
                ],
                'keywords' => [
                    'satisfaction', 'feedback', 'survey', 'rate', 'rating', 'review',
                    'evaluate', 'experience', 'form', 'questionnaire', 'opinion',
                    'comment', 'assessment', 'service quality', 'performance'
                ]
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
            // Check patterns with fuzzy matching
            foreach ($data['patterns'] as $pattern => $confidence) {
                $matchScore = $this->fuzzyMatch($pattern, $message);
                
                // Only consider matches above threshold
                if ($matchScore >= 0.7) {
                    // Adjust confidence based on match quality
                    $adjustedConfidence = $confidence * $matchScore;
                    
                    if ($adjustedConfidence > $bestMatch['confidence']) {
                        $bestMatch = ['type' => $type, 'confidence' => $adjustedConfidence];
                    }
                }
            }

            // Use keyword matching as a fallback if no good pattern match
            if ($bestMatch['confidence'] < $this->confidenceThreshold) {
                $keywordMatches = 0;
                $totalKeywords = count($data['keywords']);
                foreach ($data['keywords'] as $keyword) {
                    if (strpos($message, $keyword) !== false) {
                        $keywordMatches++;
                    }
                }
                if ($totalKeywords > 0) {
                    $confidence = $keywordMatches / $totalKeywords;
                    if ($confidence > $bestMatch['confidence']) {
                        $bestMatch = ['type' => $type, 'confidence' => $confidence];
                    }
                }
            }
        }

        // Special fallback for request volume queries that might not match patterns
        if ($bestMatch['type'] === 'unknown' && $bestMatch['confidence'] < $this->confidenceThreshold) {
            if ((strpos($message, 'many') !== false || strpos($message, 'much') !== false) && 
                (strpos($message, 'request') !== false || strpos($message, 'ticket') !== false || 
                 strpos($message, 'get') !== false || strpos($message, 'receive') !== false)) {
                $bestMatch = ['type' => 'request_volume', 'confidence' => 0.7];
            }
        }

        return $bestMatch;
    }


        /**
     * Handle questions about customer satisfaction and feedback
     */
    private function handleSatisfactionInfo($botman)
    {
        $satisfactionMessage = "Customer Satisfaction:\n\n" .
            "   - Automatic customer satisfaction will be available in Request History for completed request\n\n" .
            "How to Submit Customer Satisfaction:\n" .
            "   - For completed requests: Go to 'Request History' > Find your completed request > Click 'Rate Service'\n" .
            "   - Rate your experience on a scale from 1-5\n" .
            "   - Add specific comments about what went well or needs improvement\n\n" .
         
            "Why Your Feedback Matters:\n" .
            "   - Helps us identify areas for improvement\n" .
            "   - Recognizes staff who provide exceptional service\n" .
            "   - Directly informs training and process improvements\n";

        $botman->reply(nl2br($satisfactionMessage));
        return $satisfactionMessage;
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
            "4. Technical support\n" .
            "5. Request volume statistics\n\n" .
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
    
        $botman->reply(nl2br($statusMessage));
        return $statusMessage;
    }

    /**
     * Handle UITC working hours query
     */
    private function handleWorkingHours($botman)
    {
        $workingHoursMessage = "Open Monday to Friday: 8:00 AM - 5:00 PM\n\n" .
            "Note: Service hours may vary during special events or academic breaks.";
        
        $botman->reply(nl2br($workingHoursMessage));
        return $workingHoursMessage;
    }

    private function handleRequestVolume($botman)
    {
        // Get today's date in Y-m-d format
        $today = now()->format('Y-m-d');
        
        // Get count of student requests submitted today
        $studentRequestsToday = \App\Models\StudentServiceRequest::whereDate('created_at', $today)->count();
        
        // Get count of faculty requests submitted today
        $facultyRequestsToday = \App\Models\FacultyServiceRequest::whereDate('created_at', $today)->count();
        
        // Calculate total
        $totalRequestsToday = $studentRequestsToday + $facultyRequestsToday;
        
        // Get average requests per day over the last 30 days
        $thirtyDaysAgo = now()->subDays(30)->format('Y-m-d');
        
        $studentRequestsLast30Days = \App\Models\StudentServiceRequest::whereDate('created_at', '>=', $thirtyDaysAgo)->count();
        $facultyRequestsLast30Days = \App\Models\FacultyServiceRequest::whereDate('created_at', '>=', $thirtyDaysAgo)->count();
        
        $totalRequestsLast30Days = $studentRequestsLast30Days + $facultyRequestsLast30Days;
        $averageRequestsPerDay = round($totalRequestsLast30Days / 30, 1);
        
        $responseMessage = "Request Volume Information:\n\n" .
            "Today's Requests: $totalRequestsToday\n" .
            "• Student Requests: $studentRequestsToday\n" .
            "• Faculty Requests: $facultyRequestsToday\n\n" .
            "Average Requests Per Day: $averageRequestsPerDay\n" .
            "(based on the last 30 days)";
        
        $botman->reply(nl2br($responseMessage));
        return $responseMessage;
    }

        /**
     * Handle questions about the service request form and process
     */
    private function handleRequestFormInfo($botman, $message)
    {
        $message = strtolower($message);
        $infoMessage = "";

        // Question about finding the form
        if (strpos($message, 'find') !== false || 
            strpos($message, 'where') !== false || 
            strpos($message, 'access') !== false) {
            $infoMessage = "To access the request form\n" .
                "   - Log into your account\n" .
                "   - Navigate to Submit Request page to access the request form\n" .
                "You must be logged in to access the service request forms.";
        }
        // Difference between student and faculty requests
        elseif (strpos($message, 'difference') !== false || 
                (strpos($message, 'student') !== false && strpos($message, 'faculty') !== false)) {
            $infoMessage = "Differences Between Student and Faculty Service Requests:\n\n" .
                "1. Available Services:\n" .
                "   - Students: Limited to academic-related services like account resets, email services, and some IT equipment\n" .
                "   - Faculty & Staff: Access to additional services like DTR, biometric enrollment, telephone connections, etc.\n\n" .
                "2. Form Fields:\n" .
                "   - Students need to provide student ID and course information\n" .
                "   - Faculty need to provide department and position information\n\n" .
                "   - Different requests may be prioritized differently based on academic needs versus administrative needs";
        }
        // Processing time
        elseif (strpos($message, 'time') !== false || 
                strpos($message, 'long') !== false || 
                strpos($message, 'process') !== false) {
            $infoMessage = "Service Request Processing Time:\n" .
                "   - Simple Transaction: up to 3 business days\n" .
                "   - Technical Transaction: up to 7 business days\n" .
                "   - Highly Technical Transaction: up to 20 business days\n\n" .
                "Factors Affecting Processing Time:\n" .
                "   - Current request volume\n" .
                "   - Complexity of the request\n" .
                "   - Availability of resources/parts\n\n" .
                "Note: Requests submitted during weekends or holidays will be processed on the next business day.";
        }
        // Request approval notification
        elseif (strpos($message, 'approved') !== false || 
                strpos($message, 'know') !== false || 
                strpos($message, 'approval') !== false) {
            $infoMessage = "Tracking Request Approval Status:\n\n" .
                "1. Email Notifications:\n" .
                "   - You will receive an email notifications when your request status changes to In Progress \n" .
                "2. Real-time Notification:\n" .
                "   - Log into your account\n" .
                "   - Click the 'Notification' icon to see to be notified in the Approved requests\n\n" .
                "Detailed Status Types:\n" .
                "   - Pending: Request submitted but not yet reviewed\n" .
                "   - In Progress: Request approved and being worked on\n" .
                "   - Completed: Request has been fulfilled\n" .
                "   - Rejected: Request cannot be fulfilled (with reason provided)" .
                "   - Cancelled: Request automatically cancelled (with reason provided)";
        }
        // Cancelling requests
        elseif (strpos($message, 'cancel') !== false || 
                strpos($message, 'delete') !== false || 
                strpos($message, 'remove') !== false) {
            $infoMessage = "Cancelling Service Requests:\n\n" .
                "1. To cancel a pending request:\n" .
                "   - Go to 'My Requests'\n" .
                "   - Navigate to the request you want to cancel\n" .
                "   - Click the 'Cancel' button (only available for Pending status requests)\n\n" .
                "2. Limitations:\n" .
                "   - You cannot cancel requests that are already In Progress, Completed, or Rejected\n" .
                "   - For urgent cancellations of In Progress requests, contact UITC directly\n\n" .
                "3. After cancellation:\n" .
                "   - The request will be marked as 'Cancelled'\n" .
                "   - You may submit a new request if needed";
        }
        // Required documents
        elseif (strpos($message, 'document') !== false || 
                strpos($message, 'provide') !== false) {
            $infoMessage = "Required Documents for Service Requests:\n\n" .
                "Common documents needed:\n" .
                "   - For account changes: Supporting identification documents\n" .
                "   - For data changes: Documentation verifying the new information\n\n" .
                "   - Change of personal information: Valid ID, supporting documents\n" .
                "   - Special software installation: License information or approval from department\n\n" .
                "The service request form will indicate when a document upload is required.";
        }
        // Contact information
        elseif (strpos($message, 'contact') !== false || 
                strpos($message, 'question') !== false || 
                strpos($message, 'help') !== false) {
            $infoMessage = "Contact Information for Request Assistance:\n\n" .
                "1. Primary Contact:\n" .
                "   - Email: uitc@tup.edu.ph\n" .
                "   - Phone: (Insert UITC phone number here)\n\n" .
                "2. Visit in Person:\n" .
                "   - UITC Office Hours: Monday to Friday, 8:00 AM - 5:00 PM\n" .
                "   - Location: (Insert office location here)\n\n";
        }
        // Request limits
        elseif (strpos($message, 'limit') !== false || 
                strpos($message, 'maximum') !== false || 
                strpos($message, 'many') !== false) {
            $infoMessage = "Service Request Limits:\n\n" .
                "1. Number of requests:\n" .
                "   - There is no strict limit on the number of service requests you can submit\n" .
                "   - However, similar requests may be consolidated\n\n" .
                "2. Active requests:\n" .
                "   - For the same service type, it's recommended to wait until an existing request is completed\n" .
                "   - Multiple identical requests may be declined\n\n" .
                "3. Special limitations:\n" .
                "   - For resource-intensive services (like LED screen usage), scheduling limitations may apply\n" .
                "   - During peak periods, non-critical requests may have longer processing times";
        }

        // Weekend/holiday processing
        elseif (strpos($message, 'weekend') !== false || 
        strpos($message, 'holiday') !== false) {
        $infoMessage = "Weekend & Holiday Request Processing:\n\n" .
        "1. Submission Handling:\n" .
        "   - You can submit requests 24/7 including weekends and holidays\n" .
        "   - However, requests are only processed during regular working hours (Monday-Friday, 8AM-5PM)\n\n" .
        "2. What to Expect:\n" .
        "   - Requests submitted outside working hours will be automatically queued\n" .
        "   - Processing will begin on the next business day\n" .
        "   - You'll receive an email notification when your request status changes\n\n";
        }
        // What happens after submission
        elseif (strpos($message, 'happens') !== false || 
        strpos($message, 'after') !== false || 
        strpos($message, 'next') !== false) {
        $infoMessage = "Service Request Process Flow:\n\n" .
        "1. After Submission:\n" .
        "   - Your request receives a unique ID (e.g., SSR-20240419-0001)\n" .
        "   - Status is set to 'Pending'\n" .
        "   - You receive a confirmation email with request details\n\n" .
        "2. Review & Assignment:\n" .
        "   - Admin will reviews your request\n" .
        "   - Request is approved or rejected based on validity and completeness\n" .
        "   - Approved requests are assigned to appropriate UITC staff\n\n" .
        "3. Processing & Completion:\n" .
        "   - Status changes to 'In Progress' when request is assigned\n" .
        "   - UITC staff may contact you for additional information if needed\n" .
        "   - Once service is completed, status changes to 'Completed'\n" .
        "   - You'll receive notifications at each stage of the process";
        } 
        // Who approves requests
        elseif (strpos($message, 'who approves') !== false || 
        strpos($message, 'approval') !== false) {
        $infoMessage = "Service Request Approval Process:\n\n" .
        "   - The Admin are the one who approve the request\n";
        }
        // In Progress status meaning
        elseif (strpos($message, 'progress') !== false || 
        strpos($message, 'status') !== false) {
        $infoMessage = "Understanding 'In Progress' Status:\n\n" .
        "   - Your request has been approved\n" .
        "   - It has been assigned to a UITC staff\n" .
        "   - Work on your request has actively begun\n" .
        "   - Once the service is completed, status will change to 'Completed'\n" .
        "   - You'll receive a notification when the status changes\n";
        }
        // Need to visit UITC office
        elseif (strpos($message, 'visit') !== false || 
        strpos($message, 'office') !== false || 
        strpos($message, 'go to') !== false) {
        $infoMessage = "Visiting the UITC Office:\n\n" .
        "When a Visit Is Required:\n" .
        "   - Hardware/equipment repairs (you'll need to bring the device)\n" .
        "   - Biometric enrollment and ID processing\n" .
        "   - Software installation on non-networked devices\n" .
        "   - Some password reset scenarios requiring in-person verification\n\n" .
        "   - If a visit is necessary, you'll be notified through email\n" .
        "   - Bring your ID and request confirmation\n" .
        "   - UITC Office Hours: Monday-Friday, 8AM-5PM";
        }

        // Updating submitted requests
        elseif (strpos($message, 'update') !== false || 
        strpos($message, 'edit') !== false || 
        strpos($message, 'change') !== false || 
        strpos($message, 'modify') !== false) {
        $infoMessage = "Updating Submitted Requests:\n\n" .
        "1. For Pending Requests:\n" .
        "   - You can update the submitted request\n" .
        "   - However, it is only applicable to Pending status\n\n" .
        "2. For In Progress Requests:\n" .
        "   - Direct modifications are not possible through the system\n" .
        "   - Send an email to uitc@tup.edu.ph with your request ID and needed changes\n\n" .
        "3. Important Notes:\n" .
        "   - Major changes may require cancellation and resubmission\n" .
        "   - Completed requests cannot be modified";
        }
        // Generic response for other questions about forms/requests
        else {
            $infoMessage = "Service Request Information:\n\n" .
                "1. Access Forms:\n" .
                "   - Student forms: Dashboard > Submit Request\n" .
                "   - Faculty forms: Dashboard > Submit Request\n\n" .
                "2. Request Process:\n" .
                "   - Submit form > Review request by Admin > Approval/Rejection > Assigned UITC Staff > In Progress > Completion\n" .
                "   - Track status in 'My Requests'\n" .
                "   - Email notifications sent for status changes\n\n" .
                "3. Contact for Questions:\n" .
                "   - Email: uitc@tup.edu.ph\n" .
                "   - Visit UITC office during working hours (Mon-Fri, 8AM-5PM)\n\n" .
                "For more specific information, please ask a detailed question.";
        }

        $botman->reply(nl2br($infoMessage));
        return $infoMessage;
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


    /**
     * API method to retrieve chat history for the chatbot widget
     */
    public function getChatHistoryForWidget()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([], 200); // Return empty array for guests
        }

        // Retrieve chat history for current user
        $chatHistory = $this->getChatHistory();

        // Format for the widget
        $formattedHistory = $chatHistory->map(function($chat) {
            return [
                'message' => $chat->message,
                'sender' => $chat->sender,
                'timestamp' => $chat->created_at->toIso8601String()
            ];
        });

        // Return as JSON response
        return response()->json($formattedHistory);
    }
}