<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EcoGuard | Home</title>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500;1,600&family=Jost:wght@300;400;500&display=swap');

:root {
    --bg-top: #16452f;
    --bg-mid: #0f3323;
    --bg-deep: #081f16;
    --gold: #b9975a;
    --gold-soft: #d8c193;
    --ivory: #f0ece0;
    --ivory-dim: #a7b7aa;
}

* { box-sizing: border-box; }

html, body {
    margin: 0;
    padding: 0;
    height: 100%;
}

body {
    font-family: 'Jost', 'Segoe UI', sans-serif;
    color: var(--ivory);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    position: relative;
    overflow: hidden;

    background:
        radial-gradient(ellipse 900px 600px at 78% -5%, rgba(216, 193, 147, 0.14), transparent 60%),
        radial-gradient(ellipse 1400px 1000px at 50% 50%, rgba(22, 69, 47, 0.35), rgba(8, 31, 22, 0.85) 75%),
        linear-gradient(160deg, var(--bg-top) 0%, var(--bg-mid) 45%, var(--bg-deep) 100%);
}

/* A single soft light shaft, kept quiet */
body::before {
    content: "";
    position: absolute;
    top: -20%;
    right: 8%;
    width: 480px;
    height: 140%;
    background: linear-gradient(200deg, rgba(216, 193, 147, 0.07) 0%, transparent 55%);
    transform: rotate(-12deg);
    pointer-events: none;
}

/* Fine grain so the gradient doesn't look flat/digital */
body::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0.5;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
}

/* ---------- Split row: title+copy on the left, actions on the right ---------- */
.row {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1100px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 40px;
    flex-wrap: wrap;
}

.left {
    max-width: 560px;
}

.eyebrow {
    display: block;
    font-family: 'Jost', sans-serif;
    font-weight: 400;
    font-size: 0.72rem;
    letter-spacing: 0.36em;
    text-transform: uppercase;
    color: var(--gold-soft);
    margin-bottom: 20px;
}

.title {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-weight: 500;
    font-size: 5rem;
    line-height: 0.95;
    margin: 0 0 26px;
    color: var(--ivory);
}

.description {
    font-family: 'Jost', sans-serif;
    font-weight: 300;
    font-size: 1.05rem;
    line-height: 1.9;
    color: var(--ivory-dim);
    margin: 0;
}

.description strong {
    color: var(--gold-soft);
    font-weight: 500;
    font-style: italic;
    font-family: 'Cormorant Garamond', serif;
}

.actions {
    display: flex;
    gap: 14px;
    padding-top: 18px;
    flex-shrink: 0;
}

.btn {
    font-family: 'Jost', sans-serif;
    font-size: 0.76rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    padding: 15px 36px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    white-space: nowrap;
}

.btn.login {
    background: var(--gold);
    color: var(--bg-deep);
    font-weight: 500;
}
.btn.login:hover {
    background: var(--gold-soft);
    box-shadow: 0 10px 30px rgba(185, 151, 90, 0.3);
    transform: translateY(-2px);
}

.btn.register {
    background: transparent;
    color: var(--ivory-dim);
    border-color: rgba(240, 236, 224, 0.25);
}
.btn.register:hover {
    border-color: var(--gold-soft);
    color: var(--gold-soft);
    transform: translateY(-2px);
}

footer {
    position: absolute;
    bottom: 24px;
    left: 0;
    right: 0;
    z-index: 1;
    text-align: center;
}

footer p {
    margin: 0;
    font-size: 0.66rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(167, 183, 170, 0.5);
}

@media (max-width: 720px) {
    .row { flex-direction: column; }
    .title { font-size: 3.2rem; }
    .actions { padding-top: 8px; }
}

@media (prefers-reduced-motion: reduce) {
    .btn { transition: none; }
}
    </style>
</head>
<body>

    <div class="row">
        <div class="left">
           
            <h1 class="title">EcoGuard</h1>
            <p class="description">
                Welcome to <strong>EcoGuard</strong> — your platform to report environmental issues, participate in community clean-ups,
                and help keep your area clean and sustainable.
            </p>
        </div>
        <div class="actions">
            <button onclick="location.href='login.php'" class="btn login">Login</button>
            <button onclick="location.href='register.php'" class="btn register">Register</button>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date("Y") ?> EcoGuard &middot; All Rights Reserved</p>
    </footer>

</body>
</html>