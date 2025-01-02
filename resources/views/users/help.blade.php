<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/help.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Help</title>
</head>
<body>
   
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    
    <div class="help">
        <h1>Help</h1>
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: How do I submit a service request?
                </div>
                <div class="faq-answer">
                    A: Click on the "Submit Request", fill out the form, and click "Submit."
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: How can I track the status of my request?
                </div>
                <div class="faq-answer">
                    A: Use the "My Requests" section to search for your request by ID or filter by status.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: What information do I need to provide when submitting a request?
                </div>
                <div class="faq-answer">
                    A: When submitting a request, you will typically need to provide your contact information, a description of the issue or service needed, and any relevant attachments or documentation.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: How will I be notified about updates on my request?
                </div>
                <div class="faq-answer">
                A: You will receive a notification for any updates or changes to the status of your request. Make sure to check your account for updates.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: How long does it take to process a service request?

                </div>
                <div class="faq-answer">
                A: The processing time for service requests may vary depending on the nature and complexity of the request. You can check the estimated time in the "My Requests" section or refer to the UITC guidelines.</div>
            </div>
        </div>

        <h2 class="contact-support">Contact Support</h2>
        <p>For further assistance, please reach out to our support team:</p>
        <p><i class="fas fa-envelope"></i> Email: <a href="mailto:uitc@tup.edu.ph" id="email-link">uitc@tup.edu.ph</a></p>
        <p><i class="fas fa-phone-alt"></i> Phone: +632-301-3001</p>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @stack('scripts') 
    <script>
        function toggleAnswer(questionElement) {
            const answerElement = questionElement.nextElementSibling;
            if (answerElement.style.display === "block") {
                answerElement.style.display = "none";
            } else {
                answerElement.style.display = "block";
            }
        }

        document.getElementById('email-link').addEventListener('click', function (e) {
            var mailtoLink = this.href;
            var isMailAppOpened = false;
            // This will try to open the mail app for 5 seconds
            setTimeout(function() {
                if (!isMailAppOpened) {
                    alert("Your email app didn't open. Please ensure it is set up on your device.");
                }
            }, 5000); // 5 seconds delay
        });
    </script>

</body>
</html>
