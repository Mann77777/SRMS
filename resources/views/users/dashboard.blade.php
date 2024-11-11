<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
    
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')


    <!-- HERO SECTION -->
    <section class="hero">
        <?php
        date_default_timezone_set('Asia/Manila'); // Set your timezone
        $hour = date('H'); // Get the current hour in 24-hour format

        // Determine the greeting based on the current hour
        if ($hour >= 5 && $hour < 12) {
            $greeting = "Good Morning";
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = "Good Afternoon";
        } else {
            $greeting = "Good Evening";
        }
        ?>

        <h1><?php echo $greeting . ", " . Auth::user()->username . "!"; ?></h1>

        <!-- Role-based Buttons -->
        <div class="button-container">
            @if(Auth::user()->role === 'Student')
                <button onclick="window.location.href='/student-request'" class="btn-primary">Request Student Service</button>
                <button onclick="window.location.href='/student-status'" class="btn-secondary">Check Status</button>
            @elseif(Auth::user()->role === 'Faculty & Staff')
                <button onclick="window.location.href='/faculty-service'" class="btn-primary">Request Faculty & Staff Services</button>
                <button onclick="window.location.href='/faculty-status'" class="btn-secondary">Check Status</button>
            @endif
        </div>
    </section>

    <section class="container2 my-4">
        <div class="row">
            <!-- Total Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Requests</h5>
                        <p class="card-text h1">{{ $totalRequests ?? 0 }}</p>
                        <p>View</p>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pending Requests</h5>
                        <p class="card-text h1">{{ $pendingRequests ?? 0 }}</p>
                        <p>View</p>
                    </div>
                </div>
            </div>

            <!-- Completed Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completed Requests</h5>
                        <p class="card-text h1">{{ $completedRequests ?? 0 }}</p>
                        <p>View</p>
                    </div>
                </div>
            </div>  

            
    </section>


    <!-- Recent Requests Section -->
    <section class="requests my-4">
        <h2 class="mb-3">Recent Requests</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Request ID</th>
                    <th>Date Submitted</th>
                    <th>Time Submitted</th>
                    <th>Service</th>
                    <th>Status</th>
                </tr>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>2024-11-01</td>
                        <td>09:15 AM</td>
                        <td>Library Access</td>
                        <td>Pending</td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td>2024-11-02</td>
                        <td>10:30 AM</td>
                        <td>IT Support</td>
                        <td>In Progress</td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td>2024-11-03</td>
                        <td>01:45 PM</td>
                        <td>Counseling Session</td>
                        <td>Completed</td>
                    </tr>
            </tbody>
            </thead>
      
        </table>
    </section>
    <button class="btn btn-primary" onclick="window.location.href='{{ url('/submit-request') }}'">Submit Request</button>


    
     <!-- Floating Chatbot Button -->
     <button class="chatbot-button" onclick="toggleChat()">💬</button>
    <!-- Chat Window -->
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">Chatbot</div>
            <div class="chat-body" id="chatBody">
                <p>Hello! How can I assist you today?</p>
            </div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type your message..." />
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            chatWindow.style.display = chatWindow.style.display === 'block' ? 'none' : 'block';
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value;
            if (message.trim() !== '') {
                const chatBody = document.getElementById('chatBody');
                chatBody.innerHTML += '<p>You: ' + message + '</p>';
                input.value = '';
                // Simulate a bot response
                setTimeout(() => {
                    chatBody.innerHTML += '<p>Bot: Thank you for your message!</p>';
                    chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
                }, 1000);
            }
        }
    </script>
</body>
</html>
