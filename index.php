<?php include 'header.php'; ?>

<style>
/* ===== GLOBAL STYLES ===== */
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary: #0f172a;
    --text: #334155;
    --muted: #64748b;
    --light: #f8fafc;
    --white: #ffffff;
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: var(--text);
    background: var(--light);
}

/* ===== HERO SECTION ===== */
.hero {
    position: relative;
    overflow: hidden;
    padding: 90px 20px 100px;
    text-align: center;
    color: white;
    background:
        radial-gradient(circle at 15% 20%,
            rgba(59, 130, 246, 0.35), transparent 35%),
        radial-gradient(circle at 85% 80%,
            rgba(14, 165, 233, 0.25), transparent 30%),
        linear-gradient(135deg, #0f172a, #172554, #1e40af);
}

.hero::before {
    content: "";
    position: absolute;
    width: 300px;
    height: 300px;
    top: -150px;
    right: -70px;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50%;
}

.hero::after {
    content: "";
    position: absolute;
    width: 220px;
    height: 220px;
    bottom: -130px;
    left: -50px;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50%;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 850px;
    margin: auto;
    animation: fadeUp 0.9s ease both;
}

.hero-badge {
    display: inline-block;
    padding: 9px 16px;
    margin-bottom: 22px;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 30px;
    background: rgba(255,255,255,0.08);
    color: #bfdbfe;
    font-size: 13px;
    letter-spacing: 1px;
    font-weight: 600;
}

.hero h1 {
    margin: 0 0 22px;
    font-size: clamp(38px, 6vw, 68px);
    line-height: 1.12;
    letter-spacing: -2px;
    color: #ffffff;
}

.hero h1 span {
    color: #93c5fd;
}

.hero p {
    max-width: 680px;
    margin: 0 auto 32px;
    font-size: 18px;
    line-height: 1.8;
    color: #dbeafe;
}

.hero-btn {
    display: inline-block;
    padding: 15px 28px;
    border-radius: 10px;
    background: white;
    color: #1d4ed8;
    font-weight: 700;
    text-decoration: none;
    transition: 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.hero-btn:hover {
    transform: translateY(-3px);
    background: #dbeafe;
}

/* ===== ROLE SELECTION SECTION ===== */
.role-container {
    max-width: 1150px;
    padding: 75px 22px 85px;
    margin: auto;
}

.section-heading {
    max-width: 650px;
    margin: 0 auto 45px;
    text-align: center;
}

.section-heading .eyebrow {
    color: var(--primary);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.section-heading h2 {
    margin: 12px 0;
    font-size: clamp(28px, 4vw, 40px);
    color: var(--secondary);
    letter-spacing: -1px;
}

.section-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 16px;
    line-height: 1.8;
}

/* ===== CARDS ===== */
.card-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
}

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 330px;
    padding: 35px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(15,23,42,0.04);
    transition: transform 0.3s ease,
                box-shadow 0.3s ease,
                border-color 0.3s ease;
}

.card:hover {
    transform: translateY(-9px);
    border-color: #93c5fd;
    box-shadow: 0 20px 45px rgba(37,99,235,0.13);
}

.card::after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    right: -80px;
    top: -80px;
    border-radius: 50%;
    background: #eff6ff;
    transition: transform 0.4s ease;
}

.card:hover::after {
    transform: scale(1.5);
}

.card-icon {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    margin-bottom: 25px;
    border-radius: 16px;
    font-size: 30px;
    background: #dbeafe;
}

.employer-card .card-icon {
    background: #dcfce7;
}

.card h3 {
    position: relative;
    z-index: 1;
    margin: 0 0 15px;
    font-size: 25px;
    color: var(--secondary);
}

.card p {
    position: relative;
    z-index: 1;
    margin: 0 0 25px;
    font-size: 15px;
    line-height: 1.8;
    color: var(--muted);
}

.card-features {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 28px;
}

