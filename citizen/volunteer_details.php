
<?php
session_start();

include __DIR__ . '/../includes/db.php';

/* =========================================================
   CITIZEN ACCESS ONLY
========================================================= */

if (!isset($_SESSION['user_id']) || (int)$_SESSION['role_id'] !== 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];


/* =========================================================
   DASHBOARD PATH
   Direct path to the citizen dashboard
========================================================= */

$dashboard_url = '/ecoGuard/citizen/citizen_dashboard.php';


/* =========================================================
   GET EVENT ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . $dashboard_url);
    exit();
}

$event_id = (int)$_GET['id'];


/* =========================================================
   FETCH EVENT
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        Event_Id,
        Name,
        Date,
        Starting_Time,
        Location,
        Organized_By,
        Note
    FROM volunteer_event
    WHERE Event_Id = ?
");

$stmt->execute([$event_id]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: " . $dashboard_url);
    exit();
}


/* =========================================================
   CHECK PARTICIPATION
========================================================= */

$stmtCheck = $pdo->prepare("
    SELECT *
    FROM user_participate_volunteer_event
    WHERE U_Id = ?
    AND Event_Id = ?
");

$stmtCheck->execute([
    $user_id,
    $event_id
]);

$participation = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$alreadyRegistered = !empty($participation);

$successMessage = '';
$errorMessage = '';


/* =========================================================
   REGISTER FOR EVENT
========================================================= */

if (isset($_POST['register']) && !$alreadyRegistered) {

    try {

        $stmtRegister = $pdo->prepare("
            INSERT INTO user_participate_volunteer_event
            (U_Id, Event_Id)
            VALUES (?, ?)
        ");

        $stmtRegister->execute([
            $user_id,
            $event_id
        ]);

        $successMessage =
            "You have successfully registered for this volunteer event.";

        /* Refresh participation */

        $stmtCheck->execute([
            $user_id,
            $event_id
        ]);

        $participation = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $alreadyRegistered = true;

    } catch (PDOException $e) {

        $errorMessage =
            "Unable to register for this event. Please try again.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($event['Name']) ?> | EcoGuard
    </title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #f3f8f2 0%,
                    #eef5ec 50%,
                    #f8faf7 100%
                );

            color: #263b2b;
        }


        /* =====================================================
           TOP BAR
        ===================================================== */

        .top-bar {

            width: 100%;

            background: #ffffff;

            border-bottom:
                1px solid #dfe8dd;

            padding:
                16px 5%;

            display: flex;

            align-items: center;

            justify-content: space-between;

            position: sticky;

            top: 0;

            z-index: 100;
        }


        .brand {

            display: inline-flex;

            align-items: center;

            gap: 10px;

            color: #315c39;

            font-size: 20px;

            font-weight: 800;

            text-decoration: none;
        }


        .brand-icon {

            width: 38px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #e8f2e6;

            border-radius: 10px;

            font-size: 20px;
        }


        /* =====================================================
           DASHBOARD BUTTON
        ===================================================== */

        .dashboard-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                10px 16px;

            background: #eef5ec;

            border:
                1px solid #d4e3d1;

            color: #3f7048;

            border-radius: 9px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
        }


        .dashboard-btn:hover {

            background: #dfeade;

            border-color: #c6d9c3;

            transform: translateY(-1px);
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .page {

            width:
                min(1000px, calc(100% - 32px));

            margin:
                0 auto;

            padding:
                45px 0 70px;
        }


        /* =====================================================
           BREADCRUMB
        ===================================================== */

        .breadcrumb {

            margin-bottom: 20px;

            color: #78907c;

            font-size: 13px;
        }


        .breadcrumb a {

            color: #4d7955;

            text-decoration: none;

            font-weight: 600;
        }


        .breadcrumb a:hover {

            text-decoration: underline;
        }


        /* =====================================================
           EVENT CARD
        ===================================================== */

        .event-card {

            background: #ffffff;

            border:
                1px solid #dfe8dd;

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 10px 35px
                rgba(47, 75, 52, 0.08);
        }


        /* =====================================================
           EVENT HERO
        ===================================================== */

        .event-hero {

            padding:
                35px;

            background:
                linear-gradient(
                    135deg,
                    #edf6eb,
                    #f7faf6
                );

            border-bottom:
                1px solid #e1e9df;
        }


        .event-label {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px 10px;

            margin-bottom: 13px;

            background: #ffffff;

            border:
                1px solid #d9e6d6;

            border-radius: 20px;

            color: #4b7652;

            font-size: 11px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.07em;
        }


        .event-hero h1 {

            margin: 0 0 10px;

            color: #263b2b;

            font-size:
                clamp(28px, 5vw, 42px);

            line-height: 1.2;

            word-break: break-word;
        }


        .event-hero p {

            margin: 0;

            color: #69756c;

            font-size: 15px;

            line-height: 1.6;
        }


        /* =====================================================
           EVENT INFORMATION
        ===================================================== */

        .event-info {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            padding: 28px 35px;
        }


        .info-box {

            display: flex;

            align-items: flex-start;

            gap: 13px;

            padding:
                16px;

            background: #fafcf9;

            border:
                1px solid #e5ebe3;

            border-radius: 12px;

            min-width: 0;
        }


        .info-icon {

            width: 38px;

            height: 38px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eaf3e8;

            border-radius: 9px;

            font-size: 17px;
        }


        .info-content {

            min-width: 0;
        }


        .info-content small {

            display: block;

            margin-bottom: 4px;

            color: #8a948b;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.05em;
        }


        .info-content strong {

            display: block;

            color: #344739;

            font-size: 14px;

            line-height: 1.4;

            overflow-wrap: anywhere;
        }


        /* =====================================================
           DESCRIPTION
        ===================================================== */

        .description {

            margin:
                0 35px 28px;

            padding:
                22px;

            background: #f8faf7;

            border:
                1px solid #e4ebe2;

            border-radius: 12px;
        }


        .description h3 {

            margin:
                0 0 8px;

            color: #344739;

            font-size: 16px;
        }


        .description p {

            margin: 0;

            color: #69756c;

            font-size: 14px;

            line-height: 1.7;

            overflow-wrap: anywhere;
        }


        /* =====================================================
           MESSAGES
        ===================================================== */

        .message {

            margin:
                0 35px 25px;

            padding:
                14px 16px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 600;
        }


        .success {

            background: #eaf7ed;

            border:
                1px solid #cce8d2;

            color: #28713b;
        }


        .error {

            background: #fff0ef;

            border:
                1px solid #f2ceca;

            color: #a33c35;
        }


        /* =====================================================
           REGISTRATION AREA
        ===================================================== */

        .action-area {

            padding:
                25px 35px 35px;

            border-top:
                1px solid #e5ebe3;

            text-align: center;
        }


        .action-area h3 {

            margin:
                0 0 7px;

            color: #2e4132;

            font-size: 19px;
        }


        .action-area p {

            margin:
                0 auto 20px;

            max-width: 600px;

            color: #7a847c;

            font-size: 13px;

            line-height: 1.6;
        }


        .register-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            min-width: 220px;

            padding:
                13px 22px;

            background: #3f7048;

            border: none;

            border-radius: 9px;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .register-btn:hover {

            background: #315c39;

            transform: translateY(-2px);

            box-shadow:
                0 7px 16px
                rgba(63, 112, 72, 0.18);
        }


        /* =====================================================
           ALREADY REGISTERED
        ===================================================== */

        .registered-box {

            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding:
                13px 18px;

            background: #edf7ef;

            border:
                1px solid #cfe6d3;

            border-radius: 10px;

            color: #317342;

            font-size: 14px;

            font-weight: 700;
        }


        .registered-icon {

            width: 25px;

            height: 25px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #3f7048;

            color: white;

            border-radius: 50%;

            font-size: 13px;
        }


        /* =====================================================
           FOOTER NAVIGATION
        ===================================================== */

        .bottom-navigation {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-top: 20px;
        }


        .back-link {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            color: #5d7061;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            padding: 10px 0;
        }


        .back-link:hover {

            color: #315c39;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .top-bar {

                padding:
                    12px 16px;
            }


            .brand {

                font-size: 17px;
            }


            .brand-icon {

                width: 34px;

                height: 34px;
            }


            .dashboard-btn {

                padding:
                    9px 11px;

                font-size: 12px;
            }


            .page {

                width:
                    calc(100% - 20px);

                padding:
                    25px 0 45px;
            }


            .event-hero {

                padding: 25px 20px;
            }


            .event-info {

                grid-template-columns: 1fr;

                padding:
                    20px;
            }


            .description {

                margin:
                    0 20px 20px;

                padding:
                    18px;
            }


            .message {

                margin:
                    0 20px 20px;
            }


            .action-area {

                padding:
                    22px 20px 25px;
            }


            .bottom-navigation {

                flex-direction: column;

                align-items: stretch;
            }


            .back-link {

                justify-content: center;

                padding: 12px;
            }

        }


        /* =====================================================
           SMALL MOBILE
        ===================================================== */

        @media (max-width: 420px) {

            .event-hero h1 {

                font-size: 27px;
            }


            .event-hero p {

                font-size: 14px;
            }


            .dashboard-btn {

                padding:
                    9px 10px;
            }


            .event-info {

                padding: 15px;
            }


            .info-box {

                padding: 13px;
            }


            .register-btn {

                width: 100%;

                min-width: 0;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     TOP NAVIGATION
========================================================= -->

<header class="top-bar">

    <a
        href="/ecoGuard/citizen/citizen_dash.php"
        class="brand"
        aria-label="EcoGuard Citizen Dashboard"
    >

        <span class="brand-icon">
            🌿
        </span>

        <span>
            EcoGuard
        </span>

    </a>


    <a
        href="/ecoGuard/citizen/citizen_dash.php"
        class="dashboard-btn"
    >

        ← Dashboard

    </a>

</header>



<!-- =========================================================
     MAIN PAGE
========================================================= -->

<main class="page">


    <!-- BREADCRUMB -->

    <div class="breadcrumb">

        <a href="/ecoGuard/citizen/citizen_dash.php">
            Dashboard
        </a>

        <span>
            &nbsp; / &nbsp; Volunteer Event
        </span>

    </div>



    <!-- EVENT CARD -->

    <section class="event-card">


        <!-- =================================================
             EVENT HERO
        ================================================== -->

        <div class="event-hero">

            <span class="event-label">
                🌱 Volunteer Opportunity
            </span>


            <h1>
                <?= htmlspecialchars($event['Name']) ?>
            </h1>


            <p>
                Join your community in making a positive
                environmental impact with EcoGuard.
            </p>

        </div>



        <!-- =================================================
             EVENT INFORMATION
        ================================================== -->

        <div class="event-info">


            <!-- DATE -->

            <div class="info-box">

                <div class="info-icon">
                    📅
                </div>

                <div class="info-content">

                    <small>
                        Date
                    </small>

                    <strong>
                        <?= htmlspecialchars($event['Date']) ?>
                    </strong>

                </div>

            </div>



            <!-- TIME -->

            <div class="info-box">

                <div class="info-icon">
                    🕐
                </div>

                <div class="info-content">

                    <small>
                        Starting Time
                    </small>

                    <strong>
                        <?= htmlspecialchars($event['Starting_Time']) ?>
                    </strong>

                </div>

            </div>



            <!-- LOCATION -->

            <div class="info-box">

                <div class="info-icon">
                    📍
                </div>

                <div class="info-content">

                    <small>
                        Location
                    </small>

                    <strong>
                        <?= htmlspecialchars($event['Location']) ?>
                    </strong>

                </div>

            </div>



            <!-- ORGANIZER -->

            <div class="info-box">

                <div class="info-icon">
                    👤
                </div>

                <div class="info-content">

                    <small>
                        Organized By
                    </small>

                    <strong>
                        <?= htmlspecialchars($event['Organized_By']) ?>
                    </strong>

                </div>

            </div>


        </div>



        <!-- =================================================
             EVENT DESCRIPTION
        ================================================== -->

        <?php if (!empty($event['Note'])): ?>

            <div class="description">

                <h3>
                    About This Event
                </h3>

                <p>
                    <?= nl2br(
                        htmlspecialchars($event['Note'])
                    ) ?>
                </p>

            </div>

        <?php endif; ?>



        <!-- =================================================
             MESSAGES
        ================================================== -->

        <?php if (!empty($successMessage)): ?>

            <div class="message success">

                ✓

                <?= htmlspecialchars($successMessage) ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($errorMessage)): ?>

            <div class="message error">

                ⚠

                <?= htmlspecialchars($errorMessage) ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             REGISTRATION
        ================================================== -->

        <div class="action-area">


            <?php if (!$alreadyRegistered): ?>


                <h3>
                    Ready to make a difference?
                </h3>


                <p>
                    Register for this volunteer event
                    and contribute to keeping our community
                    clean and environmentally friendly.
                </p>


                <form method="post">

                    <button
                        type="submit"
                        name="register"
                        class="register-btn"
                    >

                        🌱

                        Register for Event

                    </button>

                </form>


            <?php else: ?>


                <div class="registered-box">

                    <span class="registered-icon">
                        ✓
                    </span>

                    You are registered for this event

                </div>


            <?php endif; ?>


        </div>


    </section>


    

</main>


</body>

</html>
