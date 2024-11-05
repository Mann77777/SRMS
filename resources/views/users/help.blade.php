<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/help.css') }}" rel="stylesheet">

    <title>Help</title>
</head>
<body>
    
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-md navbar-light fixed-top">
        <div class="container">
            <div class="navbar-logo">
                <a href="{{ url('/dashboard') }}">
                    <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
                </a>
            </div>
        
            <ul class="navbar-menu d-md-flex" id="navbar-menu">
                <li><a href="{{ url('/notifications') }}" class="notification-icon"><i class="fas fa-bell"></i></a></li>
                <li class="dropdown">
                    <a href="#" class="profile-icon">
                        @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="profile-img-navbar">
                        @else
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img-navbar">
                        @endif
                    </a>
                    <div class="dropdown-content">
                        <a href="{{ url('/myprofile') }}">My Profile</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ url('/student-request') }}" class="{{ request()->is('student-request') ? 'active' : '' }}">Submit Request</a></li>
            <li><a href="{{ url('/myrequests') }}" class="{{ request()->is('myrequests') ? 'active' : '' }}">My Requests</a></li>
            <li><a href="{{ url('/service-history') }}" class="{{ request()->is('service-history') ? 'active' : '' }}">Service History</a></li>
            <li><a href="{{ url('/messages') }}" class="{{ request()->is('messages') ? 'active' : '' }}">Messages</a></li>
            <li><a href="{{ url('/announcement') }}" class="{{ request()->is('announcement') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/help') }}" class="{{ request()->is('help') ? 'active' : '' }}">Help</a></li>
        </ul>
    </div> 
    
    <div class="help">
        <h1>Help</h1>
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleAnswer(this)">
                    Q: How do I submit a service request?
                </div>
                <div class="faq-answer">
                    A: Click on the "Submit Request" button in the main menu, fill out the form, and click "Submit."
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
                A: You will receive a notification any updates or changes to the status of your request. Make sure to check your account for updates.
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
        <p><i class="fas fa-envelope"></i> Email: <a href="mailto:uitc@tup.edu.ph">uitc@tup.edu.ph</a></p>
        <p><i class="fas fa-phone-alt"></i> Phone: +632-301-3001</p>
    </div>

    <script>
        function toggleAnswer(questionElement) {
            const answerElement = questionElement.nextElementSibling;
            if (answerElement.style.display === "block") {
                answerElement.style.display = "none";
            } else {
                answerElement.style.display = "block";
            }
        }
    </script>

</body>
</html>