.card-features span {
    padding: 7px 11px;
    border-radius: 7px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.card-link {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    color: var(--primary);
    font-size: 15px;
    font-weight: 700;
}

.employer-card .card-link {
    color: #15803d;
}

.arrow {
    font-size: 23px;
    transition: transform 0.3s ease;
}

.card:hover .arrow {
    transform: translateX(6px);
}

/* ===== BOTTOM MESSAGE ===== */
.bottom-message {
    margin-top: 45px;
    padding: 25px;
    text-align: center;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 14px;
}

.bottom-message p {
    margin: 0;
    color: #1e40af;
    font-size: 15px;
    line-height: 1.8;
}

/* ===== FOOTER ===== */
.site-footer {
    padding: 35px 20px;
    text-align: center;
    background: #0f172a;
    color: #cbd5e1;
}

.footer-brand {
    margin-bottom: 10px;
    color: white;
    font-size: 23px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.footer-brand span {
    color: #60a5fa;
}

.site-footer p {
    margin: 8px 0;
    font-size: 13px;
    line-height: 1.8;
}

.footer-credit {
    color: #93c5fd;
    font-weight: 600;
}

.footer-line {
    max-width: 900px;
    height: 1px;
    margin: 23px auto;
    background: rgba(255,255,255,0.12);
}

/* ===== ANIMATIONS ===== */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 700px) {
    .hero {
        padding: 70px 20px 75px;
    }

    .hero h1 {
        letter-spacing: -1px;
    }

    .hero p {
        font-size: 16px;
    }

    .role-container {
        padding: 55px 17px;
    }

    .card-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .card {
        min-height: 300px;
        padding: 27px;
    }

    .card h3 {
        font-size: 22px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
</style>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">

        <div class="hero-badge">
            YOUR CAREER. YOUR NEXT OPPORTUNITY.
        </div>

        <h1>
            Connect Talent.<br>
            <span>Build Careers.</span>
        </h1>

        <p>
            Welcome to CareerConnect — a platform designed to bring
            job seekers and employers together. Discover opportunities,
            showcase your skills, and take the next step in your career.
        </p>

        <a href="#get-started" class="hero-btn">
            Get Started &nbsp; →
        </a>

    </div>
</section>

<!-- ROLE SELECTION -->
<main class="role-container" id="get-started">

    <div class="section-heading">
        <span class="eyebrow">Let's get started</span>

        <h2>What brings you to CareerConnect?</h2>

        <p>
            Choose the option that matches your goals.
            We'll help you get started with the right account.
        </p>
    </div>

    <div class="card-grid">

        <!-- JOB SEEKER CARD -->
        <a href="register.php?role=jobseeker" class="card">

            <div class="card-icon">🔍</div>

            <h3>I'm Looking for a Job</h3>

            <p>
                Ready for your next opportunity? Create your profile,
                showcase your skills, explore job listings, and connect
                with employers looking for talent like yours.
            </p>

            <div class="card-features">
                <span>Find Opportunities</span>
                <span>Build Your Profile</span>
                <span>Apply for Jobs</span>
            </div>

            <div class="card-link">
                <span>Join as a Job Seeker</span>
                <span class="arrow">→</span>
            </div>

        </a>

        <!-- EMPLOYER CARD -->
        <a href="register.php?role=employer"
           class="card employer-card">

            <div class="card-icon">💼</div>

            <h3>I'm Hiring Talent</h3>

            <p>
                Find the right people for your organization.
                Create an employer profile, share job opportunities,
                discover candidates, and manage applications.
            </p>

            <div class="card-features">
                <span>Post Opportunities</span>
                <span>Discover Candidates</span>
                <span>Review Applicants</span>
            </div>

            <div class="card-link">
                <span>Join as an Employer</span>
                <span class="arrow">→</span>
            </div>

        </a>

    </div>

    <div class="bottom-message">
        <p>
            <strong>Every great career starts somewhere.</strong>
            Take the first step and let CareerConnect help you move forward.
        </p>
    </div>

</main>

<!-- FOOTER -->
<footer class="site-footer">

    <div class="footer-brand">
        Career<span>Connect</span>
    </div>

    <p>
        Connecting people with opportunities.
    </p>

    <div class="footer-line"></div>

    <p>
        &copy; <?= date('Y'); ?> CareerConnect. All rights reserved.
    </p>

    <p class="footer-credit">
        Built by Eric Maina | 5Techx
    </p>

</footer>

</body>
</html>
