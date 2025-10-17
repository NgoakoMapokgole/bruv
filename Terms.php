<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All! | Terms and Conditions </title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="mainStyle.css"/>
    
    <style>
        body {
            font-family: 'Lexend', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        main {
            max-width: 900px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1, h2, h3 {
            color: #222;
        }
        h1 {
            text-align: center;
            margin-bottom: 1rem;
        }
        h2 {
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #444;
        }
        p {
            margin-bottom: 1rem;
        }
        ul{all:unset;}
        ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        li {
            margin-bottom: 0.5rem;
        }
        a {
            color: #0077cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .contact-info p {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
        <header>
        <?php include "nav.php"; ?>

    </header>
    <main class ="terms">
        <h1>Terms and Conditions</h1>
        <p><strong>Effective Date:</strong> 08 October 2025</p>

        <p>Welcome to <strong>Rate It All!</strong>, a platform where users can rate, review, and discuss various topics, products, media, and more. By accessing or using our website, you agree to these Terms and Conditions. Please read them carefully.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By using our website, you agree to comply with these Terms and Conditions and any other policies referenced herein. If you do not agree, you must not use the site.</p>

        <h2>2. Eligibility</h2>
        <p>You must be at least 16 years old to use this site. By registering, you confirm that you meet the age requirement and have the legal capacity to enter into these Terms.</p>

        <h2>3. Account Registration</h2>
        <ul>
            <li>Users may be required to create an account to access certain features.</li>
            <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
            <li>You agree to provide accurate and up-to-date information.</li>
            <li>You are responsible for all activities that occur under your account.</li>
        </ul>

        <h2>4. User Content</h2>
        <ul>
            <li>Users can post reviews, ratings, comments, and media (“User Content”).</li>
            <li>You retain ownership of your User Content but grant us a worldwide, royalty-free license to display, modify, and distribute it on the platform.</li>
            <li>You are solely responsible for your User Content and must not post content that is unlawful, offensive, defamatory, or infringes on others’ rights.</li>
            <li>We may remove content that violates these Terms or is otherwise objectionable, at our discretion.</li>
        </ul>

        <h2>5. Likes, Dislikes, and Interactions</h2>
        <p>Users can like or dislike posts or comments. These interactions are recorded and may be displayed publicly. Misuse or manipulation of likes/dislikes is prohibited.</p>

        <h2>6. Intellectual Property</h2>
        <ul>
            <li>All website content, graphics, text, logos, and software are the property of Rate It All! or its licensors.</li>
            <li>You may not copy, reproduce, distribute, or create derivative works without permission.</li>
            <li>User Content remains yours, but by posting, you grant us permission to use it.</li>
        </ul>

        <h2>7. Prohibited Conduct</h2>
        <ul>
            <li>Use the site for unlawful purposes.</li>
            <li>Harass, abuse, or harm other users.</li>
            <li>Post spam, malware, or malicious links.</li>
            <li>Impersonate other users or falsely represent yourself.</li>
        </ul>

        <h2>8. Reports and Flagging</h2>
        <p>Users may report content they believe violates these Terms. We reserve the right to review, suspend, or remove flagged content.</p>

        <h2>9. Termination</h2>
        <p>We may suspend or terminate your account for violations of these Terms or for any conduct deemed inappropriate. You may also delete your account at any time, subject to applicable laws.</p>

        <h2>10. Disclaimers and Limitation of Liability</h2>
        <ul>
            <li>Rate It All! is provided “as is” without warranties of any kind.</li>
            <li>We do not guarantee the accuracy, completeness, or reliability of User Content.</li>
            <li>We are not responsible for any loss or damage resulting from the use of the site.</li>
        </ul>

        <h2>11. Privacy</h2>
        <p>Your use of the site is also governed by our <a href="privacy.php">Privacy Policy</a>, which explains how we collect, use, and store your data.</p>

        <h2>12. Changes to Terms</h2>
        <p>We may update these Terms at any time. The updated Terms will be effective immediately upon posting. Your continued use of the site constitutes acceptance of the updated Terms.</p>

        <h2>13. Governing Law and Jurisdiction</h2>
        <p>These Terms are governed by the laws of <strong>South Africa</strong>. Any disputes arising from these Terms or your use of the site shall be resolved in the courts of South Africa.</p>

        <h2>14. Contact</h2>
        <div class="contact-info">
            <p>Email: <a href="mailto:Techfusion@org.ac.za">Techfusion@org.ac.za</a></p>
            <p>Phone: +27 43 567 7869</p>
            <p>Address: 325 High Street, Grahamstown, Makhanda</p>
        </div>
    </main>
      
</body>
</html>
