<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/services.css') }}" rel="stylesheet">
    <title>Services</title>
</head>
<body>
    
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-md fixed-top">
        <div class="container">
            <div class="navbar-logo">
                <a href="{{ url('/dashboard') }}">
                    <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
                </a>
            </div>
        
            <ul class="navbar-menu d-md-flex" id="navbar-menu">
                <li><a href="{{ url('/dashboard') }}">Home</a></li>
                <li><a href="{{ url('/aboutus') }}">About Us</a></li>
                <li><a href="{{ url('/services') }}">Services</a></li>
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

    <section class="services-overview">
        <h2 class="section-title text-center">Our Services</h2>
        <div class="container"> 
            <div class="row">
                <div class="col-md-4">
                    <div class="service-card">
                        <h3>MS OFFICE, MS TEAMS, TUP EMAIL</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 1" class="service-image">
                        <p>We assist with creating accounts, resetting passwords, and updating data to help you collaborate effectively.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>ATTENDANCE RECORD</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 2" class="service-image">
                        <p>Our services include daily time tracking and biometric systems to ensure accurate attendance.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>BIOMETRIC ENROLLMENT</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 3" class="service-image">
                        <p>We facilitate biometrics enrollment and the creation of employee ID cards.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>TUP WEB ERS, ERS, AND TUP PORTAL</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 4" class="service-image">
                        <p> We simplify access to educational resources by helping with password resets and data updates, ensuring your information is always current</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>INTERNET AND TELEPHONE MANAGEMENT</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 5" class="service-image">
                        <p>We provide new internet and telephone connections and offer reliable repair and maintenance services to keep you connected.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>ICT EQUIPMENT MANAGEMENT</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 6" class="service-image">
                        <p> Our team specializes in computer and printer repairs, along with support for LED screens, ensuring your technology runs smoothly.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>SOFTWARE AND WEBSITE MANAGEMENT</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 7" class="service-image">
                        <p>We help install applications and keep your website updated with fresh content to engage users effectively.</p>
                    </div>
                </div>  

                  <div class="col-md-4">
                    <div class="service-card">
                        <h3>DATA, DOCUMENTS AND REPORTS</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 7" class="service-image">
                        <p>We organize and secure your data and documents, giving you easy access to accurate reports that meet your operational needs.</p>
                    </div>
                </div>  

                <div class="col-md-4">
                    <div class="service-card">
                        <h3>SOFTWARE AND WEBSITE MANAGEMENT</h3>
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Service 7" class="service-image">
                        <p>We help install applications and keep your website updated with fresh content to engage users effectively.</p>
                    </div>
                </div>  

                        
            </div>
        </div>
        <button onclick="window.location.href='/service-request'" class="requestbtn">Request Service</button>
</section>



    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <!-- First Row: Logo -->
            <div class="footer-row logo-row">
                <div class="footer-logo">
                    <a href="">
                        <img src="{{ asset('images/tuplogo.png') }}" alt="Logo"  class="footer-logo-img">
                    </a>

                </div>
            </div>

        <!-- Second Row: Contact Information -->
            <div class="footer-row contact-row">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <a href="https://www.google.com/maps/dir//San+Marcelino+St,+Ayala+Blvd,+Ermita,+Manila,+1000/@14.5870646,120.902085,12z/data=!4m8!4m7!1m0!1m5!1m1!1s0x3397ca21ac302015:0x92dcca0f915010d9!2m2!1d120.9844868!2d14.587079?entry=ttu&g_ep=EgoyMDI0MTAyMC4xIKXMDSoASAFQAw%3D%3D" target="_blank" class="text-white">
                        Ayala Blvd., Ermita, Manila, 1000, Philippines
                    </a>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>uitc@tup.edu.ph</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-globe"></i>
                    <a href="https://www.tup.edu.ph/" target="_blank" class="text-white">
                        www.tup.edu.ph
                    </a>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+632-301-3001</span>
                </div>
            </div>

            <!-- Third Row: Links -->
            <div class="footer-row links-row">
                <div class="links-item">
                    <a href="">SRMS</a>
                </div>
                <div class="links-item">
                    <a href="/aboutus">About Us</a>
                </div>
                <div class="links-item">
                    <a href="/services">Services</a>
                </div>
                <div class="links-item">
                    <a href="/terms">Terms and Conditions</a>
                </div>
            </div>
        </div>
        <div class="footer-row social-media-row">
            <div class="social-media">
                <a href="https://www.facebook.com/TUPian" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/TUPManila" target="_blank">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
            <div class="copyright">
                <p>&copy; 2024 SRMS.</p>
            </div>
        </div>
    </footer>
  
</body>
</html>
