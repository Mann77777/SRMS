<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use BotMan\BotMan\Messages\Incoming\Answer;

class BotManController extends Controller
{
    /**
     * Handle the incoming messages from the BotMan chatbot.
     */
    public function handle()
    {
        $botman = app('botman');
        
        // Listen for any message
        $botman->hears('{message}', function($botman, $message) {
            // Convert the message to lowercase to handle case insensitivity
            $message = strtolower($message);
            
            // Greeting
            if ($this->isGreeting($message)) {
                $this->askName($botman);
            }
            // Service information queries
            elseif ($this->containsServiceInfoQuery($message)) {
                $this->serviceInfo($botman);
            }
            elseif ($this->containsRequestServiceQuery($message)) {
                $botman->reply("To request a service, log into our portal and fill out the service request form.");
            }
            elseif ($this->containsSupportTypeQuery($message)) {
                $botman->reply("Yes, you can request both hardware and software support.");
            }
            elseif ($this->containsAssistanceQuery($message)) {
                $botman->reply("We provide remote assistance, but on-site support is not available.");
            }
            elseif ($this->containsSubmissionQuery($message)) {
                $botman->reply("To submit a service request, log into our portal and fill out the service request form.");
            }
            elseif ($this->containsRequestLocationQuery($message)) {
                $botman->reply("You can log a service request by visiting our service request portal. Simply log into your account and submit your request.");
            }
            // Tracking service request queries
            elseif ($this->containsTrackStatusQuery($message)) {
                $botman->reply("To check the status of your service request, go to the 'Service History' or 'My Requests' section in the portal.");
            }
            elseif ($this->containsTrackProgressQuery($message)) {
                $botman->reply("Yes, you can track the progress by navigating to 'My Requests' and filtering or searching by request ID.");
            }
            // Handle common issues
            elseif ($this->containsInternetIssueQuery($message)) {
                $botman->reply("Try restarting your router and checking for loose cables. If the problem persists, submit a service request with the details.");
            }
            elseif ($this->containsPrinterIssueQuery($message)) {
                $botman->reply("To request printer support, log into the portal and fill out the service request form with details of the issue.");
            }
            // Default response
            else {
                $botman->reply("I'm here to help! Please let me know how I can assist you.");
            }
        });

        $botman->listen();
    }


    /**
     * Check if the message is a follow-up question based on the last query.
     */
    private function isFollowUpQuery($message)
    {
        // This could be enhanced by looking for key terms like 'follow up', 'status', etc.
        return strpos($message, 'status') !== false || strpos($message, 'follow up') !== false;
    }

    /**
     * Handle follow-up questions based on the context stored in user storage.
     */
    private function handleFollowUp($botman, $message)
    {
        $userData = $botman->userStorage()->get();
        $lastQuery = $userData['last_query'] ?? null;

        if ($lastQuery == 'request_service') {
            $botman->reply("You can check the status of your service request by going to the 'My Requests' section in the portal.");
        } elseif ($lastQuery == 'track_progress') {
            $botman->reply("For follow-ups on your service request, please ensure you have the request ID handy to track its progress.");
        } else {
            $botman->reply("Could you clarify what you mean by follow-up? I'd be happy to assist you further.");
        }
    }


    /**
     * Check if the message is a greeting.
     */
    private function isGreeting($message)
    {
        return $message == 'hi' || $message == 'hello';
    }

