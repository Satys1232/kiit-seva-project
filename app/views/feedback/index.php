<?php
session_start();
require_once dirname(__DIR__, 2) . '/helpers/functions.php';

if (!isLoggedIn()) {
    redirect('/login');
}

$user = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback System - KIIT SEVA</title>
    <link rel="stylesheet" href="../../assets/css/app.css">
    <style>
        .star-rating {
            display: flex;
            gap: 5px;
            font-size: 2rem;
            margin: 20px 0;
        }
        .star {
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.1);
        }
        .feedback-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .feedback-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="navbar-brand">KIIT SEVA</div>
                <div class="d-flex gap-3">
                    <a href="/dashboard" class="nav-link">Dashboard</a>
                    <a href="/logout" class="nav-link">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>💬 Submit Feedback</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($flash): ?>
                            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 15px;">
                                <?php echo htmlspecialchars($flash['message']); ?>
                            </div>
                        <?php endif; ?>
                        <form id="feedbackForm" method="POST" action="/feedback/create">
                            <div class="form-group">
                                <label>Your Rating</label>
                                <div class="star-rating" id="starRating">
                                    <span class="star" data-rating="1">★</span>
                                    <span class="star" data-rating="2">★</span>
                                    <span class="star" data-rating="3">★</span>
                                    <span class="star" data-rating="4">★</span>
                                    <span class="star" data-rating="5">★</span>
                                </div>
                                <input type="hidden" name="rating" id="ratingValue" required>
                            </div>

                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" class="form-control" required>
                                    <option value="">Select category...</option>
                                    <option value="service">Service</option>
                                    <option value="food">Food</option>
                                    <option value="transport">Transport</option>
                                    <option value="faculty">Faculty</option>
                                    <option value="infrastructure">Infrastructure</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" required placeholder="Brief summary of your feedback">
                            </div>

                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" class="form-control" rows="4" required placeholder="Share your detailed feedback..."></textarea>
                                <small class="text-muted">Characters: <span id="charCount">0</span>/500</small>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_anonymous" class="form-check-input">
                                <label class="form-check-label">Submit anonymously</label>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>📋 Recent Feedback</h3>
                    </div>
                    <div class="card-body">
                        <div class="feedback-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="star-rating" style="font-size: 1rem;">
                                    <span style="color: #ffc107;">★★★★★</span>
                                </div>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                            <h5>Excellent Teacher Booking System</h5>
                            <p class="text-muted">The new teacher booking system is very user-friendly and efficient...</p>
                            <small><strong>Category:</strong> Service | <strong>By:</strong> Anonymous</small>
                        </div>

                        <div class="feedback-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="star-rating" style="font-size: 1rem;">
                                    <span style="color: #ffc107;">★★★★☆</span>
                                </div>
                                <small class="text-muted">1 day ago</small>
                            </div>
                            <h5>Vehicle Tracking Needs Improvement</h5>
                            <p class="text-muted">The vehicle tracking feature is helpful but could be more accurate...</p>
                            <small><strong>Category:</strong> Transport | <strong>By:</strong> Student</small>
                        </div>

                        <div class="feedback-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="star-rating" style="font-size: 1rem;">
                                    <span style="color: #ffc107;">★★★★★</span>
                                </div>
                                <small class="text-muted">3 days ago</small>
                            </div>
                            <h5>Great Platform Overall</h5>
                            <p class="text-muted">KIIT SEVA has made university services much more accessible...</p>
                            <small><strong>Category:</strong> Service | <strong>By:</strong> Rahul S.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedRating = 0;

        // Star rating functionality
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                document.getElementById('ratingValue').value = selectedRating;
                
                // Update star display
                document.querySelectorAll('.star').forEach((s, index) => {
                    if (index < selectedRating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });

            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.dataset.rating);
                document.querySelectorAll('.star').forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        document.getElementById('starRating').addEventListener('mouseleave', function() {
            document.querySelectorAll('.star').forEach((s, index) => {
                if (index < selectedRating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });

        // Character counter
        document.querySelector('textarea[name="message"]').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('charCount').textContent = count;
            
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                document.getElementById('charCount').textContent = 500;
            }
        });

        // Form submission
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            if (selectedRating === 0) {
                alert('Please select a rating');
                e.preventDefault();
                return;
            }
            // Allow form to submit to server
        });
    </script>
</body>
</html>