<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Holly, Jolly and Hallmark</title>
    <link rel="icon" type="image/png" href="public/img/logo.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/style.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
</head>
<body>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="public/img/logo.png" alt="Podcast Logo">
            </div>

            <p>
                Every other week, we’ll unwrap the magic of Hallmark together — chatting about new releases, favorite actors, behind-the-scenes trivia, and why these movies make us feel like Christmas lasts all year long.
            </p>

            <div class="features">
                <div class="feature-item">
                    <span><img src="public/img/mic.png" alt="Mic Icon" style="width: 18px;"></span> Hosted by women who love Hallmark as much as you do
                </div>
                <div class="feature-item">
                    <span><img src="public/img/wine.png" alt="Wine Glass Icon" style="width: 18px;"></span> Bi-weekly show full of cozy conversation
                </div>
                <div class="feature-item">
                    <span><img src="public/img/star_twing.png" alt="star" style="width: 18px;"></span> Featuring superfans, fun debates and heartwarming stories
                </div>
            </div>
        </div>

        <h3>Tell us a little about yourself...</h3>

        <form method="POST" action="server/submitForm.php" id="submitForm" autocomplete="off" novalidate>
            <div class="question-group">
                <p style="margin-bottom: 15px; color: hsl(1.07deg 68.29% 48.24%);"><b>Which Hallmark plotline describes your love life?</b></p>
                <label><input type="checkbox" name="plotline[]" value="amnesia"> Amnesia in a small town</label>
                <label><input type="checkbox" name="plotline[]" value="prince"> Falling for a prince</label>
                <label><input type="checkbox" name="plotline[]" value="bakery"> Saving the family bakery</label>
                <label><input type="checkbox" name="plotline[]" value="big_city_home"> Big city girl comes home for Christmas</label>
                <span class="error-text" id="error-plotline"></span>
            </div>

            <div class="question-group">
                <p style="margin-bottom: 15px; color: hsl(1.07deg 68.29% 48.24%);"><b>Who is your favorite Hallmark star?</b></p>
                <input type="text" name="favorite_star" id="favorite_star" placeholder="">
                <span class="error-text" id="error-favorite_star"></span>
            </div>

            <div class="question-group">
                <p style="margin-bottom: 15px; color: hsl(1.07deg 68.29% 48.24%);"><b>Would you be interested in participating in a podcast?</b></p>
                <label><input type="radio" name="participate" value="yes"> OMG YES - I can talk Hallmark all day!</label>
                <label><input type="radio" name="participate" value="no"> No, but I can't wait to tune in</label>
                <span class="error-text" id="error-participate"></span>
            </div>

            <div class="form-section">
                <h3>Join the Holly, Jolly & Hallmark Cozy Crew!</h3>
                <p>Sign up for early access, sneak peeks, and giveaways.</p>
                <div style="margin-bottom: 30px;">
                    <input type="text" name="name" id="name" placeholder="Your name...">
                    <span class="error-text" id="error-name"></span>
                </div>

                <div style="margin-bottom: 30px;">
                    <input type="email" name="email" id="email" placeholder="Enter your email...">
                    <span class="error-text" id="error-email"></span>
                </div>

                <div style="margin-bottom: 30px;">
                    <textarea name="message" id="message" placeholder="Include a personal message (Optional)"></textarea>
                    <span class="error-text" id="error-message"></span>
                </div>

                <button type="submit" class="submit-button" id="submitBtn">
                    &nbsp;&nbsp;
                    <span class="btn-text">Submit</span>
                    &nbsp;&nbsp;
                    <span class="btn-loader" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Custom JS -->
    <script src="public/js/script.js"></script>
</body>
</html>