    /**
     * Check if the message contains a service information query.
     */
    private function containsServiceInfoQuery($message)
    {
        $patterns = [
            'what services do you offer',
            'can you tell me about your services',
            'what are the available services',
            'what kind of services do you have',
            'what are the available services'
        ];

        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the message contains a service request query.
     */
    private function containsRequestServiceQuery($message)
    {
        $patterns = [
            'how can i request a service',
            'how do i request a service',
            'where can i request a service',
            'how to submit a service request',
            'what is the process to request a service',
            'how do i ask for a service',
            'where can i log a service request',
            'can i request a service online',
            'is there a way to request a service',
            'how to request',
            'how to request service'

        ];
    
        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    

    /**
     * Check if the message asks about hardware and software support.
     */
    private function containsSupportTypeQuery($message)
    {
        return strpos($message, 'can i request both hardware and software support') !== false;
    }

    /**
     * Check if the message asks about remote or on-site assistance.
     */
    private function containsAssistanceQuery($message)
    {
        $patterns = [
            'do you provide remote assistance or on-site support',
            'do you offer remote assistance or on-site support',
            'can i get remote assistance or on-site support',
            'do you provide both remote and on-site support',
            'is remote assistance available or only on-site support',
            'do you have remote and on-site support options'
        ];
    
        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if the message asks how to submit a service request.
     */
    private function containsSubmissionQuery($message)
    {
        $patterns = [
            'how do i submit a service request',
            'how can i submit a service request',
            'where can i log a service request',
            'what is the process for submitting a service request',
            'can you guide me through submitting a service request',
            'how do i make a request for service',
            'where do i submit my service request',
            'how do i report an issue'
        ];
    
        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the message asks where to log a service request.
     */
    private function containsRequestLocationQuery($message)
    {
        return strpos($message, 'where can i log a service request') !== false;
    }

    /**
     * Check if the message asks about checking the status of a service request.
     */
    private function containsTrackStatusQuery($message)
    {
        return strpos($message, 'how can i check the status of my service request') !== false;
    }

    /**
     * Check if the message asks about tracking the progress of a service request.
     */
    private function containsTrackProgressQuery($message)
    {
        $patterns = [
            'can i track the progress of my service request',
            'how can i track the progress of my service request',
            'where can i check the progress of my request',
            'is there a way to track my service request',
            'how do i know the progress of my service request',
            'can i see updates on my service request',
            'how can i check the status of my request'
        ];
    
        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the message mentions an internet connection issue.
     */
    private function containsInternetIssueQuery($message)
    {
        $patterns = [
            'i’m facing an issue with my internet connection',
            'my internet is not working',
            'i cannot connect to the internet',
            'having trouble with my internet'
        ];

        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the message mentions a printer issue.
     */
    private function containsPrinterIssueQuery($message)
    {
        $patterns = [
            'my printer is not working',
            'printer issue',
            'cannot print'
        ];

        foreach ($patterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ask the user for their name when they say 'hi'.
     */
    public function askName($botman)
    {
        $botman->ask('Hello! What is your name?', function (Answer $answer, $conversation) {
            $userInput = $answer->getText();

            // Check if the user is saying "I'm [name]" or "I am [name]"
            if (preg_match('/^i\'m\s+([a-zA-Z]+)$/i', $userInput, $matches) || preg_match('/^i am\s+([a-zA-Z]+)$/i', $userInput, $matches)) {
                $name = $matches[1];  // Extract name from the matched pattern
            } else {
                $name = $userInput;  // If no pattern matches, use the input as the name
            }

            // Capitalize only the first letter of the name
            $name = ucfirst(strtolower($name));

            $this->say('Nice to meet you, ' . $name);
        });
    }

    /**
     * Provide general service information.
     */
    public function serviceInfo($botman)
    {
        $serviceInfo = "
        UITC offers a range of services, including:

        • MS Office 365, MS Teams, TUP Email
        - Account Creation, Password Reset, Data Change

        • Attendance Record
        - Daily Time and Biometric Records

        • Biometrics Enrollment & ID Card
        - New ID enrollment (with personal info)

        • TUP Web ERES, ERS, TUP Portal
        - Password Reset, Data Change

        • Internet & Telephone Management
        - New Connection, Repairs & Maintenance

        • ICT Equipment Management
        - Computer and Printer Repair, LED Screen Request

        • Software & Website Management
        - Install Software, Publish/Update Website Info

        • Data/Documents Management
        - Handling data/documents managed by UITC

        If you need more details about any service, just let me know!
        ";

        $botman->reply($serviceInfo);
    }
}
